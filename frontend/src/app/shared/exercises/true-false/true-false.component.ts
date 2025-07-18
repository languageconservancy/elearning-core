import { Component, OnInit, OnDestroy, Input } from "@angular/core";
import { CookieService } from "app/_services/cookie.service";
import { Subscription } from "rxjs";
import { DeviceDetectorService } from "ngx-device-detector";

import { LessonsService } from "app/_services/lessons.service";
import { ReviewService } from "app/_services/review.service";
import { ExerciseService } from "app/_services/exercise.service";
import { AnswerType } from "app/shared/utils/elearning-types";
import { LocalStorageService } from "app/_services/local-storage.service";
import { KeyboardService } from "app/shared/keyboard/keyboard.service";
import { OwoksapeUtils } from "app/shared/utils/owoksape-utils";
import { AudioService } from "app/_services/audio.service";
import * as App from "app/_constants/app.constants";

@Component({
    selector: "app-true-false",
    templateUrl: "./true-false.component.html",
    styleUrls: ["./true-false.component.scss"],
    providers: [ExerciseService],
})
export class TrueFalseComponent implements OnInit, OnDestroy {
    public type: string = "";
    public customCardId: number = null;

    public exerciseSubscription: Subscription;
    public questionSubscription: Subscription;
    public popupSubscription: Subscription;
    private keyboardSubmitSelectionSubscription: Subscription;
    private keyboardToggleSelectionSubscription: Subscription;
    private keyboardToggleMediaSubscription: Subscription;
    public keyboardHighlightedAnswerIndex: number = -1;
    public keyboardHighlightedAnswer = "";
    readonly answerTypes = ["Y", "N"];

    public mychoice: any;
    public isMobile: boolean = false;
    public isTablet: boolean = false;

    @Input() sessionType: string;
    public AnswerType = AnswerType;
    private specifiedService;

    constructor(
        private cookieService: CookieService,
        private lessonService: LessonsService,
        private reviewService: ReviewService,
        public exerciseService: ExerciseService,
        public audioService: AudioService,
        private localStorage: LocalStorageService,
        private keyboardService: KeyboardService,
        private deviceDetector: DeviceDetectorService,
    ) {
        this.getDeviceInfo();

        this.cookieService
            .get("AuthUser")
            .then((value) => {
                if (value == "") {
                    throw value;
                }
                this.exerciseService.user = JSON.parse(value);
            })
            .catch((err) => {
                console.warn("No AuthUser cookie", err);
            });
    }

    ngOnInit() {
        this.setService();

        // Exercise subscription
        this.exerciseSubscription = this.specifiedService.currentExercise.subscribe((exercise) => {
            this.exerciseService.exercise = {};
            this.exerciseService.question = {};
            this.exerciseService.firstTime = true;
            this.audioService.pauseAudio();
            if (Object.keys(exercise).length > 0 && exercise.exercise_type == "truefalse") {
                this.exerciseService.userAnswer = this.AnswerType.NONE;
                this.exerciseService.exercise = exercise;
                this.exerciseService.nextButtonShouldBeClickable = false;
                this.exerciseService.exerciseCompleted = false;
                this.setKeyboardListeners(true);

                // Review only
                if (this.sessionType == "review") {
                    this.exerciseService.question = exercise.question;
                    this.exerciseService.response = exercise.response;
                    this.exerciseService.setPromptResponseTypes();
                    // this.exerciseService.promptTypes = exercise.question.exerciseOptions.prompt_preview_option
                    //     ? exercise.question.exerciseOptions.prompt_preview_option.split(",").map((el) => el.trim())
                    //     : [];
                    // this.exerciseService.responseTypes = exercise.question.exerciseOptions.responce_preview_option
                    //     ? exercise.question.exerciseOptions.responce_preview_option.split(",").map((el) => el.trim())
                    //     : [];

                    setTimeout(() => {
                        if (this.exerciseService.promptTypes.indexOf("a") > -1) {
                            this.audioService.playPauseAudio(this.exerciseService.question.FullAudioUrl, "prompt");
                        }
                    }, 100);
                }
                // END review only
            }
        });

        // Question subscription
        if (this.sessionType == "exercise") {
            this.questionSubscription = this.lessonService.currentQuestion.subscribe((ques) => {
                this.exerciseService.question = {};
                this.exerciseService.firstTime = true;
                this.audioService.pauseAudio();
                if (this.exerciseService.exercise.exercise_type == "truefalse" && Object.keys(ques).length > 0) {
                    this.exerciseService.question = ques.question;
                    this.exerciseService.response = ques.response;
                    this.exerciseService.userAnswer = this.AnswerType.NONE;
                    this.exerciseService.getCardIdList();
                    // this.setPromptResponseTypes();
                    this.exerciseService.setPromptResponseTypes();
                    setTimeout(() => {
                        if (this.exerciseService.promptTypes.indexOf("a") > -1) {
                            this.audioService.playPauseAudio(this.exerciseService.question.FullAudioUrl, "prompt");
                        }
                    }, 100);
                }
            });
        }

        // Popup subscription
        this.popupSubscription = this.specifiedService.popup.subscribe((res) => {
            if (res.popUpClosed) {
                this.audioService.pauseAudio();
            }
        });
    }

