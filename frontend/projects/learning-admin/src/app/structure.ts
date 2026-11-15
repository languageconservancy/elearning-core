import {
    ChangeDetectionStrategy,
    Component,
    computed,
    effect,
    ElementRef,
    inject,
    input,
    output,
    signal,
    untracked,
    viewChild,
} from "@angular/core";
import { switchMap, timer } from "rxjs";
import { AudioButton } from "./audio-button";
import { ContextPanel } from "./context-panel";
import { CopyPlan, Parent, RecordType, StructureResult, Summary } from "./models";
import { errorMessage, WorkspaceApi } from "./workspace-api";

export interface MenuItem {
    id: string;
    label: string;
    hint?: string;
    danger?: boolean;
}

let nextMenuId = 0;

/** Compact button + popover menu. Positioned fixed so it is not clipped by scrolling lists. */
@Component({
    selector: "studio-action-menu",
    changeDetection: ChangeDetectionStrategy.OnPush,
    host: {
        "(document:pointerdown)": "outside($event)",
        "(window:scroll)": "open.set(false)",
        "(window:resize)": "open.set(false)",
    },
    template: `
        <button
            #trigger
            type="button"
            [class]="buttonClass()"
            aria-haspopup="menu"
            [attr.aria-expanded]="open()"
            [attr.aria-controls]="open() ? menuId : null"
            [attr.aria-label]="ariaLabel() || null"
            [title]="ariaLabel() || label()"
            (click)="toggle()"
            (keydown.arrowdown)="show($event)"
        >
            {{ label() }}
        </button>
        @if (open()) {
            <div
                #menu
                class="action-menu"
                role="menu"
                [id]="menuId"
                [style.top.px]="position().top"
                [style.left.px]="position().left"
                (keydown)="keys($event)"
            >
                @for (item of items(); track item.id) {
                    <button
                        type="button"
                        role="menuitem"
                        [class.danger]="item.danger"
                        (click)="choose(item)"
                    >
                        <span>{{ item.label }}</span>
                        @if (item.hint) {
                            <small>{{ item.hint }}</small>
                        }
                    </button>
                }
            </div>
        }
    `,
})
export class ActionMenu {
    readonly label = input.required<string>();
    readonly ariaLabel = input("");
    readonly buttonClass = input("quiet");
    readonly items = input.required<MenuItem[]>();
    readonly chosen = output<string>();
    readonly open = signal(false);
    readonly position = signal({ top: 0, left: 0 });
    readonly menuId = "action-menu-" + nextMenuId++;
    private readonly host = inject<ElementRef<HTMLElement>>(ElementRef);
    private readonly trigger = viewChild.required<ElementRef<HTMLButtonElement>>("trigger");
    private readonly menu = viewChild<ElementRef<HTMLElement>>("menu");

    toggle() {
        if (this.open()) this.open.set(false);
        else this.reveal();
    }
    show(event: Event) {
        event.preventDefault();
        this.reveal();
    }
    choose(item: MenuItem) {
        this.open.set(false);
        this.trigger().nativeElement.focus();
        this.chosen.emit(item.id);
    }
    outside(event: Event) {
        if (this.open() && !this.host.nativeElement.contains(event.target as Node)) this.open.set(false);
    }
    keys(event: KeyboardEvent) {
        const buttons = [...(this.menu()?.nativeElement.querySelectorAll<HTMLElement>("button") ?? [])];
        const index = buttons.indexOf(document.activeElement as HTMLElement);
        const moves: Record<string, number> = {
            ArrowDown: (index + 1) % buttons.length,
            ArrowUp: (index - 1 + buttons.length) % buttons.length,
            Home: 0,
            End: buttons.length - 1,
        };
        if (event.key in moves) {
            event.preventDefault();
            buttons[moves[event.key]]?.focus();
        } else if (event.key === "Escape" || event.key === "Tab") {
            if (event.key === "Escape") event.preventDefault();
            event.stopPropagation();
            this.open.set(false);
            this.trigger().nativeElement.focus();
        }
    }
    private reveal() {
        const rect = this.trigger().nativeElement.getBoundingClientRect();
        const width = 230;
        this.position.set({
            top: rect.bottom + 4,
            left: Math.max(8, Math.min(rect.right - width, window.innerWidth - width - 8)),
        });
        this.open.set(true);
        requestAnimationFrame(() => this.menu()?.nativeElement.querySelector<HTMLElement>("button")?.focus());
    }
}

