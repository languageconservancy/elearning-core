import { Component, OnDestroy } from "@angular/core";
import { Subscription } from "rxjs";
import { trigger, state, style, animate, transition, keyframes } from "@angular/animations";

import { Loader } from "app/_services/loader.service";
import { ReviewService } from "app/_services/review.service";
import { KeyboardService } from "app/shared/keyboard/keyboard.service";
import { OwoksapeUtils } from "app/shared/utils/owoksape-utils";
import { AudioService } from "app/_services/audio.service";
import * as App from "app/_constants/app.constants";
import { LocalizeService } from "app/_services/localize.service";

@Component({
    selector: "app-review-reward-popup",
    templateUrl: "./review-reward-popup.component.html",
    styleUrls: ["./review-reward-popup.component.scss"],
    animations: [
        // the fade-in/fade-out animation
        trigger("fadeScaleBounceAnimation", [
            // static state after enter
            state("initial", style({ opacity: 1, transform: "translateY(0)" })),
            // Start small and grow to normal size
            transition(":enter", [
                animate(
                    App.Animation.EXERCISE_POPUP_FADE_IN_TIME_MS,
                    keyframes([
                        style({ transform: "scale(0)", offset: 0.0 }),
                        style({ transform: "scale(1.2)", offset: 0.1 }),
                        style({ transform: "scale(.9)", offset: 0.2 }),
                        style({ transform: "scale(1.05)", offset: 0.35 }),
                        style({ transform: "scale(1.0)", offset: 0.4 }),
                        style({ transform: "scale(1.0)", offset: 1.0 }),
                    ]),
                ),
            ]),

            transition(":enter", [style({ opacity: 0 })]),

            transition(":leave", [
                animate(App.Animation.EXERCISE_POPUP_FADE_OUT_TIME_MS, style({ opacity: 0, transform: "scale(0)" })),
            ]),
        ]),
    ],
})
export class ReviewRewardPopupComponent implements OnDestroy {
    public popupSubscription: Subscription;
    public wrongAnswerSubscription: Subscription;
    private keyboardSubmitOrCloseSubscription: Subscription;
    private keyboardToggleSelectionSubscription: Subscription;
    private keyboardToggleMediaSubscription: Subscription;
    public showPopup: boolean = false;
    public popupType: string = "";
    public popupData: any = {};
    public reviewCards: any = [];
    public activeCard: any = {};
    public activeIndex: number = 0;
    public nextDisable: boolean = true;
    public previousDisable: boolean = true;
    public popupStatus: boolean = false;
    public review: boolean = false;
    public wrongCard: any = {};
    public wrongAnswerHeader: string = "";
    public lessonPoints: any = {};
    public translations: any = {};
    public OwoksapeUtils = OwoksapeUtils;

    constructor(
        private reviewService: ReviewService,
        private keyboardService: KeyboardService,
        public audioService: AudioService,
        private loader: Loader,
        private localizeService: LocalizeService,
    ) {
        this.wrongAnswerSubscription = this.reviewService.wrongCard.subscribe((res) => {
            this.reviewCards = res;
            this.activeCard = Object.assign({}, this.reviewCards[0]);
            this.activeIndex = 0;
        });

        this.localizeService.getTranslations().subscribe((data) => {
            this.translations = data["components"]["reward-popups"];
        });

        this.popupSubscription = this.reviewService.popup.subscribe((res) => {
            if (Object.keys(res).length > 0 && !res.popUpClosed) {
                this.showPopup = true;
                this.popupType = res.type;
                this.popupData = res.data;
                this.popupStatus = res.status;
                this.review = res.review;
                this.wrongAnswerHeader = this.getWrongAnswerHeader();
                this.getTotal();
            }

            this.setKeyboardListeners(this.showPopup && !this.popupStatus);

            // Hide correct popup after a bit
            if (this.showPopup && this.popupStatus) {
                setTimeout(() => {
                    if (this.showPopup != false) {
                        this.closePopup();
                    }
                }, App.Settings.CORRECT_ANSWER_POPUP_TIME_BEFORE_DISAPPEARING_MS);
            }

            if (res.popUpClosed) {
                this.audioService.pauseAudio();
            }
        });
    }

    ngOnDestroy(): void {
        this.popupSubscription.unsubscribe();
        this.wrongAnswerSubscription.unsubscribe();
        this.setKeyboardListeners(false);
    }

