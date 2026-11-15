import {
    ChangeDetectionStrategy,
    Component,
    effect,
    ElementRef,
    inject,
    input,
    output,
    signal,
    untracked,
    viewChild,
} from "@angular/core";
import { CdkDrag, CdkDragDrop, CdkDragHandle, CdkDropList, moveItemInArray } from "@angular/cdk/drag-drop";
import { timer, switchMap } from "rxjs";
import { BrowseType, OrderScope, Parent, RecordType, Summary } from "./models";
import { arrowTarget, isConflict } from "./sortable";
import { errorMessage, WorkspaceApi } from "./workspace-api";
import { WorkspaceState } from "./workspace-state";
import { AudioButton } from "./audio-button";
import { ActionMenu, MenuItem } from "./structure";

@Component({
    selector: "studio-record-table",
    imports: [ActionMenu, AudioButton, CdkDropList, CdkDrag, CdkDragHandle],
    changeDetection: ChangeDetectionStrategy.OnPush,
    template: `
        <div class="table-tools" [class.browse-tools]="navigator()">
            <label class="search-field"
                >Find records
                <input
                    type="search"
                    [value]="query()"
                    (input)="filter($event)"
                    placeholder="Search name or ID"
                />
            </label>
            @if (!navigator()) {
                <label
                    >Sort by
                    <select [value]="sort()" (change)="sortBy($event)">
                        <option value="">Name</option>
                        <option value="id">ID</option>
                    </select>
                </label>
                <button
                    type="button"
                    (click)="descending.set(!descending())"
                    [attr.aria-label]="descending() ? 'Sort ascending' : 'Sort descending'"
                >
                    {{ descending() ? "Descending" : "Ascending" }}
                </button>
            }
        </div>
        @if (error()) {
            <p role="alert" class="error">
                {{ error() }} <button (click)="refresh()">Retry</button>
            </p>
        }
        <div #list class="record-list" [attr.aria-busy]="loading()" (scroll)="remember()">
            @if (loading()) {
                <p role="status" class="muted">Loading records...</p>
            }
            @if (navigator()) {
                @if (order()) {
                    <p class="order-hint" id="order-hint-{{ stateKey }}">
                        Drag <span aria-hidden="true">⠿</span> to reorder, or focus it and use
                        arrow keys.
                    </p>
                } @else if (canOrder() && !loading()) {
                    <p class="order-hint muted">
                        {{ query() ? "Clear the search to reorder." : "Too many records to reorder here." }}
                    </p>
                }
                <div
                    class="browse-choices"
                    cdkDropList
                    [cdkDropListData]="rows()"
                    [cdkDropListDisabled]="!order() || reordering()"
                    (cdkDropListDropped)="dropped($event)"
                    [attr.aria-busy]="reordering()"
                >
                    @for (row of rows(); track row.type + row.id; let i = $index) {
                        <div
                            class="browse-choice"
                            cdkDrag
                            [cdkDragDisabled]="!order() || reordering()"
                            [class.sortable]="order()"
                            [class.chosen]="
                                selectedId() === row.id &&
                                (!selectedType() || selectedType() === row.type)
                            "
                        >
                            @if (order()) {
                                <button
                                    type="button"
                                    class="drag-handle"
                                    cdkDragHandle
                                    [disabled]="reordering()"
                                    [attr.aria-label]="
                                        'Reorder ' + (row.title || 'record') + ', position ' + (i + 1) + ' of ' + rows().length
                                    "
                                    [attr.aria-describedby]="'order-hint-' + stateKey"
                                    title="Drag to reorder (or use arrow keys)"
                                    (keydown)="keyMove($event, i)"
                                >
                                    <span aria-hidden="true">⠿</span>
                                </button>
                            }
                            <button
                                type="button"
                                class="choice-main"
                                (click)="selected.emit(row)"
                                [attr.aria-label]="row.title"
                                [attr.aria-current]="
                                    selectedId() === row.id &&
                                    (!selectedType() || selectedType() === row.type)
                                        ? 'true'
                                        : null
                                "
                            >
                                <span class="choice-copy"
                                    ><strong>{{ row.title || "(Untitled)" }}</strong>
                                    <small>{{
                                        row.subtitle ||
                                            (row.type === "lessons"
                                                ? "Lesson"
                                                : row.type === "exercises"
                                                  ? "Exercise"
                                                  : "#" + row.id)
                                    }}</small>
                                    @if (row.metadata?.length) {
                                        <span class="row-metadata">{{
                                            row.metadata!.join(" · ")
                                        }}</span>
                                    }
                                    @if (row.description) {
                                        <span class="row-description" [title]="row.description">{{
                                            row.description
                                        }}</span>
                                    }
                                </span>
                                <span class="choice-chevron" aria-hidden="true">›</span>
                            </button>
                            <button
                                type="button"
                                class="choice-edit"
                                [attr.aria-label]="'Edit ' + row.title"
                                (click)="inspected.emit(row)"
                                title="Edit without leaving this location"
                            >
                                Edit
                            </button>
                            @if (rowMenu(row).length) {
                                <studio-action-menu
                                    class="choice-menu"
                                    label="⋯"
                                    buttonClass="choice-edit"
                                    [ariaLabel]="'More actions for ' + row.title"
                                    [items]="rowMenu(row)"
                                    (chosen)="action.emit({ action: $event, row })"
                                />
                            }
                            <div class="row-previews">
                                @for (card of row.previewCards; track card.id) {
                                    <div
                                        class="row-card-preview"
                                        [title]="card.title + ' / ' + card.english"
                                    >
                                        @if (card.imageUrl) {
                                            <img [src]="card.imageUrl" alt="" loading="lazy" />
                                        }
                                        <span
                                            >{{ card.title }}<small>{{ card.english }}</small></span
                                        >
                                        @for (audio of card.audioUrls.slice(0, 1); track audio) {
                                            <studio-audio-button
                                                [url]="audio"
                                                [label]="card.title"
                                            />
                                        }
                                    </div>
                                }
                                @if (row.audioUrl) {
                                    <studio-audio-button [url]="row.audioUrl" [label]="row.title" />
                                }
                            </div>
                        </div>
                    } @empty {
                        @if (!loading()) {
                            <p class="empty">No records here.</p>
                        }
                    }
                </div>
            } @else {
                <table>
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Record</th>
                            <th scope="col"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @for (row of rows(); track row.id) {
                            <tr [class.selected]="selectedId() === row.id">
                                <td class="mono">{{ row.id }}</td>
                                <td>
                                    @if (renaming() === row.id) {
                                        <form class="inline-edit" (submit)="rename($event, row)">
                                            <input
                                                aria-label="Record name"
                                                [value]="row.title"
                                                name="title"
                                                required
                                                maxlength="255"
                                            />
                                            <button class="primary" [disabled]="saving()">
                                                Save
                                            </button>
                                            <button
                                                type="button"
                                                [disabled]="saving()"
                                                (click)="renaming.set(null)"
                                            >
                                                Cancel
                                            </button>
                                        </form>
                                    } @else {
                                        <button
                                            type="button"
                                            class="text-button record-title"
                                            (click)="selected.emit(row)"
                                        >
                                            {{ row.title || "(Untitled)" }}
                                        </button>
                                        @if (row.subtitle) {
                                            <small class="muted">{{ row.subtitle }}</small>
                                        }
                                        @if (row.metadata?.length) {
                                            <span class="row-metadata">{{
                                                row.metadata!.join(" · ")
                                            }}</span>
                                        }
                                        @if (row.description) {
                                            <span
                                                class="row-description"
                                                [title]="row.description"
                                                >{{ row.description }}</span
                                            >
                                        }
                                        @for (card of row.previewCards; track card.id) {
                                            <div class="row-card-preview">
                                                @if (card.imageUrl) {
                                                    <img
                                                        [src]="card.imageUrl"
                                                        alt=""
                                                        loading="lazy"
                                                    />
                                                }
                                                <span
                                                    >{{ card.title
                                                    }}<small>{{ card.english }}</small></span
                                                >
                                                @for (
                                                    audio of card.audioUrls.slice(0, 1);
                                                    track audio
                                                ) {
                                                    <studio-audio-button
                                                        [url]="audio"
                                                        [label]="card.title"
                                                    />
                                                }
                                            </div>
                                        }
                                        @if (row.audioUrl) {
                                            <studio-audio-button
                                                [url]="row.audioUrl"
                                                [label]="row.title"
                                            />
                                        }
                                    }
                                </td>
                                <td>
                                    @if (editable()) {
                                        <button
                                            type="button"
                                            class="quiet"
                                            [disabled]="saving()"
                                            (click)="beginRename(row)"
                                            [attr.aria-label]="'Rename ' + row.title"
                                        >
                                            Rename
                                        </button>
                                    }
                                    @if (removable()) {
                                        <button
                                            type="button"
                                            class="quiet"
                                            (click)="removed.emit(row)"
                                            [attr.aria-label]="
                                                'Remove association with ' + row.title
                                            "
                                        >
                                            Remove
                                        </button>
                                    }
                                </td>
                            </tr>
                        } @empty {
                            @if (!loading()) {
                                <tr>
                                    <td colspan="3" class="empty">No records match this search.</td>
                                </tr>
                            }
                        }
                    </tbody>
                </table>
            }
        </div>
        <p class="sr-only" aria-live="polite">{{ announcement() }}</p>
        <footer class="pagination">
            <span>{{ total() }} records · Page {{ page() }} of {{ pages() }}</span>
            <div class="actions">
                <button (click)="page.set(page() - 1)" [disabled]="page() <= 1 || loading()">
                    Previous
                </button>
                <button (click)="page.set(page() + 1)" [disabled]="page() >= pages() || loading()">
                    Next
                </button>
            </div>
        </footer>
    `,
})
export class RecordTable {
    readonly type = input.required<BrowseType>();
    readonly navigator = input(false);
    readonly selectedType = input<RecordType>();
    readonly version = input(0);
    readonly parent = input<Parent>();
    readonly editable = input(true);
    readonly removable = input(false);
    readonly selectedId = input<number | null>(null);
    readonly selected = output<Summary>();
    readonly changed = output<void>();
    readonly removed = output<Summary>();
    readonly inspected = output<Summary>();
    /** Row actions offered in the navigator's "⋯" menu: "copy" and/or "unlink". */
    readonly actions = input<string[]>([]);
    readonly action = output<{ action: string; row: Summary }>();
    readonly query = signal("");
    readonly sort = signal("");
    readonly descending = signal(false);
    readonly page = signal(1);
    readonly total = signal(0);
    readonly rows = signal<Summary[]>([]);
    readonly loading = signal(false);
    readonly error = signal("");
    readonly renaming = signal<number | null>(null);
    readonly saving = signal(false);
    readonly order = signal<OrderScope | null>(null);
    readonly reordering = signal(false);
    readonly announcement = signal("");
    private readonly pageSize = signal(25);
    private readonly reload = signal(0);
    private readonly api = inject(WorkspaceApi);
    private readonly state = inject(WorkspaceState);
    private readonly list = viewChild.required<ElementRef<HTMLElement>>("list");
    stateKey = "";
    private revision = "";

