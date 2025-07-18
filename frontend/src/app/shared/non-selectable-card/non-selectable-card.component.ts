import { CommonModule } from "@angular/common";
import { Component, Input } from "@angular/core";
import { PipesModule } from "app/_pipes/pipes.module";
import { CardDataType } from "app/shared/utils/elearning-types";
import { AudioService } from "app/_services/audio.service";
import { PartialsModule } from "app/_partials/partials.module";

type Video = {
    FullUrl: string;
};

type Image = {
    FullUrl: string;
};

type Card = {
    id: number;
    lakota: string;
    english: string;
    image: Image;
    FullAudioUrl: string;
    video: Video;
    ResponseType: string;
    PromptType: string;
    response_html: string;
    prompt_html: string;
};

@Component({
    standalone: true,
    selector: "app-non-selectable-card",
    imports: [CommonModule, PipesModule, PartialsModule],
    templateUrl: "./non-selectable-card.component.html",
    styleUrls: ["./non-selectable-card.component.scss"],
})
export class NonSelectableCardComponent {
    @Input() card: Card;
    @Input() enabledDataTypes: string[];
    @Input() optionType: string; // prompt|response

    // Make enums accessible to template
    DataType = CardDataType;

    constructor(public audioService: AudioService) {}

    /**
     * Play/Pause audio
     * @param audioUrl - audio url to play/pause
     */
    playPauseAudio() {
        this.audioService.playPauseAudio(this.card.FullAudioUrl, this.optionType);
    }

    audioIsPlaying() {
        return (
            this.audioService.audioType === this.optionType &&
            this.audioService.getAudioSrc() === this.card.FullAudioUrl &&
            !this.audioService.audioIsPaused()
        );
    }

    isActive(dataType: CardDataType): boolean {
        switch (dataType) {
            case CardDataType.Language:
                return this.enabledDataTypes.indexOf("l") > -1;
            case CardDataType.Audio:
                return this.enabledDataTypes.indexOf("a") > -1 && this.card.FullAudioUrl !== "";
            case CardDataType.Image:
                return this.enabledDataTypes.indexOf("i") > -1 && this.card.image?.FullUrl !== "";
            case CardDataType.Video:
                return this.enabledDataTypes.indexOf("v") > -1 && this.card.video?.FullUrl !== "";
            case CardDataType.English:
                return this.enabledDataTypes.indexOf("e") > -1;
            default:
                console.error("Invalid data type", dataType);
                return false;
        }
    }

    getAudioIconUrl() {
        if (
            (this.audioService.getAudioSrc() != this.card.FullAudioUrl || this.audioService.audioIsPaused()) &&
            (this.enabledDataTypes.length == 1 || !this.isActive(this.DataType.Image))
        ) {
            return "./assets/images/audio-large-mute.png";
        } else if (
            this.audioService.getAudioSrc() == this.card.FullAudioUrl &&
            !this.audioService.audioIsPaused() &&
            this.audioService.audioType === "prompt" &&
            (this.enabledDataTypes.length == 1 || !this.isActive(this.DataType.Image))
        ) {
            return "./assets/images/audio-large.png";
        } else if (
            (this.audioService.getAudioSrc() != this.card.FullAudioUrl || this.audioService.audioIsPaused()) &&
            this.enabledDataTypes.length > 1 &&
            this.isActive(this.DataType.Image)
        ) {
            return "./assets/images/sound-mute-blue-btn.png";
        } else if (
            this.audioService.getAudioSrc() == this.card.FullAudioUrl &&
            !this.audioService.audioIsPaused() &&
            this.audioService.audioType === "prompt" &&
            this.enabledDataTypes.length > 1 &&
            this.isActive(this.DataType.Image)
        ) {
            return "./assets/images/sound-blue-btn.png";
        } else {
            return "./assets/images/sound-mute-blue-btn.png";
        }
    }
}