export type StructureRequest =
    | { kind: "create"; type: RecordType; parent: Parent | null; parentTitle: string }
    | { kind: "link"; unit: Parent; unitTitle: string }
    | { kind: "copy"; row: Summary; parent: Parent | null; parentTitle: string }
    | { kind: "unlink"; row: Summary; unit: Parent; unitTitle: string };

export interface StructureDone {
    request: StructureRequest;
    result: StructureResult | null;
    title: string;
}

const NOUNS: Record<string, string> = {
    paths: "path", levels: "level", units: "unit", lessons: "lesson", exercises: "exercise",
};

/** Create, add-existing, copy and remove-from-unit dialogs for the learning hierarchy. */
@Component({
    selector: "studio-structure-dialog",
    imports: [AudioButton, ContextPanel],
    changeDetection: ChangeDetectionStrategy.OnPush,
    template: `
        <studio-context-panel [heading]="heading()" (closed)="close()">
            @switch (request().kind) {
                @case ("create") {
                    <form class="structure-form" (submit)="create($event)">
                        <p class="structure-where">
                            @if (createRequest()?.parent) {
                                In <strong>{{ createRequest()!.parentTitle }}</strong>, added at the end.
                            } @else {
                                Not placed in a unit yet. Add it to units later with “Add existing”.
                            }
                        </p>
                        <label
                            >Name <span class="required-mark" aria-hidden="true">*</span>
                            <input
                                #nameInput
                                name="name"
                                required
                                maxlength="255"
                                autocomplete="off"
                                [value]="name()"
                                (input)="name.set(value($event))"
                            />
                        </label>
                        @if (noun() === "unit" || noun() === "level") {
                            <label
                                >Description
                                @if (noun() === "unit") {
                                    <span class="required-mark" aria-hidden="true">*</span>
                                } @else {
                                    <small class="muted">optional</small>
                                }
                                <textarea
                                    name="description"
                                    rows="3"
                                    [required]="noun() === 'unit'"
                                    [value]="description()"
                                    (input)="description.set(value($event))"
                                ></textarea>
                            </label>
                        }
                        @if (noun() === "exercise") {
                            <fieldset class="type-chips">
                                <legend>Exercise type <span class="required-mark" aria-hidden="true">*</span></legend>
                                @for (type of exerciseTypes(); track type[0]) {
                                    <label class="chip" [class.on]="exerciseType() === type[0]">
                                        <input
                                            type="radio"
                                            name="exercise_type"
                                            required
                                            [value]="type[0]"
                                            [checked]="exerciseType() === type[0]"
                                            (change)="exerciseType.set(type[0])"
                                        />
                                        {{ type[1] }}
                                    </label>
                                }
                            </fieldset>
                            <p class="muted structure-note">
                                Opens on Setup next, where you choose the content source and what
                                learners see and answer with.
                            </p>
                        }
                        @if (error()) {
                            <p role="alert" class="error">{{ error() }}</p>
                        }
                        <div class="actions sticky-actions">
                            <button type="button" (click)="close()">Cancel</button>
                            <button class="primary" [disabled]="busy() || !canCreate()">
                                {{ busy() ? "Creating..." : "Create " + noun() }}
                            </button>
                        </div>
                    </form>
                }
                @case ("link") {
                    <div class="link-picker">
                        <div class="link-tools">
                            <div class="segmented" role="radiogroup" aria-label="Activity type">
                                @for (type of linkTypes; track type[0]) {
                                    <button
                                        type="button"
                                        role="radio"
                                        [attr.aria-checked]="linkType() === type[0]"
                                        [class.on]="linkType() === type[0]"
                                        (click)="linkType.set(type[0]); page.set(1)"
                                    >
                                        {{ type[1] }}
                                    </button>
                                }
                            </div>
                            <label class="search-field"
                                ><span class="sr-only">Search {{ linkType() }}</span>
                                <input
                                    #searchInput
                                    type="search"
                                    placeholder="Search name or ID"
                                    [value]="query()"
                                    (input)="query.set(value($event)); page.set(1)"
                                />
                            </label>
                        </div>
                        <div class="link-results" [attr.aria-busy]="loading()" role="group" aria-label="Choices">
                            @if (loading()) {
                                <p role="status" class="muted">Searching...</p>
                            }
                            @for (row of results(); track row.type + row.id) {
                                @let inUnit = existing().has(row.type + ":" + row.id);
                                <label class="link-row" [class.picked]="picked().has(row.type + ':' + row.id)" [class.in-unit]="inUnit">
                                    <input
                                        type="checkbox"
                                        [disabled]="inUnit"
                                        [checked]="inUnit || picked().has(row.type + ':' + row.id)"
                                        (change)="toggle(row)"
                                    />
                                    <span class="choice-copy">
                                        <strong>{{ row.title || "(Untitled)" }}</strong>
                                        <small>#{{ row.id }}{{ row.subtitle ? " · " + row.subtitle : "" }}{{ inUnit ? " · already in this unit" : "" }}</small>
                                        @if (row.metadata?.length) {
                                            <span class="row-metadata">{{ row.metadata!.join(" · ") }}</span>
                                        }
                                    </span>
                                    <span class="link-previews">
                                        @for (card of (row.previewCards ?? []).slice(0, 3); track card.id) {
                                            <span class="row-card-preview" [title]="card.title + ' / ' + card.english">
                                                @if (card.imageUrl) {
                                                    <img [src]="card.imageUrl" alt="" loading="lazy" />
                                                }
                                                <span>{{ card.title }}</span>
                                                @for (audio of card.audioUrls.slice(0, 1); track audio) {
                                                    <studio-audio-button [url]="audio" [label]="card.title" />
                                                }
                                            </span>
                                        }
                                    </span>
                                </label>
                            } @empty {
                                @if (!loading()) {
                                    <p class="empty">No {{ linkType() }} match this search.</p>
                                }
                            }
                        </div>
                        <div class="link-pages">
                            <span class="muted">{{ total() }} {{ linkType() }}</span>
                            <button type="button" class="quiet" [disabled]="page() <= 1 || loading()" (click)="page.set(page() - 1)">Previous</button>
                            <button type="button" class="quiet" [disabled]="page() * pageSize() >= total() || loading()" (click)="page.set(page() + 1)">Next</button>
                        </div>
                        @if (picked().size) {
                            <div class="picked-tags" aria-label="Selected activities">
                                @for (item of pickedList(); track item.type + item.id) {
                                    <span class="tag"
                                        >{{ item.type === "lessons" ? "Lesson" : "Exercise" }}: {{ item.title }}
                                        <button type="button" [attr.aria-label]="'Unselect ' + item.title" (click)="toggle(item)">×</button></span
                                    >
                                }
                            </div>
                        }
                        @if (error()) {
                            <p role="alert" class="error">{{ error() }}</p>
                        }
                        <div class="actions sticky-actions">
                            <span class="muted">Linked items are shared: editing one updates every unit that uses it.</span>
                            <button type="button" (click)="close()">Cancel</button>
                            <button type="button" class="primary" [disabled]="busy() || !picked().size" (click)="link()">
                                {{ busy() ? "Adding..." : "Add " + picked().size + " to " + linkRequest()?.unitTitle }}
                            </button>
                        </div>
                    </div>
                }
                @case ("copy") {
                    @if (plan(); as plan) {
                        <form class="structure-form" (submit)="copy($event)">
                            <p class="structure-where">
                                Copying <strong>{{ plan.title }}</strong>
                                @if (copyRequest()?.parent) {
                                    · placed right after it in <strong>{{ copyRequest()!.parentTitle }}</strong>
                                }
                            </p>
                            <ul class="plan-counts" aria-label="What this includes">
                                @if (plan.type === "units") {
                                    <li>{{ count(plan.lessons, "lesson") }}</li>
                                    <li>{{ count(plan.exercises, "exercise") }}</li>
                                }
                                @if (plan.frames || plan.type === "lessons") {
                                    <li>{{ count(plan.frames, "frame") }}</li>
                                }
                                @if (plan.items || plan.type === "exercises") {
                                    <li>{{ count(plan.items, "exercise item") }}</li>
                                }
                                <li>{{ count(plan.cards, "card") }}</li>
                                <li>{{ count(plan.groups, "card group") }}</li>
                            </ul>
                            <label
                                >Name of the copy
                                <input
                                    name="name"
                                    required
                                    maxlength="255"
                                    [value]="name()"
                                    (input)="name.set(value($event))"
                                />
                            </label>
                            @if (plan.type === "units") {
                                <fieldset class="copy-choice">
                                    <legend>Lessons & exercises</legend>
                                    <label [class.on]="activities() === 'link'"
                                        ><input type="radio" name="activities" [checked]="activities() === 'link'" (change)="activities.set('link')" />
                                        <span><strong>Share the same ones</strong><small>The new unit lists the same {{ count(plan.lessons + plan.exercises, "activity", "activities") }}. Edits show up in both units.</small></span></label
                                    >
                                    <label [class.on]="activities() === 'copy'"
                                        ><input type="radio" name="activities" [checked]="activities() === 'copy'" (change)="activities.set('copy')" />
                                        <span><strong>Make independent copies</strong><small>Each lesson and exercise is duplicated with its frames and items, named “… (copy)”.</small></span></label
                                    >
                                </fieldset>
                            }
                            <fieldset class="copy-choice" [disabled]="sharesActivities()">
                                <legend>Cards <span class="muted">({{ plan.cards }})</span></legend>
                                <label [class.on]="cards() === 'link'"
                                    ><input type="radio" name="cards" [checked]="cards() === 'link'" (change)="cards.set('link')" />
                                    <span><strong>Reuse existing cards</strong><small>Recommended. Card fixes apply everywhere.</small></span></label
                                >
                                <label [class.on]="cards() === 'copy'"
                                    ><input type="radio" name="cards" [checked]="cards() === 'copy'" (change)="cards.set('copy')" />
                                    <span><strong>Duplicate cards</strong><small>Creates {{ count(plan.cards, "new card") }} you can change independently.</small></span></label
                                >
                            </fieldset>
                            @if (plan.groups) {
                                <fieldset class="copy-choice" [disabled]="sharesActivities()">
                                    <legend>Card groups <span class="muted">({{ plan.groups }})</span></legend>
                                    <label [class.on]="groups() === 'link'"
                                        ><input type="radio" name="groups" [checked]="groups() === 'link'" (change)="groups.set('link')" />
                                        <span><strong>Reuse existing groups</strong><small>Group membership stays shared.</small></span></label
                                    >
                                    <label [class.on]="groups() === 'copy'"
                                        ><input type="radio" name="groups" [checked]="groups() === 'copy'" (change)="groups.set('copy')" />
                                        <span><strong>Duplicate groups</strong><small>New groups with the same members{{ cards() === "copy" ? " (using the duplicated cards)" : "" }}.</small></span></label
                                    >
                                </fieldset>
                            }
                            @if (sharesActivities()) {
                                <p class="muted structure-note">Shared activities keep their cards and groups, so those options don't apply.</p>
                            }
                            <p class="muted structure-note">Images, audio and video files are always shared, never duplicated.</p>
                            @if (error()) {
                                <p role="alert" class="error">{{ error() }}</p>
                            }
                            <div class="actions sticky-actions">
                                <button type="button" (click)="close()">Cancel</button>
                                <button class="primary" [disabled]="busy() || !name().trim()">
                                    {{ busy() ? "Copying..." : "Copy " + noun() }}
                                </button>
                            </div>
                        </form>
                    } @else if (error()) {
                        <p role="alert" class="error">{{ error() }}</p>
                    } @else {
                        <p role="status" class="muted">Checking what this {{ noun() }} contains...</p>
                    }
                }
                @case ("unlink") {
                    <p>
                        Remove <strong>{{ unlinkRequest()?.row?.title }}</strong> from
                        <strong>{{ unlinkRequest()?.unitTitle }}</strong>?
                    </p>
                    <p class="muted">
                        Only the link is removed. The {{ noun() }}, its content and its use in any other
                        unit are kept, and you can add it back with “Add existing”.
                    </p>
                    @if (error()) {
                        <p role="alert" class="error">{{ error() }}</p>
                    }
                    <div class="actions">
                        <button type="button" (click)="close()">Keep it</button>
                        <button type="button" class="danger" [disabled]="busy()" (click)="unlink()">
                            {{ busy() ? "Removing..." : "Remove from unit" }}
                        </button>
                    </div>
                }
            }
        </studio-context-panel>
    `,
})
export class StructureDialog {
    readonly request = input.required<StructureRequest>();
    readonly done = output<StructureDone>();
    readonly closed = output<void>();
    readonly api = inject(WorkspaceApi);
    readonly name = signal("");
    readonly description = signal("");
    readonly exerciseType = signal("");
    readonly busy = signal(false);
    readonly error = signal("");
    readonly plan = signal<CopyPlan | null>(null);
    readonly activities = signal<"link" | "copy">("link");
    readonly cards = signal<"link" | "copy">("link");
    readonly groups = signal<"link" | "copy">("link");
    readonly linkTypes: [RecordType, string][] = [["lessons", "Lessons"], ["exercises", "Exercises"]];
    readonly linkType = signal<RecordType>("lessons");
    readonly query = signal("");
    readonly page = signal(1);
    readonly pageSize = signal(25);
    readonly total = signal(0);
    readonly loading = signal(false);
    readonly results = signal<Summary[]>([]);
    readonly existing = signal(new Set<string>());
    readonly picked = signal(new Map<string, Summary>());
    readonly pickedList = computed(() => [...this.picked().values()]);
    readonly createRequest = computed(() => {
        const request = this.request();
        return request.kind === "create" ? request : null;
    });
    readonly linkRequest = computed(() => {
        const request = this.request();
        return request.kind === "link" ? request : null;
    });
    readonly copyRequest = computed(() => {
        const request = this.request();
        return request.kind === "copy" ? request : null;
    });
    readonly unlinkRequest = computed(() => {
        const request = this.request();
        return request.kind === "unlink" ? request : null;
    });
    readonly noun = computed(() => {
        const request = this.request();
        const type = request.kind === "create" ? request.type : request.kind === "link" ? "lessons" : request.row.type;
        return NOUNS[type] ?? "record";
    });
    readonly heading = computed(() => {
        const request = this.request();
        switch (request.kind) {
            case "create":
                return "New " + this.noun();
            case "link":
                return "Add existing lessons & exercises";
            case "copy":
                return "Copy " + this.noun();
            default:
                return "Remove from unit?";
        }
    });
    readonly exerciseTypes = computed(() => Object.entries(this.api.config()?.exerciseTypes ?? {}));
    readonly sharesActivities = computed(() => this.request().kind === "copy" && this.plan()?.type === "units" && this.activities() === "link");
    readonly canCreate = computed(
        () =>
            !!this.name().trim() &&
            (this.noun() !== "unit" || !!this.description().trim()) &&
            (this.noun() !== "exercise" || !!this.exerciseType()),
    );
    private readonly nameInput = viewChild<ElementRef<HTMLInputElement>>("nameInput");
    private readonly searchInput = viewChild<ElementRef<HTMLInputElement>>("searchInput");

