import {
    ChangeDetectionStrategy,
    Component,
    effect,
    ElementRef,
    inject,
    input,
    output,
    signal,
    viewChild,
} from "@angular/core";
import { Parent, RecordType, Summary } from "./models";
import { RecordTable } from "./record-table";
import { ActionMenu, MenuItem, StructureDialog, StructureDone, StructureRequest } from "./structure";

export type BrowseType = RecordType | "activities";
export interface BrowseColumn {
    type: BrowseType;
    label: string;
    parent?: Parent;
    selected?: Summary;
}

export function nextColumn(record: Summary): BrowseColumn | null {
    const children: Partial<Record<RecordType, [BrowseType, string]>> = {
        paths: ["levels", "Levels"],
        levels: ["units", "Units"],
        units: ["activities", "Lessons & exercises"],
        groups: ["cards", "Group cards"],
    };
    const child = children[record.type];
    return child
        ? { type: child[0], label: child[1], parent: { type: record.type, id: record.id } }
        : null;
}

@Component({
    selector: "studio-drilldown",
    imports: [ActionMenu, RecordTable, StructureDialog],
    changeDetection: ChangeDetectionStrategy.OnPush,
    template: `
        <nav class="route-trail" aria-label="Current learning location">
            <button type="button" (click)="jump(0)">{{ columns()[0]?.label }}</button>
            @for (column of columns(); track $index; let i = $index) {
                @if (column.selected; as selected) {
                    <span aria-hidden="true">/</span>
                    <button
                        type="button"
                        (click)="jump(i + 1)"
                        [attr.aria-current]="i === columns().length - 2 ? 'location' : null"
                    >
                        {{ selected.title }}
                    </button>
                }
            }
        </nav>
        <div #board class="drilldown-board" aria-label="Horizontal content navigator">
            @for (column of columns(); track $index; let i = $index) {
                <section class="browse-column" [attr.aria-label]="column.label">
                    <header class="column-heading">
                        <div>
                            <span class="column-step">{{ i + 1 }}</span>
                            <h2>{{ column.label }}</h2>
                        </div>
                        @if (addMenu(i); as items) {
                            @if (items.length === 1) {
                                <button
                                    type="button"
                                    class="column-add"
                                    (click)="add(i, items[0].id)"
                                    [title]="items[0].hint ?? items[0].label"
                                >
                                    {{ items[0].label }}
                                </button>
                            } @else if (items.length) {
                                <studio-action-menu
                                    label="+ Add ▾"
                                    buttonClass="column-add"
                                    [ariaLabel]="'Add to ' + (column.parent ? columns()[i - 1]?.selected?.title : column.label)"
                                    [items]="items"
                                    (chosen)="add(i, $event)"
                                />
                            }
                        }
                    </header>
                    @if (column.parent) {
                        <div class="column-context">{{ columns()[i - 1]?.selected?.title }}</div>
                    }
                    <studio-record-table
                        [type]="column.type"
                        [parent]="column.parent"
                        [selectedId]="column.selected?.id ?? null"
                        [selectedType]="column.selected?.type"
                        [navigator]="true"
                        [editable]="false"
                        [version]="version() + bump()"
                        [actions]="rowActions(column)"
                        (selected)="advance(i, $event)"
                        (inspected)="inspected.emit($event)"
                        (action)="rowAction(i, $event.action, $event.row)"
                    />
                </section>
            }
            @if (columns().length === 1) {
                <section class="drilldown-hint" aria-label="Navigation help">
                    <span class="flow-mark" aria-hidden="true">→</span>
                    <h2>Select once. Keep moving.</h2>
                    <p>
                        Select a path to see its levels here, then a level for its units. Each
                        choice opens the next column, not another section below.
                    </p>
                    <div class="flow-example">
                        <span>Path</span><span>→</span><span>Level</span><span>→</span
                        ><span>Unit</span><span>→</span><span>Activity</span>
                    </div>
                    <p>
                        Use a row's edit button to change that record without losing your location,
                        or <strong>⋯</strong> to copy it. Each column's <strong>+</strong> adds a new
                        level, unit, lesson or exercise right there.
                    </p>
                </section>
            }
        </div>
        @if (dialog(); as request) {
            <studio-structure-dialog
                [request]="request"
                (closed)="dialog.set(null)"
                (done)="finished($event)"
            />
        }
        <p class="sr-only" aria-live="polite">{{ announcement() }}</p>
    `,
})
export class Drilldown {
    readonly initial = input.required<BrowseColumn[]>();
    readonly version = input(0);
    readonly inspected = output<Summary>();
    readonly changed = output<BrowseColumn[]>();
    readonly columns = signal<BrowseColumn[]>([]);
    readonly dialog = signal<StructureRequest | null>(null);
    readonly bump = signal(0);
    readonly announcement = signal("");
    private readonly board = viewChild.required<ElementRef<HTMLElement>>("board");
    private readonly element = inject<ElementRef<HTMLElement>>(ElementRef);
    constructor() {
        effect(() => this.columns.set(this.initial()));
    }

