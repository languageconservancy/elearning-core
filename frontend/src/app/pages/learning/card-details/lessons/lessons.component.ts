import { Component, inject, OnDestroy } from "@angular/core";
import { Router } from "@angular/router";
import { Subscription } from "rxjs";

import { AudioService } from "@app/_services/audio.service";
import { CookieService } from "@app/_services/cookie.service";
import { KeyboardService } from "@app/shared/keyboard/keyboard.service";
import { LessonsService } from "@app/_services/lessons.service";
import { Loader } from "@app/_services/loader.service";
import { OwoksapeUtils } from "@app/shared/utils/owoksape-utils";

@Component({
    selector: "app-lessons",
    templateUrl: "./lessons.component.html",
    styleUrls: ["./lessons.component.scss"],
    standalone: true,
})
export class LessonsComponent implements OnDestroy {
    private cookieService = inject(CookieService);
    private lessonService = inject(LessonsService);
    private keyboardService = inject(KeyboardService);
    public audioService = inject(AudioService);
    private router = inject(Router);
    private loader = inject(Loader);

    public frame: any = {};
    public count: number = 0;

    public frameSubscription: Subscription;
    private popupSubscription: Subscription;
    private keyboardToggleMediaSubscription: Subscription;
    private keyboardToggleSelectionSubscription: Subscription;
    public keyboardHighlightedBlockIndex: number = 0;

    constructor() {
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
        const firstBox = document.getElementById("box-1");
        const secondBox = document.getElementById("box-2");

        if (!firstBox || !secondBox) {
            return;
        }

        const firstHeight = firstBox.offsetHeight;
        const secondHeight = secondBox.offsetHeight;

        if (firstHeight >= secondHeight) {
            secondBox.style.height = `${firstHeight}px`;
        } else {
            firstBox.style.height = `${secondHeight}px`;
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
                        this.audioService.addToPlaylist(
                            element.CardDetails.FullAudioUrl,
                            `block-${index}`,
                        );
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
            if (
                !!this.keyboardToggleSelectionSubscription &&
                !this.keyboardToggleSelectionSubscription.closed
            ) {
                return;
            }
            // Toggle highlighted lesson frame block
            this.keyboardToggleSelectionSubscription =
                this.keyboardService.toggleSelectionEvent.subscribe((event) => {
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
            this.keyboardToggleMediaSubscription = this.keyboardService.toggleMediaEvent.subscribe(
                () => {
                    if (
                        this.keyboardHighlightedBlockIndex > -1 &&
                        this.keyboardHighlightedBlockIndex < this.frame.number_of_block
                    ) {
                        this.checkAudio(
                            this.frame.lesson_frame_blocks[this.keyboardHighlightedBlockIndex],
                        );
                    } else {
                        this.autoPlayAudio();
                    }
                },
            );
        } else {
            if (!!this.keyboardToggleSelectionSubscription)
                this.keyboardToggleSelectionSubscription.unsubscribe();
            if (!!this.keyboardToggleMediaSubscription)
                this.keyboardToggleMediaSubscription.unsubscribe();
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