    constructor() {
        effect(() => {
            const request = this.request();
            untracked(() => {
                this.error.set("");
                if (request.kind === "copy") {
                    this.api.copyPlan(request.row.type, request.row.id).subscribe({
                        next: (plan) => {
                            this.plan.set(plan);
                            this.name.set(plan.name);
                        },
                        error: (error: unknown) => this.error.set(errorMessage(error)),
                    });
                } else if (request.kind === "link") {
                    this.loadExisting(request.unit);
                }
                requestAnimationFrame(() => (this.nameInput() ?? this.searchInput())?.nativeElement.focus());
            });
        });
        effect((cleanup) => {
            if (this.request().kind !== "link") return;
            const type = this.linkType(),
                q = this.query(),
                page = this.page();
            this.loading.set(true);
            const subscription = timer(q ? 250 : 0)
                .pipe(switchMap(() => this.api.records(type, q, page)))
                .subscribe({
                    next: (result) => {
                        this.results.set(result.items);
                        this.total.set(result.total);
                        this.pageSize.set(result.pageSize || 25);
                        this.loading.set(false);
                    },
                    error: (error: unknown) => {
                        this.error.set(errorMessage(error));
                        this.loading.set(false);
                    },
                });
            cleanup(() => subscription.unsubscribe());
        });
    }