    advance(index: number, record: Summary) {
        const next = nextColumn(record);
        const parent = this.columns()[index].parent;
        if (next?.parent && record.type === "levels" && parent?.type === "paths")
            next.parent.pathId = parent.id;
        if (next?.parent && record.type === "units" && parent?.pathId)
            next.parent.pathId = parent.pathId;
        const columns = this.columns().slice(0, index + 1);
        columns[index] = { ...columns[index], selected: record };
        if (next) columns.push(next);
        this.columns.set(columns);
        this.changed.emit(columns);
        if (!next) this.inspected.emit(record);
        else
            requestAnimationFrame(() => {
                const board = this.board().nativeElement;
                board.scrollTo({ left: board.scrollWidth, behavior: "smooth" });
                this.element.nativeElement
                    .querySelector<HTMLElement>(".browse-column:last-child input")
                    ?.focus({ preventScroll: true });
            });
    }
    addMenu(index: number): MenuItem[] {
        const column = this.columns()[index];
        const parent = column.parent;
        if (column.type === "levels" && parent?.type === "paths") return [{ id: "levels", label: "+ Level" }];
        if (column.type === "units" && parent?.type === "levels") return [{ id: "units", label: "+ Unit" }];
        if (column.type === "activities")
            return [
                { id: "lessons", label: "New lesson", hint: "Blank lesson, added at the end" },
                { id: "exercises", label: "New exercise", hint: "Pick a type, then set it up" },
                { id: "link", label: "Add existing…", hint: "Search and reuse lessons or exercises" },
            ];
        if (index === 0 && column.type === "exercises")
            return [{ id: "exercises", label: "+ Exercise", hint: "New exercise, not yet in a unit" }];
        return [];
    }
    rowActions(column: BrowseColumn): string[] {
        if (column.type === "activities") return ["copy", "unlink"];
        if (column.type === "units" && column.parent?.type === "levels") return ["copy"];
        if (!column.parent && (column.type === "lessons" || column.type === "exercises")) return ["copy"];
        return [];
    }
    add(index: number, id: string) {
        const parent = this.parentOf(index);
        const parentTitle = this.columns()[index - 1]?.selected?.title ?? "";
        if (id === "link" && parent) this.dialog.set({ kind: "link", unit: parent, unitTitle: parentTitle });
        else this.dialog.set({ kind: "create", type: id as RecordType, parent, parentTitle });
    }
    rowAction(index: number, action: string, row: Summary) {
        const parent = this.parentOf(index);
        const parentTitle = this.columns()[index - 1]?.selected?.title ?? "";
        if (action === "unlink" && parent)
            this.dialog.set({ kind: "unlink", row, unit: parent, unitTitle: parentTitle });
        else if (action === "copy") this.dialog.set({ kind: "copy", row, parent, parentTitle });
    }
    finished(done: StructureDone) {
        const request = done.request;
        this.dialog.set(null);
        this.bump.update((n) => n + 1);
        const verbs = { create: "Created", link: "Added", copy: "Copied as", unlink: "Removed" };
        this.announcement.set(verbs[request.kind] + " " + done.title);
        const result = done.result;
        if (!result) return;
        const record: Summary = { id: result.id, type: result.type, title: done.title, subtitle: null, editor: null };
        const index = this.columns().findIndex((column) =>
            request.kind === "copy" || request.kind === "create"
                ? column.parent?.id === request.parent?.id && column.parent?.type === request.parent?.type
                : false,
        );
        if ((result.type === "levels" || result.type === "units") && index >= 0) this.advance(index, record);
        else this.inspected.emit(record);
    }
    private parentOf(index: number): Parent | null {
        const parent = this.columns()[index].parent;
        return parent ? { ...parent } : null;
    }
    jump(index: number) {
        const columns = this.columns().slice(0, Math.max(1, index + 1));
        if (columns[index]) columns[index] = { ...columns[index], selected: undefined };
        this.columns.set(columns);
        this.changed.emit(columns);
        requestAnimationFrame(() => {
            this.board().nativeElement.scrollLeft = this.board().nativeElement.scrollWidth;
        });
    }
}