    constructor() {
        effect(() => {
            const type = this.type(),
                parent = this.parent();
            const key = `${type}:${parent?.type ?? ""}:${parent?.id ?? ""}:${parent?.pathId ?? ""}`;
            untracked(() => {
                if (this.stateKey === key) return;
                if (this.stateKey) this.remember();
                this.stateKey = key;
                this.rows.set([]);
                this.total.set(0);
                this.order.set(null);
                const state = this.state.table(key);
                this.query.set(state.query);
                this.page.set(state.page);
                this.sort.set(state.sort);
                this.descending.set(state.descending);
                this.renaming.set(null);
            });
        });
        effect((cleanup) => {
            const type = this.type(),
                parent = this.parent(),
                q = this.query(),
                page = this.page();
            const sort = this.sort(),
                direction = this.descending() ? "desc" : "asc";
            this.reload();
            this.version();
            this.loading.set(true);
            this.error.set("");
            const subscription = timer(q ? 250 : 0)
                .pipe(switchMap(() => this.api.records(type, q, page, sort, direction, parent)))
                .subscribe({
                    next: (result) => {
                        this.rows.set(result.items);
                        this.total.set(result.total);
                        this.pageSize.set(result.pageSize || 25);
                        this.order.set(this.navigator() ? (result.order ?? null) : null);
                        this.loading.set(false);
                        requestAnimationFrame(() => {
                            this.list().nativeElement.scrollTop = this.state.table(
                                this.stateKey,
                            ).scroll;
                        });
                    },
                    error: (error: unknown) => {
                        this.error.set(errorMessage(error));
                        this.loading.set(false);
                    },
                });
            cleanup(() => subscription.unsubscribe());
        });
        effect(() => {
            this.query();
            this.page();
            this.sort();
            this.descending();
            untracked(() => this.remember());
        });
    }