    count(n: number, singular: string, plural = singular + "s") {
        return n + " " + (n === 1 ? singular : plural);
    }
    value(event: Event) {
        return (event.target as HTMLInputElement | HTMLTextAreaElement).value;
    }
    close() {
        if (!this.busy()) this.closed.emit();
    }
    toggle(row: Summary) {
        const picked = new Map(this.picked());
        const key = row.type + ":" + row.id;
        if (picked.has(key)) picked.delete(key);
        else picked.set(key, row);
        this.picked.set(picked);
    }
    create(event: Event) {
        event.preventDefault();
        const request = this.createRequest();
        if (!request || !this.canCreate() || this.busy()) return;
        const fields: Record<string, string> = { name: this.name().trim() };
        if (this.description().trim()) fields["description"] = this.description().trim();
        if (request.type === "exercises") fields["exercise_type"] = this.exerciseType();
        this.run(this.api.create(request.type, fields, request.parent), fields["name"]);
    }
    link() {
        const request = this.linkRequest();
        if (!request || !this.picked().size || this.busy()) return;
        const items = this.pickedList().map((row) => ({ type: row.type, id: row.id }));
        this.busy.set(true);
        this.error.set("");
        this.api.link(request.unit.id, request.unit.pathId ?? null, items).subscribe({
            next: () => this.done.emit({ request, result: null, title: this.count(items.length, "activity", "activities") }),
            error: (error: unknown) => {
                this.error.set(errorMessage(error));
                this.busy.set(false);
            },
        });
    }
    copy(event: Event) {
        event.preventDefault();
        const request = this.copyRequest();
        if (!request || this.busy()) return;
        const shared = this.sharesActivities();
        this.run(
            this.api.copy(request.row.type, request.row.id, {
                name: this.name().trim(),
                activities: this.activities(),
                cards: shared ? "link" : this.cards(),
                groups: shared ? "link" : this.groups(),
                parent: request.parent,
            }),
            this.name().trim(),
        );
    }
    unlink() {
        const request = this.unlinkRequest();
        if (!request?.row.linkId || this.busy()) return;
        this.busy.set(true);
        this.api.unlink(request.unit.id, request.row.linkId).subscribe({
            next: () => this.done.emit({ request, result: null, title: request.row.title }),
            error: (error: unknown) => {
                this.error.set(errorMessage(error));
                this.busy.set(false);
            },
        });
    }
    private run(call: ReturnType<WorkspaceApi["create"]>, title: string) {
        this.busy.set(true);
        this.error.set("");
        call.subscribe({
            next: (result) => this.done.emit({ request: this.request(), result, title }),
            error: (error: unknown) => {
                this.error.set(errorMessage(error));
                this.busy.set(false);
            },
        });
    }
    private loadExisting(unit: Parent) {
        this.api.records("activities", "", 1, "", "asc", unit).subscribe({
            next: (page) => this.existing.set(new Set(page.items.map((row) => row.type + ":" + row.id))),
            error: () => this.existing.set(new Set()),
        });
    }
}
