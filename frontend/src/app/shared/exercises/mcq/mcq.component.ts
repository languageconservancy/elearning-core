import { Component, OnInit, OnDestroy, Input } from "@angular/core";
import { CookieService } from "app/_services/cookie.service";
import { Subscription } from "rxjs";
import { DeviceDetectorService } from "ngx-device-detector";

import { Loader } from "app/_services/loader.service";
import { LessonsService } from "app/_services/lessons.service";
import { ReviewService } from "app/_services/review.service";
import { ExerciseService } from "app/_services/exercise.service";
import { AnswerType } from "app/shared/utils/elearning-types";
import { LocalStorageService } from "app/_services/local-storage.service";
import { KeyboardService } from "app/shared/keyboard/keyboard.service";
import { OwoksapeUtils } from "app/shared/utils/owoksape-utils";
import * as App from "app/_constants/app.constants";
import { AudioService } from "app/_services/audio.service";

@Component({
    selector: "app-mcq",
    templateUrl: "./mcq.component.html",
    styleUrls: ["./mcq.component.scss"],
    providers: [ExerciseService],
})
export class McqComponent implements OnInit, OnDestroy {
    @Input() sessionType: string;
    public AnswerType = AnswerType;
    public specifiedService: LessonsService | ReviewService;
    public exerciseSubscription: Subscription;
    public questionSubscription: Subscription;
    private keyboardToggleMediaSubscription: Subscription;
    private keyboardToggleSelectionSubscription: Subscription;
    private keyboardSubmitSelectionSubscription: Subscription;
    public keyboardSelectedChoice = -1;
    public keyboardHighlightedResponseCardIndex = -1;
    public Math: any;
    public isMobile: boolean = false;
    public isTablet: boolean = false;
    public userResponseAnswers = Array<AnswerType>();

    constructor(
        private cookieService: CookieService,
        private lessonService: LessonsService,
        private reviewService: ReviewService,
        public exerciseService: ExerciseService,
        public audioService: AudioService,
        private loader: Loader,
        private localStorage: LocalStorageService,
        private keyboardService: KeyboardService,
        private deviceDetector: DeviceDetectorService,
    ) {
        this.Math = Math;
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
        this.getDeviceInfo();
    }

    ngOnInit() {
        this.setService();

        if (this.sessionType == "exercise") {
            this.exerciseSubscription = this.lessonService.currentExercise.subscribe((exercise) => {
                if (Object.keys(exercise).length > 0 && exercise.exercise_type == "multiple-choice") {
                    this.exerciseService.exercise = exercise;
                    this.exerciseService.userAnswer = this.AnswerType.NONE;
                    this.exerciseService.nextButtonShouldBeClickable = false;
                }
            });
        } else if (this.sessionType == "review") {
            this.exerciseSubscription = this.reviewService.currentExercise.subscribe((exercise) => {
                if (
                    Object.keys(exercise).length > 0 &&
                    exercise.exercise_type == "multiple-choice" &&
                    !!exercise.choices
                ) {
                    this.exerciseService.exerciseCompleted = false;
                    this.setKeyboardListeners(true);
                    this.exerciseService.exercise = exercise;
                    this.exerciseService.firstTime = true;
                    this.exerciseService.question = exercise.question;
                    this.exerciseService.choices = exercise.choices;
                    this.userResponseAnswers = new Array(exercise.choices.length).fill(AnswerType.NONE);
                    this.exerciseService.response = exercise.response;
                    this.exerciseService.userAnswer = this.AnswerType.NONE;
                    this.exerciseService.nextButtonShouldBeClickable = false;
                    this.exerciseService.setPromptResponseTypes();

                    setTimeout(() => {
                        if (this.exerciseService.promptTypes.indexOf("a") > -1) {
                            this.audioService.playPauseAudio(this.exerciseService.question.FullAudioUrl, "prompt");
                        }
                    }, 100);
                }
            });
        }

        if (this.sessionType == "exercise") {
            this.questionSubscription = this.lessonService.currentQuestion.subscribe((ques) => {
                this.exerciseService.firstTime = true;
                this.audioService.pauseAudio();
                if (
                    this.exerciseService.exercise.exercise_type == "multiple-choice" &&
                    Object.keys(ques).length > 0 &&
                    !!ques.choices
                ) {
                    this.exerciseService.exerciseCompleted = false;
                    this.setKeyboardListeners(true);
                    this.exerciseService.question = ques.question;
                    this.exerciseService.choices = ques.choices;
                    this.userResponseAnswers = new Array(ques.choices.length).fill(AnswerType.NONE);
                    this.exerciseService.response = ques.response;
                    this.exerciseService.userAnswer = this.AnswerType.NONE;

                    if (!this.exerciseService.question.FullAudioUrl && this.exerciseService.question.audio) {
                        this.exerciseService.question.FullAudioUrl = this.exerciseService.question.audio.FullUrl;
                    }

                    this.exerciseService.setPromptResponseTypes();
                    this.exerciseService.getCardIdList();
                    setTimeout(() => {
                        if (this.exerciseService.promptTypes.indexOf("a") > -1) {
                            this.audioService.playPauseAudio(this.exerciseService.question.FullAudioUrl, "prompt");
                        }
                    }, 100);
                }
            });
        }
    }

