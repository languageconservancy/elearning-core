import { Component, OnInit, OnDestroy, Input } from "@angular/core";
import { Subscription } from "rxjs";
import { DeviceDetectorService } from "ngx-device-detector";

import { CookieService } from "app/_services/cookie.service";
import { LessonsService } from "app/_services/lessons.service";
import { ReviewService } from "app/_services/review.service";
import { ExerciseService } from "app/_services/exercise.service";
import { AnswerType } from "app/shared/utils/elearning-types";
import { LocalStorageService } from "app/_services/local-storage.service";
import { OwoksapeUtils } from "app/shared/utils/owoksape-utils";
import { KeyboardService } from "app/shared/keyboard/keyboard.service";
import { AudioService } from "app/_services/audio.service";
import { SnackbarService } from "app/_services/snackbar.service";

@Component({
    selector: "app-match-pair",
    templateUrl: "./match-pair.component.html",
    styleUrls: ["./match-pair.component.scss"],
    providers: [ExerciseService],
})
export class MatchPairComponent implements OnInit, OnDestroy {
    public questions: any = [];
    public type: string = "";
    public promptType: string = "";
    public selectedQuestionId: number = -1;
    public selectedAnswerId: number = -1;
    public matched: any = {
        questions: [],
        answers: [],
    };
    public userPromptAnswers = Array<AnswerType>();
    public userResponseAnswers = Array<AnswerType>();

    public highlightedChoiceIndex: number = -1;
    public highlightedPromptIndex: number = -1;
    private togglingPrompt: boolean = true;

    @Input() sessionType: string;
    public AnswerType = AnswerType;
    public exerciseSubscription: Subscription;
    public popupSubscription: Subscription;
    public nextSubExeSubscription: Subscription;
    private keyboardSubmitSwitchSubscription: Subscription;
    private keyboardToggleSelectionSubscription: Subscription;
    private keyboardToggleMediaSubscription: Subscription;
    private specifiedService;
    public isMobile: boolean = false;
    public isTablet: boolean = false;

    constructor(
        private cookieService: CookieService,
        private lessonService: LessonsService,
        private reviewService: ReviewService,
        public exerciseService: ExerciseService,
        public audioService: AudioService,
        private localStorage: LocalStorageService,
        private keyboardService: KeyboardService,
        private snackbarService: SnackbarService,
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

        this.popupSubscription = this.specifiedService.popup.subscribe((res) => {
            this.handlePopup(res);
        });

        /* This subscription handles the case where a match-the-pairs question
		   is answered correctly, so no popup shows up, which gets handled
		   by the popup subscription above. */
        this.nextSubExeSubscription = this.reviewService.nextSubExe.subscribe(() => {
            if (this.questions.length > 0 && this.matched.questions.length > 0) {
                this.nextEx();
            }
        });

        this.exerciseSubscription = this.specifiedService.currentExercise.subscribe((exercise) => {
            this.highlightedChoiceIndex = -1;
            this.highlightedPromptIndex = -1;
            this.audioService.pauseAudio();
            this.questions = [];
            this.matched.questions = [];
            this.matched.answers = [];
            if (Object.keys(exercise).length > 0 && exercise.exercise_type == "match-the-pair") {
                this.exerciseService.exerciseCompleted = false;
                this.setKeyboardListeners(true);
                this.exerciseService.exercise = exercise;
                this.exerciseService.promptType = exercise.promteresponsetype
                    ? exercise.promteresponsetype.split("-")[0]
                    : "";
                this.exerciseService.responseType = exercise.promteresponsetype
                    ? exercise.promteresponsetype.split("-")[1]
                    : "";
                this.setQuestions();
                this.setChoices();
            }
        });
    }

