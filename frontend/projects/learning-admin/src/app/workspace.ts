import { ChangeDetectionStrategy, Component, inject, OnInit, signal } from "@angular/core";
import { Subscription, switchMap, timer } from "rxjs";
import { ContextPanel } from "./context-panel";
import { BrowseColumn, Drilldown, nextColumn } from "./drilldown";
import { RecordDetail, RecordType, Summary } from "./models";
import { RecordEditor } from "./record-editor";
import { errorMessage, WorkspaceApi } from "./workspace-api";

@Component({
    selector: "studio-workspace",
    imports: [ContextPanel, Drilldown, RecordEditor],
    templateUrl: "./workspace.html",
    changeDetection: ChangeDetectionStrategy.OnPush,
    host: { "(window:beforeunload)": "beforeUnload($event)" },
})
export class Workspace implements OnInit {
    readonly api = inject(WorkspaceApi);
    readonly types: RecordType[] = ["paths", "lessons", "exercises", "cards", "groups"];
    readonly type = signal<RecordType>("paths");
    readonly columns = signal<BrowseColumn[]>([]);
    readonly drawer = signal<RecordDetail | null>(null);
    readonly creating = signal(false);
    readonly drawerDirty = signal(false);
    readonly createDirty = signal(false);
    readonly loading = signal(true);
    readonly error = signal("");
    readonly globalQuery = signal("");
    readonly results = signal<Summary[]>([]);
    readonly searching = signal(false);
    readonly pending = signal<(() => void) | null>(null);
    readonly version = signal(0);
    private searchSubscription?: Subscription;
    private drawerSubscription?: Subscription;
    private storageKey = "";

    async ngOnInit() {
        try {
            await this.api.initialize();
            this.storageKey = "learning-studio-location-" + this.api.config()!.userId;
            this.selectType("paths");
            const saved = sessionStorage.getItem(this.storageKey);
            if (saved) {
                const state: unknown = JSON.parse(saved);
                if (this.isNavigation(state)) {
                    this.type.set(state.type);
                    const leaf = state.columns.findIndex(
                        (column) => column.selected && !nextColumn(column.selected),
                    );
                    this.columns.set(leaf < 0 ? state.columns : state.columns.slice(0, leaf + 1));
                } else
                    throw new Error(
                        "Saved navigation could not be restored. Select a workspace to continue.",
                    );
            }
        } catch (error: unknown) {
            this.error.set(errorMessage(error));
        } finally {
            this.loading.set(false);
        }
    }

    selectType(type: RecordType) {
        this.type.set(type);
        this.columns.set([{ type, label: this.api.config()!.types[type].label }]);
    }
    navigate(columns: BrowseColumn[]) {
        this.columns.set(columns);
        try {
            sessionStorage.setItem(this.storageKey, JSON.stringify({ type: this.type(), columns }));
        } catch (error: unknown) {
            this.error.set("Navigation state could not be saved: " + errorMessage(error));
        }
    }
    open(row: Summary) {
        this.type.set(row.type);
        const next = nextColumn(row);
        this.navigate([
            { type: row.type, label: this.api.config()!.types[row.type].label, selected: row },
            ...(next ? [next] : []),
        ]);
        this.results.set([]);
        this.globalQuery.set("");
        if (!next) this.inspect(row);
    }
    inspect(row: Summary) {
        this.guard(this.drawerDirty(), () => {
            this.error.set("");
            this.loading.set(true);
            this.drawerSubscription?.unsubscribe();
            this.drawerSubscription = this.api.record(row.type, row.id).subscribe({
                next: (record) => {
                    this.drawer.set(record);
                    this.loading.set(false);
                },
                error: (error: unknown) => {
                    this.error.set(errorMessage(error));
                    this.loading.set(false);
                },
            });
        });
    }
    closeDrawer() {
        this.guard(this.drawerDirty(), () => {
            this.drawer.set(null);
            this.drawerDirty.set(false);
        });
    }
    closeCreate() {
        this.guard(this.createDirty(), () => {
            this.creating.set(false);
            this.createDirty.set(false);
        });
    }
    guard(dirty: boolean, action: () => void) {
        if (dirty) this.pending.set(action);
        else action();
    }
    confirmDiscard() {
        const action = this.pending();
        this.pending.set(null);
        action?.();
    }
    drawerSaved(record: RecordDetail) {
        this.drawer.set(record);
        this.version.update((n) => n + 1);
        this.columns.update((columns) =>
            columns.map((column) =>
                column.selected?.id === record.id && column.selected.type === record.type
                    ? { ...column, selected: record }
                    : column,
            ),
        );
    }
    created(record: RecordDetail) {
        this.creating.set(false);
        this.createDirty.set(false);
        this.version.update((n) => n + 1);
        this.inspect(record);
    }
    search(event: Event) {
        const query = (event.target as HTMLInputElement).value;
        this.globalQuery.set(query);
        this.searchSubscription?.unsubscribe();
        this.results.set([]);
        if (query.trim().length < 2) {
            this.searching.set(false);
            return;
        }
        this.searching.set(true);
        this.searchSubscription = timer(300)
            .pipe(switchMap(() => this.api.search(query)))
            .subscribe({
                next: (result) => {
                    this.results.set(result.items);
                    this.searching.set(false);
                },
                error: (error: unknown) => {
                    this.error.set(errorMessage(error));
                    this.searching.set(false);
                },
            });
    }
    beforeUnload(event: BeforeUnloadEvent) {
        if (this.drawerDirty() || this.createDirty()) {
            event.preventDefault();
            event.returnValue = "";
        }
    }
    private isNavigation(value: unknown): value is { type: RecordType; columns: BrowseColumn[] } {
        if (
            typeof value !== "object" ||
            value === null ||
            !("type" in value) ||
            !("columns" in value) ||
            typeof value.type !== "string" ||
            !Object.hasOwn(this.api.config()!.types, value.type) ||
            !Array.isArray(value.columns) ||
            !value.columns.length ||
            value.columns.length > 6
        )
            return false;
        return value.columns.every(
            (column: unknown) =>
                typeof column === "object" &&
                column !== null &&
                "type" in column &&
                typeof column.type === "string" &&
                (column.type === "activities" ||
                    Object.hasOwn(this.api.config()!.types, column.type)) &&
                "label" in column &&
                typeof column.label === "string",
        );
    }
}
