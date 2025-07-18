import { Component, OnDestroy } from "@angular/core";
import { CookieService } from "app/_services/cookie.service";
import { Router } from "@angular/router";
import { Subscription } from "rxjs";

import { Loader } from "app/_services/loader.service";
import { LessonsService } from "app/_services/lessons.service";
import { AudioService } from "app/_services/audio.service";
import { KeyboardService } from "app/shared/keyboard/keyboard.service";
import { OwoksapeUtils } from "app/shared/utils/owoksape-utils";

declare let jQuery: any;

@Component({
    selector: "app-lessons",
    templateUrl: "./lessons.component.html",
    styleUrls: ["./lessons.component.scss"],
})
export class LessonsComponent implements OnDestroy {
    public frame: any = {};
    public count: number = 0;

    public frameSubscription: Subscription;
    private popupSubscription: Subscription;
    private keyboardToggleMediaSubscription: Subscription;
    private keyboardToggleSelectionSubscription: Subscription;
    public keyboardHighlightedBlockIndex: number = 0;

    constructor(
        private cookieService: CookieService,
        private lessonService: LessonsService,
        private keyboardService: KeyboardService,
        public audioService: AudioService,
        private router: Router,
        private loader: Loader,
    ) {
        this.frameSubscription = this.lessonService.currentFrame.subscribe((frame) => {
            if (frame && Object.keys(frame).length) {
                this.frame = frame;
                this.setKeyboardListeners(true);
                this.keyboardHighlightedBlockIndex = -1;
                this.autoPlayAudio();
            }
        });

        this.popupSubscription = this.lessonService.popup.subscribe((popup) => {
            if (!!popup.popUpClosed && popup.popUpClosed) {
                this.setKeyboardListeners(true);
            } else if (!!popup.type) {
                this.setKeyboardListeners(false);
            }
        });
    }

    private setLoader(val: boolean) {
        this.loader.setLoader(val);
    }

    public compileString(input: string) {
        return input;
    }

    public setBlockSizes() {
        if (this.frame.number_of_block != 2) {
            return;
        }

        this.count = 0;
        for (let i = 0; i < this.frame.lesson_frame_blocks.length; i++) {
            const block = this.frame.lesson_frame_blocks[i];
            if (block.is_card_image == "Y" || block.type == "image") {
                this.count++;
            }
        }

        setTimeout(() => {
            if (this.count < 2) {
                this.setSize();
            }
        }, 100);
    }

    private setSize() {
        const firstBox = jQuery("#box-1").outerHeight();
        const secondBox = jQuery("#box-2").outerHeight();

        if (firstBox >= secondBox) {
            jQuery("#box-2").css({
                height: firstBox,
            });
        } else {
            jQuery("#box-1").css({
                height: secondBox,
            });
        }
    }

    private autoPlayAudio() {
        this.audioService.clearPlaylist();
        if (this.frame.FrameAudioUrl) {
            this.audioService.addToPlaylist(this.frame.FrameAudioUrl, "frame");
        }

        // Print number of blocks
        this.frame.lesson_frame_blocks.forEach((element, index) => {
            switch (element.type) {
                case "card":
                    if (element.is_card_audio == "Y") {
                        this.audioService.addToPlaylist(element.CardDetails.FullAudioUrl, `block-${index}`);
                    }
                    break;
                case "audio":
                    if (element.AudioUrl) {
                        this.audioService.addToPlaylist(element.AudioUrl, `block-${index}`);
                    }
                    break;
                default:
                    break;
            }
        });
        setTimeout(() => {
            if (this.audioService.getPlaylistLength() > 0) {
                this.audioService.setAutoPlay(true);
                this.audioService.autoPlayAudio(this.audioService.getPlaylistTrack(0), 0);
            }
        }, 100);
    }

    ngOnDestroy() {
        this.frameSubscription.unsubscribe();
        if (!!this.popupSubscription) this.popupSubscription.unsubscribe();
        this.lessonService.setLessonFrame({});
        this.audioService.pauseAndClearAudioSrc();
        this.setKeyboardListeners(false);
    }

    setKeyboardListeners(turnOn: boolean) {
        if (turnOn) {
            // Only subscribe if not already subscribed
            if (!!this.keyboardToggleSelectionSubscription && !this.keyboardToggleSelectionSubscription.closed) {
                return;
            }
            // Toggle highlighted lesson frame block
            this.keyboardToggleSelectionSubscription = this.keyboardService.toggleSelectionEvent.subscribe((event) => {
                if (event.shiftKey) {
                    this.keyboardHighlightedBlockIndex = OwoksapeUtils.decrementWrap(
                        this.keyboardHighlightedBlockIndex,
                        0,
                        this.frame.number_of_block - 1,
                    );
                } else {
                    this.keyboardHighlightedBlockIndex = OwoksapeUtils.incrementWrap(
                        this.keyboardHighlightedBlockIndex,
                        0,
                        this.frame.number_of_block - 1,
                    );
                }
            });
            // Play/pause audio
            this.keyboardToggleMediaSubscription = this.keyboardService.toggleMediaEvent.subscribe(() => {
                if (
                    this.keyboardHighlightedBlockIndex > -1 &&
                    this.keyboardHighlightedBlockIndex < this.frame.number_of_block
                ) {
                    this.checkAudio(this.frame.lesson_frame_blocks[this.keyboardHighlightedBlockIndex]);
                } else {
                    this.autoPlayAudio();
                }
            });
        } else {
            if (!!this.keyboardToggleSelectionSubscription) this.keyboardToggleSelectionSubscription.unsubscribe();
            if (!!this.keyboardToggleMediaSubscription) this.keyboardToggleMediaSubscription.unsubscribe();
        }
    }

    checkAudio(block, audioType = "") {
        if (block.type == "audio") {
            this.audioService.playPauseAudio(block.AudioUrl, audioType);
        } else if (block.is_card_audio == "Y" && block.CardDetails.FullAudioUrl) {
            this.audioService.playPauseAudio(block.CardDetails.FullAudioUrl, audioType);
        }
    }

    playAudio(url) {
        this.audioService.playPauseAudio(url);
    }

    imageError(event, blockNo) {
        const img = event.target as HTMLImageElement;
        img.onerror = null;
        img.src = this.frame.lesson_frame_blocks[blockNo].CardDetails.image.FullUrl || "";
    }
}
