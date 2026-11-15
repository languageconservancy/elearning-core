import { ChangeDetectionStrategy, Component, effect, inject, input, OnDestroy, output, signal } from "@angular/core";
import { switchMap, timer } from "rxjs";
import { ArchiveImage, MediaFile } from "./models";
import { errorMessage, WorkspaceApi } from "./workspace-api";

@Component({
    selector: "studio-media-picker",
    changeDetection: ChangeDetectionStrategy.OnPush,
    template: `
        <div class="tabs" aria-label="Media source">
            <button type="button" [attr.aria-pressed]="source() === 'library'" (click)="source.set('library')">Site library</button>
            @if (kind() === "image") {
                <button type="button" [attr.aria-pressed]="source() === 'archive'" (click)="source.set('archive')">Shared image archive</button>
            }
            <button type="button" [attr.aria-pressed]="source() === 'upload'" (click)="source.set('upload')">Upload / record</button>
        </div>
        @if (error()) { <p class="error" role="alert">{{ error() }}</p> }
        @if (source() !== "upload") {
            <label class="search-field">Search {{ source() === "archive" ? "shared archive" : "site library" }}
                <input type="search" [value]="query()" (input)="search($event)" placeholder="Search filenames" />
            </label>
            @if (loading()) { <p role="status">Loading media...</p> }
            <div class="image-grid" [attr.aria-busy]="loading()">
                @for (image of images(); track image.key) {
                    <button class="image-choice" type="button" [disabled]="busy()" (click)="importImage(image)">
                        <img [src]="image.url" alt="" loading="lazy" /><span>{{ image.name }}</span>
                    </button>
                }
                @for (file of files(); track file.id) {
                    <button class="image-choice" type="button" [disabled]="busy()" (click)="selected.emit(file)">
                        @if (kind() === "image") { <img [src]="file.url" alt="" loading="lazy" /> }
                        <span>{{ file.name }} <small>#{{ file.id }}</small></span>
                    </button>
                }
            </div>
            @if (!loading() && !images().length && !files().length) { <p class="empty">No matching media.</p> }
            <footer class="pagination">
                <span>{{ total() }} files · Page {{ page() }}</span>
                <button type="button" [disabled]="page() <= 1 || loading()" (click)="page.set(page() - 1)">Previous</button>
                <button type="button" [disabled]="page() * 24 >= total() || loading()" (click)="page.set(page() + 1)">Next</button>
            </footer>
        } @else {
            <label>Upload {{ kind() }}
                <input type="file" [accept]="kind() + '/*'" (change)="chooseFile($event)" [disabled]="busy() || recording()" />
            </label>
            <p class="muted">Maximum 50 MB, subject to the server's upload limit. New media is saved in the site library; attach it by saving the record.</p>
            @if (kind() !== "image") {
                <section class="capture">
                    <h3>Record {{ kind() }}</h3>
                    <p class="muted">Requires HTTPS or localhost and permission to use your {{ kind() === "video" ? "camera and microphone" : "microphone" }}. Capture stops after 2 minutes or 45 MB.</p>
                    @if (stream() && kind() === "video") {
                        <video [srcObject]="stream()" autoplay muted playsinline aria-label="Live camera preview"></video>
                    }
                    @if (recording()) {
                        <p role="status">Recording · {{ seconds() }} seconds</p>
                        <button type="button" class="danger" (click)="stop()">Stop recording</button>
                    } @else {
                        <button type="button" (click)="start()" [disabled]="busy() || starting()">Start recording</button>
                    }
                </section>
            }
            @if (preview()) {
                <section class="capture">
                    <h3>Preview before upload</h3>
                    @if (kind() === "video") { <video [src]="preview()" controls playsinline></video> }
                    @else if (kind() === "audio") { <audio [src]="preview()" controls></audio> }
                    @else { <img class="media-preview" [src]="preview()" alt="Selected image" /> }
                    <label>Media name<input #name [value]="filename()" maxlength="200" /></label>
                    <button type="button" class="primary" [disabled]="busy() || recording()" (click)="upload(name.value)">Upload and select</button>
                </section>
            }
        }
        @if (busy()) { <p role="status">Saving media...</p> }
    `,
})
export class MediaPicker implements OnDestroy {
    readonly kind = input.required<string>();
    readonly selected = output<MediaFile>();
    readonly source = signal("library");
    readonly query = signal("");
    readonly page = signal(1);
    readonly total = signal(0);
    readonly images = signal<ArchiveImage[]>([]);
    readonly files = signal<MediaFile[]>([]);
    readonly error = signal("");
    readonly busy = signal(false);
    readonly loading = signal(false);
    readonly starting = signal(false);
    readonly recording = signal(false);
    readonly stream = signal<MediaStream | null>(null);
    readonly seconds = signal(0);
    readonly preview = signal("");
    readonly filename = signal("");
    private blob: Blob | null = null;
    private recorder: MediaRecorder | null = null;
    private interval: ReturnType<typeof setInterval> | null = null;
    private alive = true;
    private readonly api = inject(WorkspaceApi);

