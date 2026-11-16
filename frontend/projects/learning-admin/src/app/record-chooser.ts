import {
    ChangeDetectionStrategy,
    Component,
    computed,
    forwardRef,
    inject,
    input,
    OnInit,
    output,
    signal,
} from "@angular/core";
import { ContextPanel } from "./context-panel";
import { RecordDetail, Summary } from "./models";
import { RecordEditor } from "./record-editor";
import { RecordTable } from "./record-table";
import { errorMessage, WorkspaceApi } from "./workspace-api";

const NOUNS: Record<string, string> = { cards: "card", groups: "card group" };

/**
 * Pick an existing card or card group, or create/edit one in place and then use it.
 * Used everywhere a card or group is attached so the flow stays the same.
 */
@Component({
    selector: "studio-record-chooser",
    imports: [ContextPanel, RecordTable, forwardRef(() => RecordEditor)],
    changeDetection: ChangeDetectionStrategy.OnPush,
    template: `
        <studio-context-panel [heading]="heading()" (closed)="leave('close')">
            @if (mode() === "list") {
                <div class="chooser-toolbar">
                    <button type="button" class="primary" (click)="startNew()">
                        + New {{ noun() }}
                    </button>
                    <span class="muted">
                        or choose an existing {{ noun() }}{{
                            type() === "groups" ? " (Edit changes its name or cards first)" : ""
                        }}
                    </span>
                </div>
                <studio-record-table
                    [type]="type()"
                    [editable]="false"
                    [openable]="type() === 'groups'"
                    [selectedId]="selectedId()"
                    (selected)="chosen.emit($event)"
                    (opened)="edit($event)"
                />
            } @else {
                <div class="chooser-toolbar">
                    @if (!editId()) {
                        <button type="button" (click)="leave('list')">← All {{ noun() }}s</button>
                    }
                    <span class="muted">{{ hint() }}</span>
                </div>
                @if (error()) {
                    <p class="error" role="alert">{{ error() }}</p>
                }
                @if (loading()) {
                    <p class="muted" aria-live="polite">Loading {{ noun() }}…</p>
                } @else {
                    <div class="chooser-editor">
                        <studio-record-editor
                            [type]="type()"
                            [record]="record()"
                            (saved)="saved($event)"
                            (dirtyChange)="dirty.set($event)"
                            (inspect)="inspect($event)"
                        />
                    </div>
                }
                @if (confirmLeave()) {
                    <div class="sticky-actions" role="alert">
                        <span class="muted">This {{ noun() }} has unsaved changes.</span>
                        <button type="button" (click)="confirmLeave.set(null)">Keep editing</button>
                        <button type="button" class="danger" (click)="forceLeave()">
                            Discard changes
                        </button>
                    </div>
                } @else {
                    <div class="sticky-actions">
                        <span class="muted">{{
                            !record()
                                ? "Save the " + noun() + " to use it."
                                : dirty()
                                  ? "Save or discard changes first."
                                  : "Saved."
                        }}</span>
                        <button
                            type="button"
                            class="primary"
                            [disabled]="!record() || dirty()"
                            (click)="chosen.emit(record()!)"
                        >
                            {{ editId() ? "Done" : "Use this " + noun() }}
                        </button>
                    </div>
                }
            }
        </studio-context-panel>
        @if (inspecting(); as related) {
            <studio-record-chooser
                [type]="related.type"
                [editId]="related.id"
                (chosen)="inspected()"
                (closed)="inspected()"
            />
        }
    `,
})
export class RecordChooser implements OnInit {
    readonly type = input.required<"cards" | "groups">();
    readonly selectedId = input<number | null>(null);
    /** Open straight into editing this record instead of the list. */
    readonly editId = input<number | null>(null);
    readonly chosen = output<Summary>();
    /** Emitted on every save so callers can refresh previews of the record. */
    readonly updated = output<RecordDetail>();
    readonly closed = output<void>();
    readonly mode = signal<"list" | "edit">("list");
    readonly record = signal<RecordDetail | null>(null);
    readonly dirty = signal(false);
    readonly loading = signal(false);
    readonly error = signal("");
    readonly inspecting = signal<{ type: "cards" | "groups"; id: number } | null>(null);
    readonly confirmLeave = signal<"close" | "list" | null>(null);
    readonly noun = computed(() => NOUNS[this.type()]);
    readonly heading = computed(() => {
        if (this.mode() === "list") return "Choose " + this.noun();
        return (this.record() ? "Edit " : "New ") + this.noun();
    });
    readonly hint = computed(() => {
        if (!this.record()) return "Fill in the required fields, then save.";
        return this.type() === "groups"
            ? "Use Relationships to add or remove cards in this group."
            : "Attach image, audio or video in Media.";
    });
    private readonly api = inject(WorkspaceApi);

    ngOnInit() {
        const id = this.editId();
        if (id) this.edit({ id } as Summary);
    }
    startNew() {
        this.record.set(null);
        this.dirty.set(false);
        this.error.set("");
        this.mode.set("edit");
    }
    edit(row: Summary) {
        this.mode.set("edit");
        this.loading.set(true);
        this.error.set("");
        this.api.record(this.type(), row.id).subscribe({
            next: (record) => {
                this.record.set(record);
                this.loading.set(false);
            },
            error: (error: unknown) => {
                this.loading.set(false);
                this.error.set(errorMessage(error));
            },
        });
    }
    /** Open a related card or group on top, keeping this one where it is. */
    inspect(row: Summary) {
        if (row.type === "cards" || row.type === "groups")
            this.inspecting.set({ type: row.type, id: row.id });
    }
    inspected() {
        this.inspecting.set(null);
        // Reload so relationship lists show any renamed or re-imaged members.
        const current = this.record();
        if (current)
            this.api.record(this.type(), current.id).subscribe({
                next: (record) => this.record.set(record),
                error: (error: unknown) => this.error.set(errorMessage(error)),
            });
    }
    saved(record: RecordDetail) {
        this.record.set(record);
        this.updated.emit(record);
    }
    leave(target: "close" | "list") {
        if (this.mode() === "edit" && this.dirty()) {
            this.confirmLeave.set(target);
            return;
        }
        this.go(target);
    }
    forceLeave() {
        const target = this.confirmLeave();
        this.confirmLeave.set(null);
        this.dirty.set(false);
        if (target) this.go(target);
    }
    private go(target: "close" | "list") {
        if (target === "close" || this.editId()) {
            this.closed.emit();
            return;
        }
        this.mode.set("list");
        this.record.set(null);
        this.error.set("");
    }
}
