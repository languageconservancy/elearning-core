import { CommonModule } from "@angular/common";
import {
    Component,
    Input,
    Output,
    EventEmitter,
    ElementRef,
    ViewChild,
    ViewEncapsulation,
    OnInit,
    OnChanges,
} from "@angular/core";
import { PipesModule } from "app/_pipes/pipes.module";
import { AudioService } from "app/_services/audio.service";
import { DeviceDetectorService } from "ngx-device-detector";
import { AnswerType, CardDataType } from "app/shared/utils/elearning-types";

type Media = {
    FullUrl: string;
};

type Card = {
    id: number;
    lakota: string;
    english: string;
    audio: Media;
    image: Media;
    FullAudioUrl: string;
    video: Media;
    ResponseType: string;
    PromptType: string;
    response_html: string;
    prompt_html: string;
};

@Component({
    standalone: true,
    selector: "app-selectable-card",
    imports: [CommonModule, PipesModule],
    templateUrl: "./selectable-card.component.html",
    styleUrls: ["./selectable-card.component.scss"],
    encapsulation: ViewEncapsulation.None,
})
export class SelectableCardComponent implements OnInit, OnChanges {
    @Input() card: Card;
    @Input() index: number;
    @Input() highlightedCardIndex: number;
    @Input() selectedCardId: number;
    @Input() isSelected: boolean;
    @Input() isDisabled: boolean;
    @Input() correctAnswerId: number;
    @Input() userAnswer: AnswerType;
    @Input() enabledDataTypes: string[];
    @Input() optionType: string; // prompt|response
    @Input() matched: any = {
        questions: [],
        answers: [],
    };

    @Output() cardSelectedEvent = new EventEmitter<Card>();

    @ViewChild("video") video: ElementRef;

    isHovered: boolean = false; // Whether mouse is hovering over card
    isVideoHovered: boolean = false; // Whether mouse is hovering over video
    showSelectButton: boolean = false; // Whether this card should show a select button

    // Make enums accessible to template
    DataType = CardDataType;
    AnswerType = AnswerType;

    // Check if device is desktop or not for responsive design
    isDesktop: boolean = !this.deviceService.isMobile() && !this.deviceService.isTablet();

    constructor(
        private audioService: AudioService,
        private deviceService: DeviceDetectorService,
    ) {}

    ngOnInit() {
        this.updateSelectButtonVisibility();
    }

    ngOnChanges() {
        this.updateSelectButtonVisibility();
    }

    private updateSelectButtonVisibility() {
        this.showSelectButton = this.hasSelectButton();
    }

    /**
     * Play/Pause audio
     * @param audioUrl - audio url to play/pause
     */
    playPauseAudio() {
        let audioUrl = "";
        if (this.card.FullAudioUrl) {
            audioUrl = this.card.FullAudioUrl;
        } else if (this.card.audio && this.card.audio.FullUrl) {
            audioUrl = this.card.audio.FullUrl;
        }
        this.audioService.playPauseAudio(audioUrl, this.optionType);
    }

    audioIsPlaying() {
        return (
            this.audioService.audioType === this.optionType &&
            (this.audioService.getAudioSrc() === this.card.FullAudioUrl ||
                this.audioService.getAudioSrc() === this.card.audio.FullUrl) &&
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

    /**
     * On card click, if the card doesn't have a select button, emit the card selected event.
     */
    onCardClick() {
        if (this.isDisabled) {
            return;
        }

        this.cardSelectedEvent.emit(this.card);
    }

    /**
     * Check if the card has a select button.
     * A video card with no english text (which sits below the video) has a select button.
     * A audio card with only audio doesn't have card area, so it has a select button.
     * @returns true if the card has a select button, false otherwise.
     */
    hasSelectButton(): boolean {
        const hasVideo = this.isActive(this.DataType.Video);
        const hasEnglish = this.isActive(this.DataType.English);
        const hasAudio = this.isActive(this.DataType.Audio);
        const enabledTypesLength = this.enabledDataTypes.length;

        const result = (hasVideo && !hasEnglish) || (hasAudio && enabledTypesLength === 1);

        return result;
    }

    videoClicked(event: Event): void {
        event.stopPropagation();
    }

    onMouseEnterCard() {
        this.isHovered = true;
    }

    onMouseLeaveCard() {
        this.isHovered = false;
    }

    onMouseEnterVideo() {
        this.isVideoHovered = true;
    }

    onMouseLeaveVideo() {
        this.isVideoHovered = false;
    }
}