    rowMenu(row: Summary): MenuItem[] {
        const items: MenuItem[] = [];
        if (this.actions().includes("copy") && ["units", "lessons", "exercises"].includes(row.type))
            items.push({ id: "copy", label: "Copy…", hint: "With its content; choose what to duplicate" });
        if (this.actions().includes("unlink") && row.linkId)
            items.push({ id: "unlink", label: "Remove from unit", hint: "Keeps the " + (row.type === "lessons" ? "lesson" : "exercise") + " itself", danger: true });
        return items;
    }

    remember() {
        if (!this.stateKey) return;
        this.state.saveTable(this.stateKey, {
            query: this.query(),
            page: this.page(),
            sort: this.sort(),
            descending: this.descending(),
            scroll: this.list()?.nativeElement.scrollTop ?? 0,
        });
    }

    pages() {
        return Math.max(1, Math.ceil(this.total() / this.pageSize()));
    }
    /** Whether this column lists children of an ordered parent (so reordering is possible in principle). */
    canOrder() {
        const parent = this.parent()?.type,
            type = this.type();
        return (
            (parent === "paths" && type === "levels") ||
            (parent === "levels" && type === "units") ||
            (parent === "units" && type === "activities")
        );
    }
    dropped(event: CdkDragDrop<Summary[]>) {
        this.move(event.previousIndex, event.currentIndex);
    }
    keyMove(event: KeyboardEvent, index: number) {
        const target = arrowTarget(event, index, this.rows().length, "y");
        if (target === null) return;
        this.move(index, target, event.currentTarget as HTMLElement);
    }
    private move(from: number, to: number, handle?: HTMLElement) {
        const order = this.order();
        if (!order || from === to || this.reordering()) return;
        const previous = this.rows();
        const rows = [...previous];
        moveItemInArray(rows, from, to);
        const ids = rows.map((row) => row.linkId);
        if (ids.some((id) => !id)) return;
        this.rows.set(rows);
        this.reordering.set(true);
        this.error.set("");
        const moved = rows[to];
        this.announcement.set(`Moving ${moved.title || "record"} to position ${to + 1} of ${rows.length}.`);
        // Keep keyboard focus on the moved row's handle after Angular re-renders the list.
        if (handle) {
            const list = handle.closest(".browse-choices");
            requestAnimationFrame(() =>
                (list?.querySelectorAll<HTMLElement>(".drag-handle")[to])?.focus(),
            );
        }
        this.api.reorder(order, ids as number[]).subscribe({
            next: () => {
                this.reordering.set(false);
                this.announcement.set(`Moved ${moved.title || "record"} to position ${to + 1} of ${rows.length}.`);
                this.changed.emit();
            },
            error: (error: unknown) => {
                this.reordering.set(false);
                this.rows.set(previous);
                this.announcement.set("The order was not changed.");
                this.error.set(
                    isConflict(error)
                        ? "Someone else changed this list. It has been refreshed; please try again."
                        : errorMessage(error),
                );
                if (isConflict(error)) this.refresh();
            },
        });
    }
    refresh() {
        this.reload.update((n) => n + 1);
    }
    filter(event: Event) {
        this.query.set((event.target as HTMLInputElement).value);
        this.page.set(1);
    }
    sortBy(event: Event) {
        this.sort.set((event.target as HTMLSelectElement).value);
        this.page.set(1);
    }
    beginRename(row: Summary) {
        this.error.set("");
        this.saving.set(true);
        this.api.record(row.type, row.id).subscribe({
            next: (record) => {
                this.revision = record.revision;
                this.renaming.set(row.id);
                this.saving.set(false);
            },
            error: (error: unknown) => {
                this.error.set(errorMessage(error));
                this.saving.set(false);
            },
        });
    }

    rename(event: Event, row: Summary) {
        event.preventDefault();
        if (this.saving()) return;
        const title = new FormData(event.target as HTMLFormElement).get("title");
        if (typeof title !== "string" || !title.trim()) return;
        this.saving.set(true);
        this.api
            .save(
                row.type,
                row.id,
                { [this.api.config()!.types[row.type].title]: title.trim() },
                this.revision,
            )
            .subscribe({
                next: () => {
                    this.saving.set(false);
                    this.renaming.set(null);
                    this.refresh();
                    this.changed.emit();
                },
                error: (error: unknown) => {
                    this.saving.set(false);
                    this.error.set(errorMessage(error));
                },
            });
    }
}
