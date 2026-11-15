import {
    ChangeDetectionStrategy,
    Component,
    effect,
    input,
    OnDestroy,
    signal,
} from "@angular/core";

@Component({
    selector: "studio-audio-button",
    changeDetection: ChangeDetectionStrategy.OnPush,
    template: `
        <button
            type="button"
            class="audio-button"
            [attr.aria-label]="(playing() ? 'Pause ' : 'Play ') + label()"
            (click)="toggle()"
        >
            {{ playing() ? "Pause" : "Play audio" }}
        </button>
        @if (error()) {
            <small role="alert" class="error">{{ error() }}</small>
        }
    `,
})
export class AudioButton implements OnDestroy {
    readonly url = input.required<string>();
    readonly label = input("audio");
    readonly playing = signal(false);
    readonly error = signal("");
    private audio?: HTMLAudioElement;
    constructor() {
        effect(() => {
            this.url();
            this.audio?.pause();
            this.playing.set(false);
            this.error.set("");
        });
    }
    async toggle() {
        this.error.set("");
        if (this.playing()) {
            this.audio?.pause();
            this.playing.set(false);
            return;
        }
        if (!this.audio || this.audio.src !== new URL(this.url(), location.href).href) {
            this.audio?.pause();
            this.audio = new Audio(this.url());
            this.audio.onended = () => this.playing.set(false);
            this.audio.onerror = () => {
                this.playing.set(false);
                this.error.set("Audio could not be played.");
            };
        }
        try {
            await this.audio.play();
            this.playing.set(true);
        } catch {
            this.error.set("Audio could not be played. Check the file and try again.");
            this.playing.set(false);
        }
    }
    ngOnDestroy() {
        this.audio?.pause();
        if (this.audio) this.audio.src = "";
    }
}