    ngOnDestroy() {
        if (!!this.exerciseSubscription) this.exerciseSubscription.unsubscribe();
        if (!!this.questionSubscription) this.questionSubscription.unsubscribe();
        this.setKeyboardListeners(false);
        this.popupSubscription.unsubscribe();
    }

    private getDeviceInfo() {
        this.isMobile = this.deviceDetector.isMobile();
        this.isTablet = this.deviceDetector.isTablet();
    }

    setService() {
        if (this.sessionType == "exercise") {
            this.specifiedService = this.lessonService;
        } else if (this.sessionType == "review") {
            this.specifiedService = this.reviewService;
        }
    }

    setKeyboardListeners(turnOn: boolean) {
        // If on mobile or tablet, don't set keyboard listeners
        if (this.isMobile || this.isTablet) {
            return;
        }
        if (turnOn && !this.exerciseService.exerciseCompleted) {
            // Toggle selection subscriber
            this.keyboardToggleSelectionSubscription = this.keyboardService.toggleSelectionEvent.subscribe((event) => {
                if (event.shiftKey) {
                    this.keyboardHighlightedAnswerIndex = OwoksapeUtils.decrementWrap(
                        this.keyboardHighlightedAnswerIndex,
                        -1,
                        this.answerTypes.length - 1,
                    );
                } else {
                    this.keyboardHighlightedAnswerIndex = OwoksapeUtils.incrementWrap(
                        this.keyboardHighlightedAnswerIndex,
                        -1,
                        this.answerTypes.length - 1,
                    );
                }
                this.keyboardHighlightedAnswer = this.answerTypes[this.keyboardHighlightedAnswerIndex];
            });
            // Submit selection subscriber
            this.keyboardSubmitSelectionSubscription = this.keyboardService.submitOrCloseEvent.subscribe(() => {
                if (this.keyboardHighlightedAnswerIndex < 0) {
                    return;
                }
                this.checkAnswer(this.answerTypes[this.keyboardHighlightedAnswerIndex]);
            });
            // Audio playback subscriber
            this.keyboardToggleMediaSubscription = this.keyboardService.toggleMediaEvent.subscribe(() => {
                if (this.exerciseService.promptTypes.indexOf("a") > -1) {
                    this.audioService.playPauseAudio(this.exerciseService.question.FullAudioUrl, "prompt");
                } else if (this.exerciseService.responseTypes.indexOf("a") > -1) {
                    this.audioService.playPauseAudio(this.exerciseService.response.FullAudioUrl, "response");
                }
            });
        } else {
            if (!this.keyboardToggleSelectionSubscription) this.keyboardToggleSelectionSubscription.unsubscribe();
            if (!!this.keyboardSubmitSelectionSubscription) this.keyboardSubmitSelectionSubscription.unsubscribe();
            if (!!this.keyboardToggleMediaSubscription) this.keyboardToggleMediaSubscription.unsubscribe();
        }
    }