    ngOnDestroy() {
        this.exerciseSubscription.unsubscribe();
        this.popupSubscription.unsubscribe();
        this.nextSubExeSubscription.unsubscribe();
        this.setKeyboardListeners(false);
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
            if (!OwoksapeUtils.subscriptionClosed(this.keyboardSubmitSwitchSubscription)) {
                return;
            }

            // Submit the choice as a match, or change toggling between prompt cards and option cards
            this.keyboardSubmitSwitchSubscription = this.keyboardService.submitOrCloseEvent.subscribe((event) => {
                if (event.shiftKey) {
                    // Switch between toggling prompt and option cards
                    this.togglingPrompt = !this.togglingPrompt;
                    return true;
                }
                if (this.togglingPrompt && this.questions[this.highlightedPromptIndex]) {
                    this.togglingPrompt = false;
                    return true;
                }
                if (!this.exerciseService.choices[this.highlightedChoiceIndex]) {
                    return false;
                }
                if (
                    this.highlightedChoiceIndex >= 0 &&
                    this.highlightedChoiceIndex < this.exerciseService.choices.length &&
                    this.matched.answers.indexOf(this.exerciseService.choices[this.highlightedChoiceIndex]?.id) < 0
                ) {
                    // Submit the selected choice as a match
                    this.selectAns(this.exerciseService.choices[this.highlightedChoiceIndex]);
                    this.highlightedChoiceIndex = -1;
                }
            });
            // Toggle hightlighted prompt or option card
            this.keyboardToggleSelectionSubscription = this.keyboardService.toggleSelectionEvent.subscribe((event) => {
                this.toggleIndex(this.togglingPrompt, event.shiftKey);

                if (this.togglingPrompt) {
                    if (this.highlightedPromptIndex >= 0 && this.highlightedPromptIndex < this.questions.length) {
                        // Select the highlighted prompt card
                        this.selectQuest(this.questions[this.highlightedPromptIndex].question);
                    }
                }
            });
            // Toggle audio playback
            this.keyboardToggleMediaSubscription = this.keyboardService.toggleMediaEvent.subscribe(() => {
                if (
                    this.togglingPrompt ||
                    (this.exerciseService.promptType == "a" && this.exerciseService.responseType != "a")
                ) {
                    if (this.exerciseService.promptType == "a") {
                        // Play/pause the audio for the highlighted prompt card
                        this.audioService.playPauseAudio(
                            this.questions[this.highlightedPromptIndex].question.FullAudioUrl,
                            "question",
                        );
                    }
                }
                if (
                    !this.togglingPrompt ||
                    (this.exerciseService.responseType == "a" && this.exerciseService.promptType != "a")
                ) {
                    if (
                        this.highlightedChoiceIndex >= 0 &&
                        this.highlightedChoiceIndex < this.exerciseService.choices.length &&
                        (this.exerciseService.responseType == "a" ||
                            this.exerciseService.responseTypes.indexOf("a") > -1) &&
                        !!this.exerciseService.choices[this.highlightedChoiceIndex] &&
                        this.exerciseService.choices[this.highlightedChoiceIndex].FullAudioUrl
                    ) {
                        // Play/pause the audio for the highlighted option card
                        this.audioService.playPauseAudio(
                            this.exerciseService.choices[this.highlightedChoiceIndex].FullAudioUrl,
                            "response",
                        );
                    }
                }
            });
        } else {
            if (!!this.keyboardSubmitSwitchSubscription) {
                this.keyboardSubmitSwitchSubscription.unsubscribe();
            }
            if (!!this.keyboardToggleSelectionSubscription) {
                this.keyboardToggleSelectionSubscription.unsubscribe();
            }
            if (!!this.keyboardToggleMediaSubscription) {
                this.keyboardToggleMediaSubscription.unsubscribe();
            }
        }
    }

    toggleIndex(togglingPrompt: boolean, decrementing: boolean) {
        if (decrementing) {
            if (togglingPrompt) {
                do {
                    this.highlightedPromptIndex = OwoksapeUtils.decrementWrap(
                        this.highlightedPromptIndex,
                        -1,
                        this.questions.length - 1,
                    );
                } while (
                    this.highlightedPromptIndex >= 0 &&
                    this.matched.questions.indexOf(this.questions[this.highlightedPromptIndex]?.id) > -1
                );
            } else {
                do {
                    this.highlightedChoiceIndex = OwoksapeUtils.decrementWrap(
                        this.highlightedChoiceIndex,
                        -1,
                        this.exerciseService.choices.length - 1,
                    );
                } while (
                    this.highlightedChoiceIndex >= 0 &&
                    this.matched.answers.indexOf(this.exerciseService.choices[this.highlightedChoiceIndex]?.id) > -1
                );
            }
        } else {
            if (togglingPrompt) {
                do {
                    this.highlightedPromptIndex = OwoksapeUtils.incrementWrap(
                        this.highlightedPromptIndex,
                        -1,
                        this.questions.length - 1,
                    );
                } while (
                    this.highlightedPromptIndex >= 0 &&
                    this.matched.questions.indexOf(this.questions[this.highlightedPromptIndex]?.id) > -1
                );
            } else {
                do {
                    this.highlightedChoiceIndex = OwoksapeUtils.incrementWrap(
                        this.highlightedChoiceIndex,
                        -1,
                        this.exerciseService.choices.length - 1,
                    );
                } while (
                    this.highlightedChoiceIndex >= 0 &&
                    this.matched.answers.indexOf(this.exerciseService.choices[this.highlightedChoiceIndex]?.id) > -1
                );
            }
        }
    }

    private handlePopup(res) {
        if (res.popUpClosed) {
            // if (!this.audioService.audioIsPaused() && this.exerciseService.audio.currentTime) {
            // this.exerciseService.audio.pause();
            // }
            if (this.questions.length > 0 && this.matched.questions.length > 0) {
                this.nextEx();
            }
            this.questions.forEach((ques) => {
                if (ques.question.id == this.selectedQuestionId) {
                    if (this.exerciseService.promptType.indexOf("a") > -1) {
                        setTimeout(() => {
                            this.audioService.playPauseAudio(ques.question.FullAudioUrl, "question");
                        }, 200);
                    }
                }
            });
        }
    }

    private setQuestions() {
        this.questions.splice(0, this.questions.length);
        this.userPromptAnswers = new Array(this.questions.length).fill(AnswerType.NONE);
        this.exerciseService.exercise.questions.forEach((ques) => {
            if (this.exerciseService.exercise.card_type == "custom") {
                if (ques.question.PromptType == "card") {
                    ques.promptArray = ques.question.exerciseOptions.prompt_preview_option
                        ? ques.question.exerciseOptions.prompt_preview_option.split(",").map((el) => el.trim())
                        : [];
                } else {
                    ques.promptArray = [];
                    if (ques.question.prompt_audio_id !== null) {
                        ques.promptArray.push("a");
                        ques.question.FullAudioUrl = ques.question.audio.FullUrl;
                    }

                    if (ques.question.prompt_image_id !== null) {
                        ques.promptArray.push("i");
                    }
                }
            } else {
                if (this.sessionType == "exercise") {
                    ques.promptArray = ques.question.exerciseOptions.prompt_preview_option
                        ? ques.question.exerciseOptions.prompt_preview_option.split(",").map((el) => el.trim())
                        : [];
                } else if (this.sessionType == "review") {
                    ques.promptArray = [this.exerciseService.promptType];
                }
            }
            this.questions.push(ques);
        });
        this.selectedQuestionId = -1;
    }

    private setChoices() {
        if (this.exerciseService.choices.length) {
            this.exerciseService.choices.splice(0, this.exerciseService.choices.length);
            this.userResponseAnswers = new Array(this.exerciseService.choices.length).fill(AnswerType.NONE);
        }
        this.exerciseService.exercise.choices.forEach((choice) => {
            if (this.exerciseService.exercise.card_type == "custom") {
                if (choice.ResponseType == "card") {
                    choice.respArray = choice.exerciseOptions.responce_preview_option
                        ? choice.exerciseOptions.responce_preview_option.split(",").map((el) => el.trim())
                        : [];
                } else {
                    choice.respArray = [];
                    if (choice.response_audio_id !== null) {
                        choice.respArray.push("a");
                        choice.FullAudioUrl = choice.audio.FullUrl;
                    }

                    if (choice.response_image_id !== null) {
                        choice.respArray.push("i");
                    }
                }
            } else {
                if (this.sessionType == "exercise") {
                    choice.respArray = choice.exerciseOptions.responce_preview_option
                        ? choice.exerciseOptions.responce_preview_option.split(",").map((el) => el.trim())
                        : [];
                } else if (this.sessionType == "review") {
                    choice.respArray = [this.exerciseService.responseType];
                }
            }
            this.exerciseService.choices.push(choice);
        });
    }

    selectQuest(question) {
        if (this.matched.questions.indexOf(question.id) == -1) {
            this.selectedQuestionId = question.id;
        }
    }

    // FIXME
    imageResp(ans: any) {
        if (ans.respArray.indexOf("a") < 0) {
            this.selectAns(ans);
        }
    }

    selectAns(ans: any) {
        // if the selected answer isn't already matched
        if (this.matched.answers.indexOf(ans.id) < 0) {
            // and if a question is selected
            if (this.selectedQuestionId > -1) {
                // for some reason, add the selected question to the matched questions
                this.matched.questions.push(this.selectedQuestionId);
                // update the selected answer
                this.selectedAnswerId = ans.id;
                this.checkAnswer();
            } else {
                this.showError("Please select a question first");
            }
        }
    }

    // FIXME: shouldn't we just grab the question selected instead of for-looping
    // this could also avoid the problem of playing audio for multiple items
    // that are similar, i think. For example:
    // const questionIdx = this.questions.findIndex(ques => ques.question.id == this.selectedQuestionId);
    // const question = this.questions[questionIdx];
    // if (this.selectedAnswerId == question.response.id) {
    // 	... // correct answer
    // } else {
    // 	... // wrong answer
    // }
    checkAnswer() {
        // get the index of the selected question
        const questionIdx = this.questions.findIndex((ques) => ques.question.id == this.selectedQuestionId);
        if (questionIdx < 0) {
            console.warn("Couldn't find question with id: ", this.selectedQuestionId);
            return;
        }

        const selectedQuestion = this.questions[questionIdx];
        let choiceIdx = -1;
        // if answer is correct (matches the question response)
        if (this.selectedAnswerId == selectedQuestion.response.id) {
            this.exerciseService.userAnswer = AnswerType.CORRECT;
            // add selected answer to the array of correctly matched answers
            this.matched.answers.push(selectedQuestion.response.id);
            for (let i = 0; i < this.exerciseService.choices.length; ++i) {
                const choice = this.exerciseService.choices[i];
                if (choice.id == this.selectedAnswerId) {
                    choiceIdx = i;
                    if (choice.respArray.indexOf("a") > -1) {
                        this.audioService.playPauseAudio(choice.FullAudioUrl);
                    }
                    break;
                }
            }
        } else {
            this.exerciseService.userAnswer = AnswerType.INCORRECT;
            selectedQuestion.question.wrongAnswer = true;
            if (this.sessionType == "exercise") {
                this.lessonService.wrongAnswerGiven(selectedQuestion.question);
            }
            this.selectedAnswerId = -1;
        }

        // Set prompt answer to correct or incorrect
        this.userPromptAnswers[questionIdx] = this.exerciseService.userAnswer;

        // Only set response answer to correct if the user's answer was correct,
        // otherwise, it will remain as NONE so it can cotninue to be selected.
        if (this.exerciseService.userAnswer == AnswerType.CORRECT) {
            this.userResponseAnswers[choiceIdx] = AnswerType.CORRECT;
        }

        setTimeout(() => {
            this.sendAnswer(selectedQuestion);
        }, 100);
    }

    /**
     * Set wrongArray (array of questions and responses answered incorrectly)
     * Set cardIdArray (array of questions and responses use has experienced)
     * Set cardId (id of current question that was just answered)
     */
    private sendAnswer(ques) {
        const wrongArray: any = [];
        const cardIdArray: any = [];

        this.setKeyboardListeners(false);
        if (this.exerciseService.exercise.card_type !== "custom") {
            if (!!ques.question.exerciseOptions) {
                if (
                    !!ques.question.exerciseOptions &&
                    (ques.question.exerciseOptions.type == "card" || ques.question.exerciseOptions.type == "group")
                ) {
                    wrongArray.push(ques.question);
                    cardIdArray.push(ques.question.id);
                }
                if (ques.response.id != ques.question.id) {
                    if (ques.question.exerciseOptions.type == "card") {
                        wrongArray.push(ques.response);
                        cardIdArray.push(ques.response.id);
                    }
                }
            } else {
                wrongArray.push(ques.question);
                if (ques.response.id != ques.question.id) {
                    wrongArray.push(ques.response);
                    cardIdArray.push(ques.response.id);
                }
            }
        } else {
            if (ques.question.PromptType == "card") {
                wrongArray.push(ques.question);
                cardIdArray.push(ques.question.id);
            }

            if (ques.response.id != ques.question.id && ques.response.ResponseType == "card") {
                wrongArray.push(ques.response);
                cardIdArray.push(ques.response.id);
            }
        }
        const params = Object();
        params.level_id = parseInt(this.localStorage.getItem("LevelID")) || null;
        params.unit_id = parseInt(this.localStorage.getItem("unitID")) || null;
        params.exercise_id = this.exerciseService.exercise.id;
        params.card_id = ques.question.PromptType == "html" ? null : ques.question.id;
        params.activity_type = this.sessionType;
        params.user_id = this.exerciseService.user.id;
        params.answar_type = this.exerciseService.userAnswer == this.AnswerType.CORRECT ? "right" : "wrong";
        params.matchnpair = true;
        params.experiencecard_ids = cardIdArray.join();
        params.popup_status = true;
        if (!!ques.response.exercise_option_id) {
            params.exercise_option_id = ques.response.exercise_option_id;
        } else if (!!ques.response.exerciseOptions) {
            params.exercise_option_id = ques.response.exerciseOptions.id;
        } else {
            console.warn("[match-pair] No exercise option id for response", ques.response);
        }

        // Notify subscribers of newly given answer
        if (this.sessionType == "review") {
            params.prompt_type = this.exerciseService.exercise.promotetype;
            params.response_type = this.exerciseService.exercise.responsetype;
            params.exercise_type = this.exerciseService.exercise.exercise_type;
        }

        // FIXME why is timeout here but not for review-match-pair
        // Notify subscribers of newly incorrectly answered cards
        // setTimeout(() => {
        this.specifiedService.setWrongCards(wrongArray);
        // }, 100);
        this.specifiedService.answerGiven(params);

        // Reset selected answer id
        this.selectedQuestionId = this.selectedAnswerId = -1;
        this.questions.forEach((ques) => {
            if (this.matched.questions.indexOf(ques.question.id) < 0 && this.selectedQuestionId > -1) {
                this.selectedQuestionId = ques.question.id;
            }
        });
        this.togglingPrompt = true;
    }

    nextEx() {
        if (this.questions.length == this.matched.questions.length) {
            this.selectedQuestionId = null;
            this.selectedAnswerId = null;
            this.matched.questions = [];
            this.matched.answers = [];
            this.highlightedChoiceIndex = -1;
            this.exerciseService.exerciseCompleted = true;
            this.specifiedService.nextScreen(true);
        } else {
            this.setKeyboardListeners(true);
        }
    }

    showError(message) {
        this.snackbarService.showSnackbar({ status: false, msg: message });
        setTimeout(() => {
            this.exerciseService.exerciseCompleted = false;
        }, 2500);
    }

    getPromptAudioIconUrl(audioService: AudioService, ques: any) {
        if (audioService.getAudioSrc() != ques.question.FullAudioUrl || audioService.audioType != "question") {
            if (ques.promptArray.length == 1) {
                return "./assets/images/audio-large-mute.png";
            } else {
                return "./assets/images/sound-mute-blue-btn.png";
            }
        } else if (audioService.getAudioSrc() == ques.question.FullAudioUrl || audioService.audioType == "question") {
            if (ques.promptArray.length == 1) {
                return "./assets/images/audio-large.png";
            } else {
                return "./assets/images/sound-blue-btn.png";
            }
        }
    }

    getResponseAudioIconUrl(audioService: AudioService, choice: any) {
        if (audioService.getAudioSrc() != choice.FullAudioUrl || audioService.audioType != "response") {
            if (choice.respArray.length == 1) {
                return "./assets/images/audio-large-mute.png";
            } else {
                return "./assets/images/sound-mute-blue-btn.png";
            }
        } else if (audioService.getAudioSrc() == choice.FullAudioUrl || audioService.audioType == "response") {
            if (choice.respArray.length == 1) {
                return "./assets/images/audio-large.png";
            } else {
                return "./assets/images/sound-blue-btn.png";
            }
        } else {
            console.warn("getResponseAudioIconUrl didn't work.");
        }
    }

    checkAudio(audioUrl) {
        if (this.exerciseService.responseType == "a") {
            this.audioService.playPauseAudio(audioUrl);
        }
    }
}
