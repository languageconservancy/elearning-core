import { ChangeDetectionStrategy, Component, computed, input, output, signal } from "@angular/core";
import { AudioButton } from "./audio-button";
import { ContextPanel } from "./context-panel";
import { CardPreview, ContentReferences, FieldValue, MediaFile, Summary } from "./models";
import { MediaPicker } from "./media-picker";
import { RecordChooser } from "./record-chooser";

@Component({
    selector: "studio-content-asset",
    imports: [AudioButton, ContextPanel, MediaPicker, RecordChooser],
    changeDetection: ChangeDetectionStrategy.OnPush,
    template: `
        <div class="content-asset">
            <div class="asset-toolbar">
                <span>{{ label() }}</span>
                <button type="button" class="quiet" (click)="choosing.set(true)">
                    {{ value() ? "Replace" : "Choose" }}
                </button>
                @if (value() && record()) {
                    <button type="button" class="quiet" (click)="editing.set(true)">Edit</button>
                }
                @if (value()) {
                    <button type="button" class="quiet" (click)="changed.emit(null)">Clear</button>
                }
            </div>
            @if (card(); as preview) {
                <div class="asset-card-preview">
                    @if (preview.imageUrl) {
                        <img [src]="preview.imageUrl" alt="" loading="lazy" />
                    }
                    <div>
                        <strong>{{ preview.title }}</strong
                        ><small>{{ preview.english }}</small>
                        @for (audio of preview.audioUrls; track audio) {
                            <studio-audio-button [url]="audio" [label]="preview.title" />
                        }
                    </div>
                </div>
            } @else if (value()) {
                @if (kind() === "image" && url()) {
                    <img class="asset-image" [src]="url()" alt="Selected image" />
                } @else if (kind() === "audio" && url()) {
                    <studio-audio-button [url]="url()" [label]="label()" />
                } @else if (kind() === "video" && url()) {
                    <video [src]="url()" controls playsinline></video>
                } @else {
                    <small>{{ selected()?.title || label() + " #" + value() }}</small>
                }
            } @else {
                <small class="muted">Not selected</small>
            }
        </div>
        @if (choosing() || editing()) {
            @if (record(); as type) {
                <studio-record-chooser
                    [type]="type"
                    [selectedId]="numeric()"
                    [editId]="editing() ? numeric() : null"
                    (chosen)="chooseRecord($event)"
                    (updated)="refresh($event)"
                    (closed)="choosing.set(false); editing.set(false)"
                />
            } @else {
                <studio-context-panel [heading]="'Choose ' + label()" (closed)="choosing.set(false)">
                    <studio-media-picker [kind]="kind()" (selected)="chooseMedia($event)" />
                </studio-context-panel>
            }
        }
    `,
})
export class ContentAsset {
    readonly kind = input.required<string>();
    readonly label = input.required<string>();
    readonly value = input<FieldValue>(null);
    readonly references = input.required<ContentReferences>();
    readonly changed = output<number | null>();
    readonly choosing = signal(false);
    readonly editing = signal(false);
    readonly record = computed<"cards" | "groups" | null>(() =>
        this.kind() === "card" ? "cards" : this.kind() === "group" ? "groups" : null,
    );
    readonly numeric = computed(() => (this.value() ? Number(this.value()) : null));
    readonly localCard = signal<CardPreview | null>(null);
    readonly localSelection = signal<{ id: number; url?: string; title: string } | null>(null);
    readonly selected = computed(() =>
        this.localSelection()?.id === Number(this.value()) ? this.localSelection() : null,
    );
    readonly card = computed(() => {
        if (this.kind() !== "card" || !this.value()) return null;
        const local = this.localCard();
        return local?.id === Number(this.value())
            ? local
            : this.references().cards[Number(this.value())];
    });
    readonly url = computed(
        () => this.selected()?.url || this.references().files[Number(this.value())] || "",
    );
    chooseRecord(row: Summary) {
        this.refresh(row);
        if (row.id !== this.numeric()) this.changed.emit(row.id);
        this.choosing.set(false);
        this.editing.set(false);
    }
    /** Keep the preview in step with edits made to the chosen record. */
    refresh(row: Summary) {
        if (this.choosing() || row.id === this.numeric()) {
            this.localCard.set(row.previewCards?.[0] ?? null);
            this.localSelection.set({ id: row.id, title: row.title });
        }
    }
    chooseMedia(file: MediaFile) {
        this.localSelection.set({ id: file.id, url: file.url, title: file.name });
        this.changed.emit(file.id);
        this.choosing.set(false);
    }
}