    private setPromptResponseTypes() {
        if (this.exerciseService.exercise.card_type == "custom") {
            if (this.exerciseService.question.PromptType == "card") {
                this.exerciseService.promptTypes = this.exerciseService.question.exerciseOptions.prompt_preview_option
                    ? this.exerciseService.question.exerciseOptions.prompt_preview_option
                          .split(",")
                          .map((el) => el.trim())
                    : [];
            } else {
                this.exerciseService.promptTypes = [];
                if (this.exerciseService.question.prompt_audio_id !== null) {
                    this.exerciseService.promptTypes.push("a");
                    this.exerciseService.question.FullAudioUrl = this.exerciseService.question.audio.FullUrl;
                }

                if (this.exerciseService.question.prompt_image_id !== null) {
                    this.exerciseService.promptTypes.push("i");
                }

                if (this.exerciseService.question.prompt_video_id !== null) {
                    this.exerciseService.promptTypes.push("v");
                }
            }

            if (this.exerciseService.response.ResponseType == "card") {
                this.exerciseService.responseTypes = this.exerciseService.response.exerciseOptions
                    .responce_preview_option
                    ? this.exerciseService.response.exerciseOptions.responce_preview_option
                          .split(",")
                          .map((el) => el.trim())
                    : [];
            } else {
                this.exerciseService.responseTypes = [];
                if (this.exerciseService.response.response_audio_id !== null) {
                    this.exerciseService.responseTypes.push("a");
                    this.exerciseService.response.FullAudioUrl = this.exerciseService.response.audio.FullUrl;
                }

                if (this.exerciseService.response.response_image_id !== null) {
                    this.exerciseService.responseTypes.push("i");
                }
            }
        } else {
            this.exerciseService.promptTypes = this.exerciseService.question.exerciseOptions.prompt_preview_option
                ? this.exerciseService.question.exerciseOptions.prompt_preview_option.split(",").map((el) => el.trim())
                : [];
            this.exerciseService.responseTypes = this.exerciseService.question.exerciseOptions.responce_preview_option
                ? this.exerciseService.question.exerciseOptions.responce_preview_option
                      .split(",")
                      .map((el) => el.trim())
                : [];
        }
    }

    checkAudio(question) {
        if (question.FullAudioUrl) {
            this.audioService.playPauseAudio(question.FullAudioUrl, "prompt");
        } else if (
            this.exerciseService.exercise.card_type == "custom" &&
            question.type == "html" &&
            question.audio.FullUrl
        ) {
            this.audioService.playPauseAudio(question.audio.FullUrl, "prompt");
        }
    }

    /**
     * Called when either "True" or "False" is selected by the user
     * @param choice Either 'Y' for true or 'N' for false
     */
    checkAnswer(choice) {
        this.exerciseService.exerciseCompleted = true;
        this.setKeyboardListeners(false);
        this.mychoice = choice;
        if (this.exerciseService.firstTime) {
            this.setCorrectAnswer(this.exerciseService.question.exerciseOptions.response_true_false, choice);
            if (this.exerciseService.userAnswer == this.AnswerType.CORRECT) {
                if (this.exerciseService.responseTypes.indexOf("a") > -1) {
                    this.audioService.playPauseAudio(this.exerciseService.response.FullAudioUrl, "response");
                }
            } else {
                this.wrongAnswer();
                // this.exerciseService.nextButtonShouldBeClickable = true;
            }

            const params = Object();
            params.level_id = parseInt(this.localStorage.getItem("LevelID")) || null;
            params.unit_id = parseInt(this.localStorage.getItem("unitID")) || null;
            params.card_id =
                this.exerciseService.exercise.card_type == "custom"
                    ? this.customCardId
                    : this.exerciseService.question.id;
            params.activity_type = this.sessionType;
            params.user_id = this.exerciseService.user.id;
            params.answar_type = this.exerciseService.userAnswer == this.AnswerType.CORRECT ? "right" : "wrong";
            params.experiencecard_ids = this.exerciseService.cardIdArray.join();
            params.popup_status = true;

            if (this.sessionType == "exercise") {
                params.exercise_id = this.exerciseService.exercise.id;
                if (!!this.exerciseService.question.exerciseOptions?.id) {
                    params.exercise_option_id = this.exerciseService.question.exerciseOptions.id;
                } else if (!!this.exerciseService.question.exercise_option_id) {
                    params.exercise_option_id = this.exerciseService.question.exercise_option_id;
                } else {
                    console.warn(
                        "[TrueFalse] No exercise option ID found for exercise",
                        this.exerciseService.exercise.id,
                    );
                }
            } else if (this.sessionType == "review") {
                params.prompt_type = this.exerciseService.exercise.promotetype;
                params.response_type = this.exerciseService.exercise.responsetype;
                params.exercise_type = this.exerciseService.exercise.exercise_type;
            }

            this.exerciseService.answerGivenObj = params;
            this.exerciseService.firstTime = false;

            // if (this.exerciseService.userAnswer == this.AnswerType.CORRECT) {
            setTimeout(() => {
                this.goToNext();
            }, App.Settings.AVANCE_TO_NEXT_EXERCISE_DELAY_MS);
            // }
        }
    }

