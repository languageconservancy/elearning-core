import {
    ChangeDetectionStrategy,
    Component,
    computed,
    effect,
    inject,
    input,
    output,
    signal,
    untracked,
} from "@angular/core";
import { FormControl, FormRecord, ReactiveFormsModule } from "@angular/forms";
import { ContextPanel } from "./context-panel";
import { fieldLabel, FieldValue, MediaFile, RecordDetail, RecordType, Summary } from "./models";
import { MediaPicker } from "./media-picker";
import { RecordTable } from "./record-table";
import { errorMessage, WorkspaceApi } from "./workspace-api";
import { ReferenceSelect } from "./reference-select";
import { RecordChooser } from "./record-chooser";
import { ExerciseEditor } from "./exercise-editor";
import { LessonEditor } from "./lesson-editor";

@Component({
    selector: "studio-record-editor",
    imports: [
        ReactiveFormsModule,
        ContextPanel,
        MediaPicker,
        RecordTable,
        ReferenceSelect,
        ExerciseEditor,
        LessonEditor,
        RecordChooser,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
    template: `
        <article class="editor">
            <header class="record-header">
                <div>
                    <div class="eyebrow">
                        {{ definition().label }} /
                        {{ record() ? "#" + record()!.id : "New record" }}
                    </div>
                    <h2>{{ record()?.title || "Create " + definition().label.toLowerCase() }}</h2>
                </div>
                <span class="badge">{{
                    dirty() || contentDirty() ? "Unsaved changes" : "Saved"
                }}</span>
            </header>
            <nav class="tabs" aria-label="Record sections">
                @for (section of sections(); track section) {
                    <button
                        type="button"
                        [attr.aria-pressed]="tab() === section"
                        (click)="tab.set(section)"
                    >
                        {{ section }}
                    </button>
                }
            </nav>
            @if (error()) {
                <p class="error" role="alert">{{ error() }}</p>
            }
            @if (notice()) {
                <p class="success" role="status">{{ notice() }}</p>
            }
            @if (record(); as current) {
                <section class="record-content" [hidden]="tab() !== 'Content'">
                    @if (type() === "exercises") {
                        <studio-exercise-editor
                            [id]="current.id"
                            (saved)="contentSaved()"
                            (dirtyChange)="contentChanged($event)"
                        />
                    } @else if (type() === "lessons") {
                        <studio-lesson-editor
                            [id]="current.id"
                            (saved)="contentSaved()"
                            (dirtyChange)="contentChanged($event)"
                        />
                    }
                </section>
            }
            <form
                [formGroup]="form"
                (ngSubmit)="save()"
                [hidden]="tab() !== 'Overview'"
                class="field-grid"
            >
                @for (field of definition().fields; track field) {
                    <label [class.full-width]="field === 'description'">
                        {{ label(field) }}
                        {{ definition().required.includes(field) ? "*" : "(optional)" }}
                        @if (definition().references?.[field]; as reference) {
                            <studio-reference-select
                                [reference]="reference"
                                [label]="label(field)"
                                [value]="form.controls[field].value"
                                (chosen)="setReference(field, $event)"
                            />
                        } @else if (definition().options?.[field]; as options) {
                            <select [formControlName]="field">
                                @for (option of options; track option) {
                                    <option [value]="option">{{ option }}</option>
                                }
                            </select>
                        } @else if (field === "description") {
                            <textarea
                                [formControlName]="field"
                                [required]="definition().required.includes(field)"
                                rows="4"
                            ></textarea>
                        } @else {
                            <input
                                [formControlName]="field"
                                [required]="definition().required.includes(field)"
                                maxlength="255"
                            />
                        }
                    </label>
                }
                @if (type() === "groups" && !record()) {
                    <div class="full-width">
                        <label>First card *</label>
                        <button type="button" (click)="selectingCard.set(true)">
                            {{ initialCard()?.title || "Choose a card" }}
                        </button>
                        <p class="muted">
                            A group must start with at least one card. More can be added in
                            Relationships.
                        </p>
                    </div>
                }
                <p class="muted full-width">
                    * Required. Identifiers and timestamps are generated by the system.
                </p>
                <button type="submit" class="sr-only" tabindex="-1">Save record</button>
            </form>
            <section [hidden]="tab() !== 'Relationships'">
                @if (record(); as current) {
                    <div class="relationship-summary">
                        @for (relation of current.relations; track relation.type) {
                            <button
                                type="button"
                                [attr.aria-pressed]="relationType() === relation.type"
                                (click)="relationType.set(relation.type)"
                            >
                                {{ relation.label }} <span class="count">{{ relation.count }}</span>
                            </button>
                        } @empty {
                            <p>No supported relationships for this record.</p>
                        }
                    </div>
                    <p class="muted">
                        Open a related record in the side drawer.
                        {{
                            type() === "groups"
                                ? "Removing a card only removes its group association."
                                : "Use the advanced editor to change structural associations."
                        }}
                    </p>
                    @if (type() === "groups") {
                        <button
                            type="button"
                            (click)="selectingCard.set(true)"
                            [disabled]="dirty() || saving()"
                        >
                            + Add card
                        </button>
                        @if (dirty()) {
                            <p class="muted">
                                Save or discard field edits before changing group membership.
                            </p>
                        }
                    }
                    @if (relationType(); as target) {
                        <studio-record-table
                            [type]="target"
                            [parent]="parent()"
                            (selected)="inspect.emit($event)"
                            [removable]="type() === 'groups' && !dirty() && !saving()"
                            (removed)="removeCard.set($event)"
                        />
                    }
                } @else {
                    <p>Save this record before managing relationships.</p>
                }
            </section>
            <section [hidden]="tab() !== 'Media'">
                <div class="media-grid">
                    @for (entry of media(); track entry.field) {
                        <section class="media-card">
                            <h3>{{ entry.kind }}</h3>
                            @if (entry.url) {
                                @if (entry.kind === "image") {
                                    <img
                                        class="media-preview"
                                        [src]="entry.url"
                                        alt="Record image"
                                    />
                                } @else if (entry.kind === "audio") {
                                    <audio [src]="entry.url" controls></audio>
                                } @else {
                                    <video [src]="entry.url" controls playsinline></video>
                                }
                            } @else {
                                <p class="muted">
                                    {{
                                        entry.missing
                                            ? "Referenced file is missing."
                                            : "No media attached."
                                    }}
                                </p>
                            }
                            <div class="actions">
                                <button type="button" (click)="mediaField.set(entry.field)">
                                    Choose / record {{ entry.kind }}
                                </button>
                                @if (form.controls[entry.field].value) {
                                    <button type="button" (click)="removeMedia(entry.field)">
                                        Detach
                                    </button>
                                }
                            </div>
                            <small class="muted"
                                >Detaching removes only this association, not the file.</small
                            >
                        </section>
                    } @empty {
                        <p class="muted">
                            Media for this record is managed within its frames or options in the
                            advanced editor.
                        </p>
                    }
                </div>
            </section>
            <section [hidden]="tab() !== 'Details'">
                <dl class="details-grid">
                    <dt>Record ID</dt>
                    <dd>{{ record()?.id || "Not saved" }}</dd>
                    <dt>Created</dt>
                    <dd>{{ record()?.created || "Not available" }}</dd>
                    <dt>Last modified</dt>
                    <dd>{{ record()?.modified || "Not available" }}</dd>
                </dl>
                <details>
                    <summary>Advanced editing and dependencies</summary>
                    <p>
                        Use Content to edit lesson frames, blocks, and exercise options. Hierarchy
                        changes and record deletion remain in the advanced editor with its
                        dependency checks.
                    </p>
                    <p>
                        Inspect relationship counts before deleting a referenced record. Removing an
                        association is not the same as deleting a record.
                    </p>
                    @if (record()?.editor; as editor) {
                        <a [href]="editor" target="_blank" rel="noopener"
                            >Open advanced editor in a new tab</a
                        >
                    }
                </details>
                <p class="muted">
                    Workspace saves are logged server-side. Detailed per-record audit history and
                    undo are not available in this migration.
                </p>
            </section>
            <footer class="editor-actions" [hidden]="tab() === 'Content'">
                <span>{{
                    saving() ? "Saving..." : dirty() ? "Changes are not saved" : "Up to date"
                }}</span>
                <div class="actions">
                    <button
                        type="button"
                        [disabled]="saving() || !dirty()"
                        (click)="requestDiscard()"
                    >
                        Discard changes
                    </button>
                    <button
                        type="button"
                        class="primary"
                        [disabled]="saving() || (!dirty() && !!record())"
                        (click)="save()"
                    >
                        Save changes
                    </button>
                </div>
            </footer>
        </article>
        @if (mediaField(); as field) {
            <studio-context-panel
                [heading]="'Choose ' + definition().media[field]"
                (closed)="mediaField.set(null)"
            >
                <studio-media-picker
                    [kind]="definition().media[field]"
                    (selected)="attachMedia(field, $event)"
                />
            </studio-context-panel>
        }
        @if (confirmDiscard()) {
            <studio-context-panel
                heading="Discard unsaved changes?"
                (closed)="confirmDiscard.set(false)"
            >
                <p>Saved data will not be changed. Your current edits will be lost.</p>
                <div class="actions">
                    <button type="button" (click)="confirmDiscard.set(false)">Keep editing</button>
                    <button type="button" class="danger" (click)="discard()">Discard edits</button>
                </div>
            </studio-context-panel>
        }
        @if (selectingCard()) {
            <studio-record-chooser
                type="cards"
                (chosen)="chooseCard($event)"
                (closed)="selectingCard.set(false)"
            />
        }
        @if (removeCard(); as card) {
            <studio-context-panel
                heading="Remove this association?"
                (closed)="removeCard.set(null)"
            >
                <p>
                    Remove {{ card.title }} from this group? The card itself, its media, and its
                    other uses will not be deleted.
                </p>
                <div class="actions">
                    <button type="button" (click)="removeCard.set(null)">Cancel</button>
                    <button
                        type="button"
                        class="danger"
                        [disabled]="saving()"
                        (click)="membership(card, 'remove')"
                    >
                        Remove association
                    </button>
                </div>
            </studio-context-panel>
        }
    `,
})
export class RecordEditor {
    readonly type = input.required<RecordType>();
    readonly record = input<RecordDetail | null>(null);
    readonly saved = output<RecordDetail>();
    readonly inspect = output<Summary>();
    readonly dirtyChange = output<boolean>();
    readonly sections = computed(() =>
        this.record() && ["exercises", "lessons"].includes(this.type())
            ? ["Content", "Overview", "Relationships", "Details"]
            : ["Overview", "Relationships", "Media", "Details"],
    );
    readonly tab = signal("Overview");
    readonly relationType = signal<RecordType | null>(null);
    readonly mediaField = signal<string | null>(null);
    readonly confirmDiscard = signal(false);
    readonly selectingCard = signal(false);
    readonly initialCard = signal<Summary | null>(null);
    readonly removeCard = signal<Summary | null>(null);
    readonly error = signal("");
    readonly notice = signal("");
    readonly saving = signal(false);
    readonly dirty = signal(false);
    readonly contentDirty = signal(false);
    readonly form = new FormRecord<FormControl<FieldValue>>({});
    readonly api = inject(WorkspaceApi);
    readonly definition = computed(() => this.api.config()!.types[this.type()]);
    readonly parent = computed(() => ({ type: this.type(), id: this.record()!.id }));
    private readonly localMedia = signal<Record<string, MediaFile | null>>({});
    readonly media = computed(() =>
        Object.entries(this.definition().media).map(([field, kind]) => {
            const local = this.localMedia();
            const original = this.record()?.media.find((m) => m.field === field);
            return {
                field,
                kind,
                url: field in local ? local[field]?.url : original?.url,
                missing: original?.missing,
            };
        }),
    );
    readonly label = fieldLabel;
    /** undefined until the first record (or blank form) has been shown. */
    private loadedId: number | null | undefined = undefined;

    constructor() {
        effect(() => {
            const record = this.record(),
                definition = this.definition();
            untracked(() => {
                if (this.loadedId === record?.id && this.dirty()) return;
                if (this.loadedId !== (record?.id ?? null)) {
                    // A group that was just created has no cards beyond the first; go add them.
                    const created = this.loadedId === null && !!record;
                    this.tab.set(
                        record && ["exercises", "lessons"].includes(this.type())
                            ? "Content"
                            : created && this.type() === "groups"
                              ? "Relationships"
                              : "Overview",
                    );
                    this.contentDirty.set(false);
                }
                this.loadedId = record?.id ?? null;
                this.reset(record, definition.fields.concat(Object.keys(definition.media)));
                this.relationType.set(record?.relations[0]?.type ?? null);
                this.error.set("");
            });
        });
        this.form.valueChanges.subscribe(() => {
            this.dirty.set(this.form.dirty);
            this.emitDirty();
        });
    }

    private reset(record: RecordDetail | null, fields: string[]) {
        for (const key of Object.keys(this.form.controls))
            this.form.removeControl(key, { emitEvent: false });
        for (const field of fields) {
            this.form.addControl(
                field,
                new FormControl<FieldValue>(
                    record ? (record.fields[field] ?? null) : (this.definition().defaults?.[field] ?? null),
                ),
                { emitEvent: false },
            );
        }
        this.form.markAsPristine();
        this.dirty.set(false);
        this.emitDirty();
        this.localMedia.set({});
    }
    private emitDirty() {
        this.dirtyChange.emit(this.dirty() || this.contentDirty());
    }
    contentChanged(dirty: boolean) {
        this.contentDirty.set(dirty);
        this.emitDirty();
    }
    contentSaved() {
        this.api.record(this.type(), this.record()!.id).subscribe({
            next: (record) => this.saved.emit(record),
            error: (error: unknown) =>
                this.error.set(
                    "Content was saved, but record details could not refresh: " +
                        errorMessage(error),
                ),
        });
    }
    attachMedia(field: string, file: MediaFile) {
        this.form.controls[field].setValue(file.id);
        this.markDirty();
        this.localMedia.update((value) => ({ ...value, [field]: file }));
        this.mediaField.set(null);
    }
    setReference(field: string, id: number) {
        this.form.controls[field].setValue(id);
        this.markDirty();
    }
    chooseCard(card: Summary) {
        if (!this.record()) {
            this.initialCard.set(card);
            this.selectingCard.set(false);
            this.markDirty();
        } else this.membership(card, "add");
    }
    membership(card: Summary, operation: "add" | "remove") {
        if (this.saving() || !this.record()) return;
        this.saving.set(true);
        this.error.set("");
        this.api
            .membership(this.record()!.id, card.id, operation, this.record()!.revision)
            .subscribe({
                next: (record) => {
                    this.saving.set(false);
                    this.selectingCard.set(false);
                    this.removeCard.set(null);
                    this.saved.emit(record);
                    this.notice.set(
                        operation === "add"
                            ? "Card added to group."
                            : "Association removed. Card not deleted.",
                    );
                },
                error: (error: unknown) => {
                    this.saving.set(false);
                    this.selectingCard.set(false);
                    this.removeCard.set(null);
                    this.error.set(errorMessage(error));
                },
            });
    }
    removeMedia(field: string) {
        this.form.controls[field].setValue(null);
        this.markDirty();
        this.localMedia.update((value) => ({ ...value, [field]: null }));
    }
    private markDirty() {
        this.form.markAsDirty();
        this.dirty.set(true);
        this.emitDirty();
        this.notice.set("");
    }
    requestDiscard() {
        this.confirmDiscard.set(true);
    }
    discard() {
        this.reset(
            this.record(),
            this.definition().fields.concat(Object.keys(this.definition().media)),
        );
        this.confirmDiscard.set(false);
        this.error.set("");
        this.initialCard.set(null);
    }
    save() {
        if (this.saving()) return;
        const values = this.form.getRawValue();
        const fields = this.record()
            ? Object.fromEntries(
                  Object.entries(values).filter(
                      ([key, value]) => value !== this.record()!.fields[key],
                  ),
              )
            : values;
        for (const field of this.definition().required) {
            if (!String(values[field] ?? "").trim()) {
                this.error.set(`${this.label(field)} is required.`);
                return;
            }
        }
        this.error.set("");
        this.notice.set("");
        this.saving.set(true);
        if (this.type() === "groups" && !this.record() && !this.initialCard()) {
            this.saving.set(false);
            this.error.set("Choose the first card for this group.");
            return;
        }
        this.api
            .save(
                this.type(),
                this.record()?.id ?? null,
                fields,
                this.record()?.revision,
                this.initialCard()?.id,
            )
            .subscribe({
                next: (record) => {
                    this.saving.set(false);
                    this.form.markAsPristine();
                    this.dirty.set(false);
                    this.emitDirty();
                    this.saved.emit(record);
                    this.notice.set("Changes saved.");
                },
                error: (error: unknown) => {
                    this.saving.set(false);
                    this.error.set(errorMessage(error));
                },
            });
    }
}