    constructor() {
        effect((cleanup) => {
            const source = this.source(), q = this.query(), page = this.page(), kind = this.kind();
            this.images.set([]); this.files.set([]); this.error.set("");
            if (source === "upload") { this.loading.set(false); return; }
            this.loading.set(true);
            const subscription = timer(250).pipe(switchMap(() =>
                source === "archive" ? this.api.archive(q, page) : this.api.media(kind, q, page),
            )).subscribe({
                next: (result) => {
                    if (source === "archive") this.images.set(result.items.filter((item): item is ArchiveImage => "key" in item));
                    else this.files.set(result.items.filter((item): item is MediaFile => "id" in item));
                    this.total.set(result.total); this.loading.set(false);
                },
                error: (error: unknown) => { this.error.set(errorMessage(error)); this.loading.set(false); },
            });
            cleanup(() => subscription.unsubscribe());
        });
        effect(() => { this.source(); this.page.set(1); });
    }

    search(event: Event) { this.query.set((event.target as HTMLInputElement).value); this.page.set(1); }
    importImage(image: ArchiveImage) {
        if (this.busy()) return;
        this.busy.set(true); this.error.set("");
        this.api.importImage(image.key).subscribe({
            next: (file) => { this.busy.set(false); this.selected.emit(file); },
            error: (error: unknown) => { this.busy.set(false); this.error.set(errorMessage(error)); },
        });
    }

    chooseFile(event: Event) {
        const file = (event.target as HTMLInputElement).files?.[0];
        if (!file) return;
        if (!file.type.startsWith(this.kind() + "/") || file.size > 50 * 1024 * 1024) {
            this.error.set("Choose a matching media file under 50 MB."); return;
        }
        this.setBlob(file, file.name);
    }

    async start() {
        this.error.set("");
        if (!navigator.mediaDevices?.getUserMedia || typeof MediaRecorder === "undefined") {
            this.error.set("Recording is not supported here. Use HTTPS or upload an existing media file."); return;
        }
        this.starting.set(true);
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true, video: this.kind() === "video" });
            if (!this.alive) { stream.getTracks().forEach((track) => track.stop()); return; }
            this.stream.set(stream);
            const candidates = this.kind() === "audio"
                ? ["audio/webm;codecs=opus", "audio/mp4", "audio/ogg"]
                : ["video/webm;codecs=vp8,opus", "video/mp4", "video/webm"];
            const mimeType = candidates.find((mime) => MediaRecorder.isTypeSupported(mime));
            const recorder = new MediaRecorder(stream, mimeType ? { mimeType } : undefined);
            this.recorder = recorder;
            const chunks: Blob[] = [];
            let size = 0;
            recorder.ondataavailable = (event) => {
                if (event.data.size) { chunks.push(event.data); size += event.data.size; }
                if (size >= 45 * 1024 * 1024 && recorder.state === "recording") this.stop();
            };
            recorder.onstop = () => {
                this.releaseStream();
                if (!this.alive) return;
                const blob = new Blob(chunks, { type: recorder.mimeType });
                const ext = recorder.mimeType.includes("mp4") ? "mp4" : recorder.mimeType.includes("ogg") ? "ogg" : "webm";
                this.setBlob(blob, `recording-${Date.now()}.${ext}`);
            };
            recorder.onerror = () => { this.error.set("Recording failed. Please retry or upload a file."); this.stop(); };
            this.seconds.set(0);
            this.recording.set(true);
            recorder.start(1000);
            this.interval = setInterval(() => {
                this.seconds.update((s) => s + 1);
                if (this.seconds() >= 120) this.stop();
            }, 1000);
        } catch (error: unknown) {
            this.error.set(errorMessage(error)); this.releaseStream();
        } finally { this.starting.set(false); }
    }

    stop() {
        if (this.recorder && this.recorder.state !== "inactive") this.recorder.stop();
        this.releaseStream();
    }

    upload(name: string) {
        if (!this.blob || this.busy()) return;
        if (!name.trim()) { this.error.set("Enter a media name."); return; }
        this.busy.set(true); this.error.set("");
        this.api.upload(this.blob, name.trim(), this.kind()).subscribe({
            next: (file) => { this.busy.set(false); this.selected.emit(file); },
            error: (error: unknown) => { this.busy.set(false); this.error.set(errorMessage(error)); },
        });
    }

    private setBlob(blob: Blob, name: string) {
        if (this.preview()) URL.revokeObjectURL(this.preview());
        this.blob = blob; this.filename.set(name); this.preview.set(URL.createObjectURL(blob));
    }
    private releaseStream() {
        this.stream()?.getTracks().forEach((track) => track.stop());
        this.stream.set(null); this.recording.set(false);
        if (this.interval) { clearInterval(this.interval); this.interval = null; }
    }
    ngOnDestroy() {
        this.alive = false; this.stop();
        if (this.preview()) URL.revokeObjectURL(this.preview());
    }
}