    wrongAnswer() {
        this.exerciseService.question.wrongAnswer = true;
        this.lessonService.wrongAnswerGiven(this.exerciseService.question);
        const wrongArray: any = [];

        if (this.exerciseService.exercise.card_type !== "custom") {
            if (
                this.exerciseService.question.exerciseOptions.type == "card" ||
                this.exerciseService.question.exerciseOptions.type == "group"
            ) {
                wrongArray.push(this.exerciseService.question);
            }
            if (this.exerciseService.response.id != this.exerciseService.question.id) {
                if (this.exerciseService.response.exerciseOptions.type == "card") {
                    wrongArray.push(this.exerciseService.response);
                }
            }
        } else {
            if (this.exerciseService.question.PromptType == "card") {
                wrongArray.push(this.exerciseService.question);
            }

            if (
                this.exerciseService.response.id != this.exerciseService.question.id &&
                this.exerciseService.response.ResponseType == "card"
            ) {
                wrongArray.push(this.exerciseService.response);
            }
        }

        setTimeout(() => {
            this.specifiedService.setWrongCards(wrongArray);
        }, App.Settings.DELAY_BEFORE_SHOWING_POPUP_MS);
    }

    private setCorrectAnswer(response, choice) {
        this.exerciseService.userAnswer = response == choice ? this.AnswerType.CORRECT : this.AnswerType.INCORRECT;
    }

    goToNext() {
        if (this.exerciseService.goToNext()) {
            this.specifiedService.answerGiven(this.exerciseService.answerGivenObj);
            this.exerciseService.cleanUp();
            this.keyboardHighlightedAnswer = "";
            this.keyboardHighlightedAnswerIndex = -1;
        }
    }

    getAudioIconUrl(audioService: AudioService, exerciseService: ExerciseService, audioUrl: string) {
        if (
            (audioService.getAudioSrc() != audioUrl || audioService.audioIsPaused()) &&
            (exerciseService.promptTypes.length == 1 || exerciseService.promptTypes.indexOf("i") < 0)
        ) {
            return "./assets/images/audio-large-mute.png";
        } else if (
            audioService.getAudioSrc() == audioUrl &&
            !audioService.audioIsPaused() &&
            (exerciseService.promptTypes.length == 1 || exerciseService.promptTypes.indexOf("i") < 0)
        ) {
            return "./assets/images/audio-large.png";
        } else if (
            (audioService.getAudioSrc() != audioUrl || audioService.audioIsPaused()) &&
            exerciseService.promptTypes.length > 1 &&
            exerciseService.promptTypes.indexOf("i") > -1
        ) {
            return "./assets/images/sound-mute-blue-btn.png";
        } else if (
            audioService.getAudioSrc() == audioUrl &&
            !audioService.audioIsPaused() &&
            exerciseService.promptTypes.length > 1 &&
            exerciseService.promptTypes.indexOf("i") > -1
        ) {
            return "./assets/images/sound-blue-btn.png";
        } else {
            return "./assets/images/sound-mute-blue-btn.png";
        }
    }
}