    setKeyboardListeners(turnOn: boolean) {
        if (turnOn) {
            if (!!this.keyboardSubmitOrCloseSubscription && !this.keyboardSubmitOrCloseSubscription.closed) {
                return;
            }

            this.keyboardSubmitOrCloseSubscription = this.keyboardService.submitOrCloseEvent.subscribe(() => {
                switch (this.popupType) {
                    case "exercise":
                        if (this.showPopup) {
                            this.closePopup();
                        }
                        break;
                    case "reviewScore":
                        this.closePopupReview();
                        break;
                    default:
                        console.warn("Oops! Unhandled popupType.");
                        break;
                }
            });

            this.keyboardToggleSelectionSubscription = this.keyboardService.toggleSelectionEvent.subscribe((event) => {
                if (this.popupType == "exercise" && this.reviewCards.length > 1) {
                    if (event.shiftKey) {
                        this.previous();
                    } else {
                        this.next();
                    }
                }
            });

            this.keyboardToggleMediaSubscription = this.keyboardService.toggleMediaEvent.subscribe(() => {
                if (!!this.activeCard && !!this.activeCard.FullAudioUrl) {
                    this.audioService.playPauseAudio(this.activeCard.FullAudioUrl);
                }
            });
        } else {
            if (!!this.keyboardSubmitOrCloseSubscription) {
                this.keyboardSubmitOrCloseSubscription.unsubscribe();
            }
            if (!!this.keyboardToggleSelectionSubscription) {
                this.keyboardToggleSelectionSubscription.unsubscribe();
            }
            if (!!this.keyboardToggleMediaSubscription) {
                this.keyboardToggleMediaSubscription.unsubscribe();
            }
        }
    }

    getTotal() {
        switch (this.popupType) {
            case "exercise":
                this.popupData.total =
                    parseFloat(this.popupData.reading_score) +
                    parseFloat(this.popupData.writing_score) +
                    parseFloat(this.popupData.speaking_score) +
                    parseFloat(this.popupData.listening_score) +
                    parseFloat(this.popupData.path_score);
                break;
            case "reviewScore":
                this.popupData.total =
                    parseFloat(this.popupData.path_score_total) +
                    parseFloat(this.popupData.reading_score_total) +
                    parseFloat(this.popupData.speaking_score_total) +
                    parseFloat(this.popupData.listening_score_total) +
                    parseFloat(this.popupData.writing_score_total) +
                    parseFloat(this.popupData.review_score_total) +
                    parseFloat(this.popupData.social_score_total);
                break;
            default:
                break;
        }
    }

    getCorrectAnswerHeader(score: number) {
        const scoreArray = [-1, 4, 7, 9, 12, 14, 100];

        let header = null;
        for (let i = 0; i < scoreArray.length; ++i) {
            if (score > scoreArray[i] && score <= scoreArray[i + 1]) {
                header = this.translations["congrats"]["congrats_" + (i + 1)];
                break;
            }
        }
        return header;
    }

    getWrongAnswerHeader() {
        if (!!this.translations) {
            const wrongAnswerHeaders = this.translations["wrongAnswerPopups"];
            return wrongAnswerHeaders[Math.floor(Math.random() * wrongAnswerHeaders.length)];
        } else {
            return "";
        }
    }

    next() {
        this.audioService.pauseAudio();
        this.activeIndex = Math.min(this.activeIndex + 1, this.reviewCards.length - 1);
        if (this.reviewCards[this.activeIndex]) {
            this.activeCard = Object.assign({}, this.reviewCards[this.activeIndex]);
        }
        if (this.activeIndex == this.reviewCards.length - 1) {
            this.nextDisable = true;
        } else {
            this.nextDisable = false;
        }

        if (this.activeIndex > 0) {
            this.previousDisable = false;
        }
    }

    previous() {
        this.audioService.pauseAudio();
        this.activeIndex = Math.max(this.activeIndex - 1, 0);
        if (this.reviewCards[this.activeIndex]) {
            this.activeCard = Object.assign({}, this.reviewCards[this.activeIndex]);
        }
        if (this.activeIndex == 0) {
            this.previousDisable = true;
        } else {
            this.previousDisable = false;
        }

        if (this.activeIndex < this.reviewCards.length - 1) {
            this.nextDisable = false;
        }
    }

    clearAll() {
        this.showPopup = false;
        this.popupType = "";
        this.popupData = {};
        this.reviewCards = [];
        this.activeCard = {};
        this.activeIndex = 0;
        this.nextDisable = true;
        this.previousDisable = true;
        this.popupStatus = false;
        this.wrongCard = {};
        this.wrongAnswerHeader = "";
        this.lessonPoints = {};
        this.setKeyboardListeners(false);
    }

    closePopup() {
        this.clearAll();
        this.reviewService.setPopup({ popUpClosed: true });
    }

    closePopupReview() {
        this.clearAll();
    }
}
