import { Component, OnDestroy } from "@angular/core";
import { trigger, state, style, animate, transition, keyframes } from "@angular/animations";
import { Subscription } from "rxjs";

import { CookieService } from "app/_services/cookie.service";
import { ReviewService } from "app/_services/review.service";
import { Loader } from "app/_services/loader.service";
import { LessonsService } from "app/_services/lessons.service";
import { KeyboardService } from "app/shared/keyboard/keyboard.service";
import { AudioService } from "app/_services/audio.service";
import { LocalStorageService } from "app/_services/local-storage.service";
import { OwoksapeUtils } from "app/shared/utils/owoksape-utils";
import { LocalizeService } from "app/_services/localize.service";
import * as App from "app/_constants/app.constants";

@Component({
    selector: "app-reward-popups",
    templateUrl: "./reward-popups.component.html",
    styleUrls: ["./reward-popups.component.scss"],
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
export class RewardPopupsComponent implements OnDestroy {
    public popupSubscription: Subscription;
    public wrongAnswerSubscription: Subscription;
    private keyboardSubmitOrCloseSubscription: Subscription;
    private keyboardToggleSelectionSubscription: Subscription;
    private keyboardToggleMediaSubscription: Subscription;
    public showPopup: boolean = false;
    public popupType: string = "lesson";
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
    public fireData: any = {};
    public fireImage: string = "dead";
    public user: any = {};
    public translations: any = {};
    public keyboardUnitPopupHighlightIndex = -1;
    public isClassroom: boolean = false;
    public OwoksapeUtils = OwoksapeUtils;
    private closeCorrectAnswerPopupTimeoutId = undefined;

    constructor(
        private lessonService: LessonsService,
        private loader: Loader,
        private reviewService: ReviewService,
        private cookieService: CookieService,
        private keyboardService: KeyboardService,
        private localStorage: LocalStorageService,
        private localizeService: LocalizeService,
        public audioService: AudioService,
    ) {
        this.wrongAnswerSubscription = this.lessonService.wrongCard.subscribe((res) => {
            if (res) {
                this.reviewCards = res;
                if (this.reviewCards.length > 1) {
                    this.activeCard = Object.assign({}, this.reviewCards[1]);
                } else {
                    this.activeCard = Object.assign({}, this.reviewCards[0]);
                }
                this.activeIndex = 0;
            }
        });

        this.localizeService.getTranslations().subscribe((data) => {
            this.translations = data["components"]["reward-popups"];
        });

        this.cookieService
            .get("AuthUser")
            .then((value) => {
                if (value == "") {
                    throw value;
                }
                this.user = JSON.parse(value);
                this.popupSubscription = this.lessonService.popup.subscribe((res) => {
                    if (Object.keys(res).length > 0 && !res.popUpClosed) {
                        if (typeof res.popup_status != "undefined") {
                            if (res.popup_status) {
                                this.showPopup = true;
                            } else if (!res.popup_status) {
                                this.showPopup = false;
                                if (this.popupType == "exerciseSetPerfect" || this.popupType == "exerciseSet") {
                                    this.closePopupExSet();
                                } else {
                                    this.closePopup("exercise question");
                                }
                            }
                        } else {
                            this.showPopup = true;
                        }

                        this.popupType = res.type;
                        this.popupData = res.data;
                        this.handleData(res);
                    }

                    this.setKeyboardListeners(this.showPopup && !this.popupStatus);

                    if (res.popUpClosed) {
                        this.audioService.pauseAudio();
                    }

                    // Hide correct popup after a bit, if a timeout isn't already set
                    if (this.showPopup && this.popupStatus && !this.closeCorrectAnswerPopupTimeoutId) {
                        this.closeCorrectAnswerPopupTimeoutId = setTimeout(() => {
                            if (this.showPopup != false) {
                                this.closePopup("exercise question");
                            }
                        }, App.Settings.CORRECT_ANSWER_POPUP_TIME_BEFORE_DISAPPEARING_MS);
                    }
                });
            })
            .catch((err) => {
                console.warn("No AuthUser cookie", err);
            });
    }

    ngOnDestroy(): void {
        this.lessonService.setPopup({});
        this.popupSubscription.unsubscribe();
        this.wrongAnswerSubscription.unsubscribe();
        this.setKeyboardListeners(false);
    }

    setKeyboardListeners(turnOn: boolean) {
        if (turnOn) {
            if (!!this.keyboardSubmitOrCloseSubscription && !this.keyboardSubmitOrCloseSubscription.closed) {
                return;
            }
            this.keyboardUnitPopupHighlightIndex = -1;
            this.keyboardSubmitOrCloseSubscription = this.keyboardService.submitOrCloseEvent.subscribe(() => {
                switch (this.popupType) {
                    case "lesson":
                        if (this.showPopup) {
                            this.closePopup("lesson");
                        }
                    case "exercise":
                        if (this.showPopup) {
                            this.closePopup("exercise question");
                        }
                        break;
                    case "exerciseSetPerfect":
                    case "exerciseSet":
                        this.closePopupExSet();
                        break;
                    case "unit":
                        if (this.keyboardUnitPopupHighlightIndex == 1) {
                            this.closeUnitPopup("lessons-and-exercises");
                        } else {
                            if (this.isClassroom) {
                                this.closeUnitPopup("classroom");
                            } else {
                                this.closeUnitPopup("review");
                            }
                        }
                        break;
                    default:
                        if (this.showPopup) {
                            console.warn("Oops! Got unhandled popupType");
                        }
                        break;
                }
            });

            this.keyboardToggleSelectionSubscription = this.keyboardService.toggleSelectionEvent.subscribe((event) => {
                if (this.popupType != "unit" && this.reviewCards.length > 1) {
                    if (event.shiftKey) {
                        this.previous();
                    } else {
                        this.next();
                    }
                } else if (this.popupType == "unit") {
                    if (event.shiftKey) {
                        this.keyboardUnitPopupHighlightIndex = OwoksapeUtils.decrementWrap(
                            this.keyboardUnitPopupHighlightIndex,
                            0,
                            1,
                        );
                    } else {
                        this.keyboardUnitPopupHighlightIndex = OwoksapeUtils.incrementWrap(
                            this.keyboardUnitPopupHighlightIndex,
                            0,
                            1,
                        );
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

    private setLoader(val: boolean) {
        this.loader.setLoader(val);
    }

    private handleData(res: any) {
        switch (res.type) {
            case "lesson":
                this.setCards(res.data);
                this.lessonPoints = res.points;
                break;
            case "exercise":
                this.popupStatus = res.status;
                this.review = res.review;
                this.wrongAnswerHeader = this.getWrongAnswerHeader();
                break;
            case "exerciseSet":
                this.setCards(res.data.cardSet);
                break;
            case "unit":
                this.isClassroom = parseInt(this.localStorage.getItem("isClassroom")) === 1;
                this.getFireData();
                break;
            default:
                break;
        }

        this.getTotal(this.popupType);
    }

    /**
     * Generate correct answer header text based on number of total points.
     * The more points, the more emphatic the congratulations.
     * @param {number} score - Total points
     * @return {string} - Header text associated with number of points,
     * or empty string.
     */
    getCorrectAnswerHeader(score: number) {
        // Score cutoff points where congrats text changes
        const scoreArray = [-100, 4, 7, 9, 12, 14, 1000];

        let header = "";
        for (let i = 0; i < scoreArray.length; ++i) {
            if (score > scoreArray[i] && score <= scoreArray[i + 1]) {
                header = this.translations["congrats"]["congrats_" + (i + 1)] ?? "";
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

    private getFireData() {
        this.setLoader(true);
        this.reviewService
            .getFire({ user_id: this.user.id })
            .then((res) => {
                if (res.data.status) {
                    this.fireData = res.data.results;
                    if (this.fireData.haveReviewExercise) {
                        this.setFireImage();
                    }
                }
            })
            .catch((err) => {
                console.warn("Error getting fire data", err);
            })
            .finally(() => {
                this.setLoader(false);
            });
    }

    private setFireImage() {
        if (this.fireData.FireData.fire_days > 0 && this.fireData.FireData.fire_days < 3) {
            this.fireImage = "low";
        } else if (this.fireData.FireData.fire_days >= 3 && this.fireData.FireData.fire_days < 7) {
            this.fireImage = "medium";
        } else if (this.fireData.FireData.fire_days >= 7 && this.fireData.FireData.fire_days < 14) {
            this.fireImage = "high";
        } else if (this.fireData.FireData.fire_days >= 14) {
            this.fireImage = "ultra";
        }
    }

    getTotal(type) {
        switch (type) {
            case "lesson":
                if (this.lessonPoints) {
                    this.popupData.total =
                        parseFloat(this.lessonPoints.reading_score) +
                        parseFloat(this.lessonPoints.writing_score) +
                        parseFloat(this.lessonPoints.speaking_score) +
                        parseFloat(this.lessonPoints.listening_score) +
                        parseFloat(this.lessonPoints.path_score);
                }
                break;
            case "exercise":
                this.popupData.total =
                    parseFloat(this.popupData.reading_score) +
                    parseFloat(this.popupData.writing_score) +
                    parseFloat(this.popupData.speaking_score) +
                    parseFloat(this.popupData.listening_score) +
                    parseFloat(this.popupData.path_score);
                break;
            case "unit":
                this.popupData.total =
                    parseFloat(this.popupData.reading_score) +
                    parseFloat(this.popupData.writing_score) +
                    parseFloat(this.popupData.speaking_score) +
                    parseFloat(this.popupData.listening_score) +
                    parseFloat(this.popupData.social_score) +
                    parseFloat(this.popupData.path_score);
                break;
            case "exerciseSet":
            case "exerciseSetPerfect":
                this.exSetTotal();
                break;
            default:
                break;
        }
    }

    exSetTotal() {
        this.popupData.total =
            parseFloat(this.popupData.points.path_score_total) +
            parseFloat(this.popupData.points.reading_score_total) +
            parseFloat(this.popupData.points.speaking_score_total) +
            parseFloat(this.popupData.points.listening_score_total) +
            parseFloat(this.popupData.points.writing_score_total) +
            parseFloat(this.popupData.points.review_score_total) +
            parseFloat(this.popupData.points.social_score_total);
    }

    setCards(data: any) {
        this.reviewCards = [];
        switch (this.popupType) {
            case "lesson":
                data.lesson.lessonframes.forEach((frame: any) => {
                    for (let i = 0; i < frame.lesson_frame_blocks.length; i++) {
                        const block = frame.lesson_frame_blocks[i];
                        if (block.type == "card" && block.CardDetails.include_review == "1") {
                            this.reviewCards.push(block.CardDetails);
                        }
                    }
                });
                break;
            case "exercise":
                this.reviewCards = data;
                break;
            case "exerciseSet":
                data.forEach((element) => {
                    if (element.PromptType != "html" && element.ResponseType != "html") {
                        this.reviewCards.push(element);
                    }
                });
                break;
            default:
                break;
        }
        if (this.reviewCards.length > 1) {
            this.nextDisable = false;
        }
        setTimeout(() => {
            this.activeCard = Object.assign({}, this.reviewCards[0]);
            this.activeIndex = 0;
        }, 100);
    }

    next() {
        if (this.activeIndex != this.reviewCards.length - 1) {
            this.audioService.pauseAudio();
        }
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
        if (this.activeIndex != 0) {
            this.audioService.pauseAudio();
        }
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
        this.setKeyboardListeners(false);
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
        this.lessonPoints = {};
        this.wrongAnswerHeader = "";
    }

    /**
     * Cancel the timeout that closes the correct answer popup. This is necessary
     * because if the user closes the popup manually by clicking outside the
     * popup area, we don't want the timeout to close the next popup, which usual
     * causes a flash and mulitple of the same popup to appear, due to triggering
     * multiple of the same event in the unit state machine.
     */
    cancelClosePopupTimeout() {
        if (this.closeCorrectAnswerPopupTimeoutId) {
            clearTimeout(this.closeCorrectAnswerPopupTimeoutId);
            this.closeCorrectAnswerPopupTimeoutId = undefined;
        }
    }

    closePopup(type: string) {
        if (!type) {
            throw new Error("closePopup: type is required");
        }
        this.cancelClosePopupTimeout();
        this.clearAll();
        this.lessonService.setPopup({ popUpClosed: true, type });
    }

    closePopupExSet() {
        // this.lessonService.setPopup({ popUpClosed: true, exSet: true, containsWrong: (this.popupType == 'exerciseSet') });
        this.cancelClosePopupTimeout();
        this.lessonService.setPopup({ popUpClosed: true, type: "exercise set", exSet: true, containsWrong: false });
        this.clearAll();
    }

    closeUnitPopup(route: string) {
        this.cancelClosePopupTimeout();
        this.reviewService.setReviewProgress({});
        this.clearAll();
        this.lessonService.setPopup({ popUpClosed: true, type: "unit", unitComplete: true, route });
    }
}