    ngOnDestroy() {
        if (!!this.exerciseSubscription) this.exerciseSubscription.unsubscribe();
        if (!!this.questionSubscription) this.questionSubscription.unsubscribe();
        this.setKeyboardListeners(false);
        this.audioService.pauseAndClearAudioSrc();
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
            if (!OwoksapeUtils.subscriptionClosed(this.keyboardToggleSelectionSubscription)) {
                return;
            }

            // Submit selected response card as the answer
            this.keyboardSubmitSelectionSubscription = this.keyboardService.submitOrCloseEvent.subscribe(() => {
                if (this.exerciseService.exerciseCompleted) {
                    return false;
                }
                const highlightedCard = this.keyboardHighlightedResponseCardIndex;
                if (highlightedCard < 0 || highlightedCard >= this.exerciseService.choices.length) {
                    return false;
                }
                this.answer(this.exerciseService.choices[highlightedCard]);
                this.keyboardHighlightedResponseCardIndex = -1;
            });
            // Toggle selected response card
            this.keyboardToggleSelectionSubscription = this.keyboardService.toggleSelectionEvent.subscribe((event) => {
                if (this.exerciseService.exerciseCompleted) {
                    return false;
                }
                if (event.shiftKey) {
                    this.keyboardHighlightedResponseCardIndex = OwoksapeUtils.decrementWrap(
                        this.keyboardHighlightedResponseCardIndex,
                        0,
                        this.exerciseService.choices.length,
                    );
                } else {
                    this.keyboardHighlightedResponseCardIndex = OwoksapeUtils.incrementWrap(
                        this.keyboardHighlightedResponseCardIndex,
                        0,
                        this.exerciseService.choices.length,
                    );
                }
            });
            // Toggle audio playback
            this.keyboardToggleMediaSubscription = this.keyboardService.toggleMediaEvent.subscribe(() => {
                if (this.exerciseService.exerciseCompleted) {
                    return false;
                }
                const highlightedCard = this.keyboardHighlightedResponseCardIndex;
                if (this.exerciseService.responseType == "a" || this.exerciseService.responseTypes.indexOf("a") > -1) {
                    if (
                        !!this.exerciseService.choices[highlightedCard] &&
                        !!this.exerciseService.choices[highlightedCard].FullAudioUrl
                    ) {
                        if (this.exerciseService.responseType == "a") {
                            this.audioService.playPauseAudio(
                                this.exerciseService.choices[highlightedCard].FullAudioUrl,
                                "response",
                            );
                        }
                    }
                } else if (
                    this.exerciseService.promptTypes.indexOf("a") > -1 &&
                    !!this.exerciseService.question.FullAudioUrl
                ) {
                    this.audioService.playPauseAudio(this.exerciseService.question.FullAudioUrl, "prompt");
                } else if (
                    !!this.exerciseService.choices[highlightedCard].exerciseOptions &&
                    !!this.exerciseService.choices[highlightedCard].exerciseOptions.responce_preview_option &&
                    this.exerciseService.choices[highlightedCard].exerciseOptions.responce_preview_option.indexOf("a") >
                        -1
                ) {
                    this.audioService.playPauseAudio(
                        this.exerciseService.choices[highlightedCard].FullAudioUrl,
                        "response",
                    );
                }
            });
        } else {
            if (!!this.keyboardSubmitSelectionSubscription) {
                this.keyboardSubmitSelectionSubscription.unsubscribe();
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

    customAnswer(choice) {
        if (this.exerciseService.responseTypes.indexOf("a") > -1 && choice.FullAudioUrl) {
            return;
        }
        if (this.exerciseService.exercise.card_type == "custom") {
            this.answer(choice);
        }
    }

    /**
     * Called when option is selected by the user.
     * @param choice The card object that chosen by the user
     */
    answer(choice) {
        this.exerciseService.exerciseCompleted = true;
        if (this.exerciseService.firstTime) {
            this.exerciseService.setCorrectAnswer(choice);

            const params = Object();
            params.level_id = parseInt(this.localStorage.getItem("LevelID")) || null;
            params.unit_id = parseInt(this.localStorage.getItem("unitID")) || null;
            params.card_id =
                this.exerciseService.exercise.card_type == "custom"
                    ? this.exerciseService.customCardId
                    : this.exerciseService.question.id;
            params.activity_type = this.sessionType;
            params.user_id = this.exerciseService.user.id;
            params.answar_type = this.exerciseService.userAnswer == this.AnswerType.CORRECT ? "right" : "wrong";
            params.experiencecard_ids = this.exerciseService.cardIdArray.join();
            params.popup_status = true;

            const choiceIdx = this.exerciseService.choices.indexOf(choice);
            this.userResponseAnswers[choiceIdx] = this.exerciseService.userAnswer;

            if (this.sessionType == "exercise") {
                // exercise-specific params
                params.exercise_id = this.exerciseService.exercise.id;
                if (!!this.exerciseService.question.exerciseOptions) {
                    params.exercise_option_id = this.exerciseService.question.exerciseOptions.id;
                } else if (!!this.exerciseService.question.exercise_option_id) {
                    params.exercise_option_id = this.exerciseService.question.exercise_option_id;
                } else {
                    console.warn(
                        "[mcq] No exercise option id found for exercise id: " +
                            this.exerciseService.exercise.id +
                            ", Question id: " +
                            this.exerciseService.question.id,
                    );
                }
            } else if (this.sessionType == "review") {
                // review-specific params
                params.prompt_type = this.exerciseService.exercise.promotetype;
                params.response_type = this.exerciseService.exercise.responsetype;
                params.exercise_type = this.exerciseService.exercise.exercise_type;
            }

            this.exerciseService.answerGivenObj = params;
            this.exerciseService.firstTime = false;

            this.setKeyboardListeners(false);
            this.exerciseService.setCloseWindowSubscription(true, this.keyboardService);

            if (this.exerciseService.userAnswer == this.AnswerType.INCORRECT) {
                // this.exerciseService.nextButtonShouldBeClickable = true;
                this.exerciseService.question.wrongAnswer = true;
                if (this.sessionType == "exercise") {
                    this.lessonService.wrongAnswerGiven(this.exerciseService.question);
                }
                const wrongArray: any = [];
                if (this.sessionType == "exercise") {
                    if (
                        this.exerciseService.question.exerciseOptions.type == "card" ||
                        this.exerciseService.question.exerciseOptions.type == "group"
                    ) {
                        wrongArray.push(this.exerciseService.question);
                        if (
                            this.exerciseService.response.id != this.exerciseService.question.id &&
                            this.exerciseService.response.exerciseOptions.type == "card"
                        ) {
                            wrongArray.push(this.exerciseService.response);
                        }
                    }
                } else if (this.sessionType == "review") {
                    wrongArray.push(this.exerciseService.question);
                    if (this.exerciseService.response.id != this.exerciseService.question.id) {
                        wrongArray.push(this.exerciseService.response);
                    }
                }
                setTimeout(() => {
                    this.specifiedService.setWrongCards(wrongArray);
                    this.goToNext();
                    this.setKeyboardListeners(true);
                    this.exerciseService.setCloseWindowSubscription(false, this.keyboardService);
                }, App.Settings.DELAY_BEFORE_SHOWING_POPUP_MS);
            } else {
                setTimeout(() => {
                    this.goToNext();
                    this.setKeyboardListeners(true);
                    this.exerciseService.setCloseWindowSubscription(false, this.keyboardService);
                }, App.Settings.AVANCE_TO_NEXT_EXERCISE_DELAY_MS);
            }
            // setTimeout(() => {
            // this.goToNext();
            // }, App.Settings.AVANCE_TO_NEXT_EXERCISE_DELAY_MS);
        }
    }

    checkAudio(audioUrl: string, audioType: string) {
        if (this.exerciseService.responseType == "a") {
            this.audioService.playPauseAudio(audioUrl, audioType);
        }
    }

    goToNext() {
        if (this.exerciseService.goToNext()) {
            this.specifiedService.answerGiven(this.exerciseService.answerGivenObj);
            this.keyboardHighlightedResponseCardIndex = -1;
            this.exerciseService.cleanUp();
        }
    }

    questionAudioIsPlaying(audioService: AudioService, exerciseService: ExerciseService) {
        return (
            audioService.getAudioSrc() == exerciseService.question.FullAudioUrl &&
            !audioService.audioIsPaused() &&
            audioService.audioType === "prompt"
        );
    }

    getAudioIconUrl(audioService: AudioService, exerciseService: ExerciseService) {
        if (
            (audioService.getAudioSrc() != exerciseService.question.FullAudioUrl || audioService.audioIsPaused()) &&
            (exerciseService.promptTypes.length == 1 || exerciseService.promptTypes.indexOf("i") < 0)
        ) {
            return "./assets/images/audio-large-mute.png";
        } else if (
            audioService.getAudioSrc() == exerciseService.question.FullAudioUrl &&
            !audioService.audioIsPaused() &&
            audioService.audioType === "prompt" &&
            (exerciseService.promptTypes.length == 1 || exerciseService.promptTypes.indexOf("i") < 0)
        ) {
            return "./assets/images/audio-large.png";
        } else if (
            (audioService.getAudioSrc() != exerciseService.question.FullAudioUrl || audioService.audioIsPaused()) &&
            exerciseService.promptTypes.length > 1 &&
            exerciseService.promptTypes.indexOf("i") > -1
        ) {
            return "./assets/images/sound-mute-blue-btn.png";
        } else if (
            audioService.getAudioSrc() == exerciseService.question.FullAudioUrl &&
            !audioService.audioIsPaused() &&
            audioService.audioType === "prompt" &&
            exerciseService.promptTypes.length > 1 &&
            exerciseService.promptTypes.indexOf("i") > -1
        ) {
            return "./assets/images/sound-blue-btn.png";
        } else {
            return "./assets/images/sound-mute-blue-btn.png";
        }
    }
}
