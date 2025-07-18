import { Component, OnInit, OnDestroy, Input } from "@angular/core";
import { CookieService } from "app/_services/cookie.service";
import { Subscription } from "rxjs";

import { LessonsService } from "app/_services/lessons.service";
import { ReviewService } from "app/_services/review.service";
import { ExerciseService } from "app/_services/exercise.service";
import { AnswerType } from "app/shared/utils/elearning-types";
import { LocalStorageService } from "app/_services/local-storage.service";
import { KeyboardService } from "app/shared/keyboard/keyboard.service";
import { KeyboardConfigService } from "app/_services/keyboard-config.service";
import { OwoksapeUtils } from "app/shared/utils/owoksape-utils";
import { AudioService } from "app/_services/audio.service";

declare let jQuery: any;

@Component({
    selector: "app-anagram",
    templateUrl: "./anagram.component.html",
    styleUrls: ["./anagram.component.scss"],
    providers: [ExerciseService],
})
export class AnagramComponent implements OnInit, OnDestroy {
    public answerSubmitted: boolean = false;
    public mainResponse: string = ""; // target language ('l') or English ('e')
    public inputCountArray: any = []; // array of correct answers with replacement of non-standard characters
    public jumbledKeys: any = []; // buttons for user to click on
    public punctuationArray: any = []; // array of punctuation for each word
    public answer: any = []; // array of values in <input>s
    public activeInputIndex: number = 0; // which <input> user is editing
    public answerIsCorrect: AnswerType[] = []; // array of correctness of each word in the answer
    public finalAnswer: AnswerType = AnswerType.NONE; // overall correctness of the answer

    @Input() sessionType: string; // exercise or review
    public AnswerType = AnswerType; // enum for answer types
    public exerciseSubscription: Subscription; // subscription to current exercise
    public questionSubscription: Subscription; // subscription to current question
    public popupSubscription: Subscription; // subscription to popup
    private keyboardBackspaceSubscription: Subscription; // subscription to backspace
    private keyboardToggleSelectionSubscription: Subscription; // subscription to toggle selection
    private keyboardToggleMediaSubscription: Subscription; // subscription to toggle media
    private keyboardSubmitSubscription: Subscription; // subscription to submit
    private keyboardTypingSubscription: Subscription; // subscription to typing
    private specifiedService; // service to use (lesson or review)

    constructor(
        private cookieService: CookieService,
        private lessonService: LessonsService,
        private reviewService: ReviewService,
        public exerciseService: ExerciseService,
        public audioService: AudioService,
        private localStorage: LocalStorageService,
        private keyboardService: KeyboardService,
        private keyboardConfigService: KeyboardConfigService,
    ) {
        // Ensure the user is logged in
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

        // Initialize OwoksapeUtils with keyboard config service
        OwoksapeUtils.setKeyboardConfigService(this.keyboardConfigService);

        // Exercise subscription
        this.exerciseSubscription = this.specifiedService.currentExercise.subscribe((exercise) => {
            this.exerciseService.exercise = {};
            this.exerciseService.question = {};
            this.answer = [];
            if (Object.keys(exercise).length > 0 && exercise.exercise_type == "anagram") {
                this.exerciseService.exercise = exercise;
                const promptResponses = exercise.promteresponsetype.split("-");
                this.mainResponse = promptResponses[1];
                this.exerciseService.userAnswer = this.AnswerType.NONE;
                this.setKeyboardListeners(true);

                if (this.sessionType == "review") {
                    this.exerciseService.question = exercise.question;
                    this.exerciseService.promptTypes = [promptResponses[0]];
                    this.exerciseService.responseTypes = [promptResponses[1]];
                    this.answerSubmitted = false;
                    this.setKeyboard();
                    if (this.exerciseService.promptTypes.indexOf("a") > -1) {
                        this.audioService.playPauseAudio(
                            this.exerciseService.question.FullAudioUrl,
                            "prompt",
                        );
                    }
                }
            }
        });

        // Question subscription
        if (this.sessionType == "exercise") {
            this.questionSubscription = this.lessonService.currentQuestion.subscribe((ques) => {
                this.audioService.pauseAudio();
                if (
                    this.exerciseService.exercise.exercise_type == "anagram" &&
                    Object.keys(ques).length > 0
                ) {
                    this.exerciseService.question = {};
                    this.exerciseService.question = ques.question;
                    this.exerciseService.promptTypes = ques.question
                        ? ques.question.exerciseOptions.prompt_preview_option
                              .split(",")
                              .map((el) => el.trim())
                        : [];
                    this.exerciseService.responseTypes = ques.question
                        ? ques.question.exerciseOptions.responce_preview_option
                              .split(",")
                              .map((el) => el.trim())
                        : [];
                    this.answerSubmitted = false;
                    this.setKeyboard();
                    if (this.exerciseService.promptTypes.indexOf("a") > -1) {
                        this.audioService.playPauseAudio(
                            this.exerciseService.question.FullAudioUrl,
                            "prompt",
                        );
                    }
                }
            });
        }

        // Popup subscription
        this.popupSubscription = this.specifiedService.popup.subscribe((res) => {
            if (res.popUpClosed && this.answerSubmitted) {
                this.audioService.pauseAudio();
            }
        });
    }

    /**
     * Unsubscribe from all subscriptions when the component is destroyed.
     */
    ngOnDestroy() {
        if (!!this.exerciseSubscription) this.exerciseSubscription.unsubscribe();
        if (!!this.questionSubscription) this.questionSubscription.unsubscribe();
        if (!!this.popupSubscription) this.popupSubscription.unsubscribe();
        this.setKeyboardListeners(false);
        this.audioService.pauseAndClearAudioSrc();
    }

    /**
     * Set the service to use based on the session type.
     */
    private setService() {
        if (this.sessionType == "exercise") {
            this.specifiedService = this.lessonService;
        } else if (this.sessionType == "review") {
            this.specifiedService = this.reviewService;
        }
    }

    /**
     * Set the keyboard listeners based on whether the user is currently typing or not.
     * @param turnOn Whether to turn on or off the keyboard listeners.
     * @returns void
     */
    setKeyboardListeners(turnOn: boolean) {
        if (turnOn) {
            if (!OwoksapeUtils.subscriptionClosed(this.keyboardBackspaceSubscription)) {
                return;
            }
            // Backspace
            this.keyboardBackspaceSubscription = this.keyboardService.backspaceEvent.subscribe(
                (event) => {
                    event.preventDefault();
                    this.backspace();
                },
            );
            // Toggle Selection
            this.keyboardToggleSelectionSubscription =
                this.keyboardService.toggleSelectionEvent.subscribe((event) => {
                    if (event.shiftKey) {
                        this.setActiveInput(
                            OwoksapeUtils.decrementWrap(
                                this.activeInputIndex,
                                0,
                                this.inputCountArray.length - 1,
                            ),
                        );
                    } else {
                        this.setActiveInput(
                            OwoksapeUtils.incrementWrap(
                                this.activeInputIndex,
                                0,
                                this.inputCountArray.length - 1,
                            ),
                        );
                    }
                });
            // Toggle media
            this.keyboardToggleMediaSubscription = this.keyboardService.toggleMediaEvent.subscribe(
                () => {
                    if (this.exerciseService.promptTypes.indexOf("a") > -1) {
                        this.audioService.playPauseAudio(
                            this.exerciseService.question.FullAudioUrl,
                            "prompt",
                        );
                    }
                },
            );
            // Submit
            this.keyboardSubmitSubscription = this.keyboardService.submitOrCloseEvent.subscribe(
                () => {
                    if (!this.answerSubmitted) {
                        this.submitAnswer();
                    }
                },
            );
            // Typing
            this.keyboardTypingSubscription = this.keyboardService.typingEvent.subscribe(
                (event) => {
                    this.handleTypingLogic(event);
                },
            );
        } else {
            // Unsubscribe from all keyboard events
            if (!!this.keyboardBackspaceSubscription) {
                this.keyboardBackspaceSubscription.unsubscribe();
            }
            if (!!this.keyboardToggleSelectionSubscription) {
                this.keyboardToggleSelectionSubscription.unsubscribe();
            }
            if (!!this.keyboardToggleMediaSubscription) {
                this.keyboardToggleMediaSubscription.unsubscribe();
            }
            if (!!this.keyboardSubmitSubscription) {
                this.keyboardSubmitSubscription.unsubscribe();
            }
            if (!!this.keyboardTypingSubscription) {
                this.keyboardTypingSubscription.unsubscribe();
            }
        }
    }

    /**
     * Handle the logic for typing in the input fields.
     * @param event The keydown event.
     * @returns void
     */
    handleTypingLogic(event: KeyboardEvent) {
        const pressedKeys: any = [];
        const orderedWord = this.inputCountArray[this.activeInputIndex].split("");
        const correctChar = this.answer[this.activeInputIndex]
            ? orderedWord[this.answer[this.activeInputIndex].length]
            : "";
        const jumbledKeysLowerCase = this.toLowerCase(this.jumbledKeys);

        for (let i = 0; i < this.jumbledKeys.length; ++i) {
            if (this.jumbledKeys[i].disabled) continue;

            if (event.key == this.jumbledKeys[i] && this.jumbledKeys[i] == correctChar) {
                // If typed character is exactly correct, use it
                this.jumbledLetterPressed(this.jumbledKeys[i]);
                return;
            } else if (
                jumbledKeysLowerCase[i] == event.key.toLowerCase() ||
                jumbledKeysLowerCase[i] ==
                    OwoksapeUtils.convertNonTargetLanguageKeyboardToTargetKeyboard(event)
            ) {
                // Otherwise add key to array and figure out which is correct below
                pressedKeys.push(this.jumbledKeys[i]);
            }
        }

        // If only one match, case is wrong, but use it anyway
        if (pressedKeys.length === 1) {
            this.jumbledLetterPressed(pressedKeys[0]);
            return;
        }

        // If more than one match, select the one that comes first
        // in the actual answer
        for (let i = 0; i < orderedWord.length; ++i) {
            for (let j = 0; j < pressedKeys.length; ++j) {
                if (orderedWord[i] === pressedKeys[j].key) {
                    this.jumbledLetterPressed(pressedKeys[j]);
                    return;
                }
            }
        }
    }

    /**
     * Convert the options in the array to lowercase.
     * @param array The array of options.
     * @returns the array of options with all options converted to lowercase.
     */
    toLowerCase(array) {
        return array.map(function (v) {
            return v.key.toLowerCase();
        });
    }

    /**
     * Shuffle the array of options.
     * @param array The array of options.
     * @returns the shuffled array of options.
     */
    private shuffle(array) {
        let currentIndex = array.length,
            temporaryValue,
            randomIndex;
        // While there remain elements to shuffle...
        while (0 !== currentIndex) {
            // Pick a remaining element...
            randomIndex = Math.floor(Math.random() * currentIndex);
            currentIndex -= 1;
            // And swap it with the current element.
            temporaryValue = array[currentIndex];
            array[currentIndex] = array[randomIndex];
            array[randomIndex] = temporaryValue;
        }

        return array;
    }

    /**
     * Input text: qʼʷáalʼs
     * Jumbled text: [qʼʷ, áa, lʼ, s]
     * @param text The text to jumble.
     * @param language The language of the text.
     * @returns the jumbled array of options.
     */
    createOptionsArray(text: string) {
        let jumbledArray = [];

        // Use keyboard config if available, otherwise fall back to hardcoded values
        if (this.keyboardConfigService) {
            const treatedAsOneChar = this.keyboardConfigService.getCharCombosTreatedAsOneChar();
            if (treatedAsOneChar.length > 0) {
                // Loop through characters in text
                for (let i = 0; i < text.length; ++i) {
                    let matched = false;
                    if (text.length - i >= 3) {
                        // Check for 3-character matches
                        const nextThreeChars = text.substring(i, i + 3);
                        if (treatedAsOneChar.indexOf(nextThreeChars) >= 0) {
                            jumbledArray.push(nextThreeChars);
                            i += 2;
                            matched = true;
                        }
                        if (!matched) {
                            // Check for 2-character matches
                            const nextTwoChars = text.substring(i, i + 2);
                            if (treatedAsOneChar.indexOf(nextTwoChars) >= 0) {
                                jumbledArray.push(nextTwoChars);
                                i += 1;
                                matched = true;
                            }
                        }
                    } else if (text.length - i == 2) {
                        // Check for 2-character matches
                        const nextTwoChars = text.substring(i, i + 2);
                        if (treatedAsOneChar.indexOf(nextTwoChars) >= 0) {
                            jumbledArray.push(nextTwoChars);
                            i += 1;
                            matched = true;
                        }
                    }
                    if (!matched) {
                        jumbledArray.push(text[i]);
                    }
                }
                return jumbledArray;
            }
        }

        jumbledArray = text.split("");

        return jumbledArray;
    }

    /**
     * Set the keyboard (option buttons) for the user to interact with.
     * @returns void
     */
    setKeyboard() {
        this.jumbledKeys = [];
        this.answer = [];
        this.activeInputIndex = 0;
        let text: string;

        // Extract prompt text from question object
        text =
            this.mainResponse === "l"
                ? OwoksapeUtils.stripHtml(this.exerciseService.question.lakota)
                : OwoksapeUtils.stripHtml(this.exerciseService.question.english);

        // Replace non standard characters with standard ones
        text = OwoksapeUtils.replaceNonStandardChars(text);

        // Create array of options for user to click on, including punctuation
        const fullArray = OwoksapeUtils.convertTextToCombineCharsArray(text);

        const orderedOptionsArray = [];
        const punctuationArray = [];

        // Create array of options for user to click on, excluding punctuation
        fullArray.forEach((char) => {
            if ([",", ".", "!", "?", " ", ":", ";"].indexOf(char) < 0) {
                orderedOptionsArray.push(char);
            }
        });

        // Shuffle the array of options
        const jumbledOptionsArray = this.shuffle(orderedOptionsArray);

        // Create array of objects with key and disabled properties
        jumbledOptionsArray.forEach((element: string) => {
            this.jumbledKeys.push({ key: element, disabled: false });
        });

        // Create array of punctuation for each word
        text.split(" ").forEach((word) => {
            // if word has punctuation, add it to punctuationArray, otherwise add empty string
            const punctuation = word.match(/[\.,!?:;]/);
            if (punctuation) {
                punctuationArray.push(punctuation[0]);
            } else {
                punctuationArray.push("");
            }
        });

        this.punctuationArray = punctuationArray;

        // Create array of words with punctuation removed. This is the array of correct answers
        // Split on spaces, periods, commas, exclamation points, question marks
        this.inputCountArray = text.split(/(?<!\s)[\s.!?,:;]+/).filter(Boolean);

        // Create array that holds the correctness of each word in the answer
        // so we can highlight the input fields green for correct, or red of incorrect.
        this.answerIsCorrect = new Array(this.inputCountArray.length).fill(AnswerType.NONE);
    }

    /**
     * Calculate the length of the longest blank in the inputCountArray.
     * This is used to set the width of the input fields so they are all the same size,
     * in order to prevent making certain answers obvious.
     * @returns the length of the longest blank in the inputCountArray
     */
    getMaxBlankLength(): number {
        let max = 0;
        this.inputCountArray.forEach((blank) => {
            if (blank && blank.length > max) {
                max = blank.length;
            }
        });

        return max;
    }

    getRandInt(min: number, max: number): number {
        return Math.floor(Math.random() * (max - min + 1) + min);
    }

    /**
     * Set the index of the input field that the user is currently editing.
     * @param index the index of the input field that the user is currently editing.
     */
    setActiveInput(index: number) {
        this.activeInputIndex = index;
    }

    /**
     * Handle when the user clicks on one of the jumbled letters.
     * @param key the key that was pressed amongst the jumbled keys shown to the user.
     */
    jumbledLetterPressed(key) {
        if (this.activeInputIndex < this.inputCountArray.length) {
            if (this.answer[this.activeInputIndex]) {
                this.answer[this.activeInputIndex] += key.key;
                if (
                    !!this.answer[this.activeInputIndex] &&
                    !!this.inputCountArray[this.activeInputIndex] &&
                    this.answer[this.activeInputIndex].length ==
                        this.inputCountArray[this.activeInputIndex].length &&
                    this.activeInputIndex < this.inputCountArray.length - 1
                ) {
                    this.activeInputIndex++;
                    jQuery("#input-" + this.activeInputIndex).focus();
                }
            } else {
                this.answer[this.activeInputIndex] = key.key;
            }
        }
        key.disabled = true;
    }

    /**
     * Handle when the user clicks on the backspace button or presses the backspace key.
     * This deletes the last character in the active input field,
     * taking into account the possibility of non-standard characters,
     * which may be more than one character long.
     * The deleted character is then re-enabled in the jumbled keys.
     * @returns void
     */
    backspace() {
        let deletedChar: string = "";
        if (
            this.activeInputIndex < this.inputCountArray.length && // input is valid
            !!this.answer[this.activeInputIndex] && // input is not null
            this.answer[this.activeInputIndex].length > 0 // input is not empty
        ) {
            const numCharsToDelete = OwoksapeUtils.numCharsInPotentialLigatureReversed(
                this.answer[this.activeInputIndex],
            );
            deletedChar = this.answer[this.activeInputIndex].substr(
                this.answer[this.activeInputIndex].length - numCharsToDelete,
            );
            this.answer[this.activeInputIndex] = this.answer[this.activeInputIndex].slice(
                0,
                this.answer[this.activeInputIndex].length - numCharsToDelete,
            );

            for (let i = 0; i < this.jumbledKeys.length; i++) {
                if (this.jumbledKeys[i].key == deletedChar) {
                    if (this.jumbledKeys[i].disabled) {
                        this.jumbledKeys[i].disabled = false;
                        break;
                    }
                }
            }
        }

        if (
            !this.answer[this.activeInputIndex] ||
            (this.answer[this.activeInputIndex].length == 0 && this.activeInputIndex > 0)
        ) {
            this.activeInputIndex = Math.max(this.activeInputIndex - 1, 0);
        }
    }

    /**
     * Submit the user's answer and check if it is correct.
     */
    submitAnswer() {
        this.answerSubmitted = true;

        this.setAnswerIsCorrect();

        this.checkAnswer();
    }

    /**
     * Set the correctness of each word in the answer.
     */
    setAnswerIsCorrect() {
        this.finalAnswer = AnswerType.CORRECT;
        for (let i = 0; i < this.inputCountArray.length; i++) {
            if (this.answer[i] === this.inputCountArray[i]) {
                this.answerIsCorrect[i] = AnswerType.CORRECT;
            } else {
                this.answerIsCorrect[i] = AnswerType.INCORRECT;
                this.finalAnswer = AnswerType.INCORRECT;
            }
        }
    }

    /**
     * Check the user's answer and handle the response.
     */
    checkAnswer() {
        switch (this.mainResponse) {
            case "l":
                if (this.finalAnswer == this.AnswerType.CORRECT) {
                    this.exerciseService.userAnswer = this.AnswerType.CORRECT;
                    if (this.exerciseService.responseTypes.indexOf("a") > -1) {
                        this.audioService.playPauseAudio(
                            this.exerciseService.question.FullAudioUrl,
                            "prompt",
                        );
                    }
                } else {
                    this.exerciseService.userAnswer = this.AnswerType.INCORRECT;
                }
                break;
            case "e":
                if (this.finalAnswer == this.AnswerType.CORRECT) {
                    this.exerciseService.userAnswer = this.AnswerType.CORRECT;
                    if (this.exerciseService.responseTypes.indexOf("a") > -1) {
                        this.audioService.playPauseAudio(
                            this.exerciseService.question.FullAudioUrl,
                            "prompt",
                        );
                    }
                } else {
                    this.exerciseService.userAnswer = this.AnswerType.INCORRECT;
                }
                break;
            default:
                break;
        }

        this.handleAnswer();
    }

    /**
     * Send the answer to the server.
     */
    handleAnswer() {
        const params = Object();
        params.level_id = parseInt(this.localStorage.getItem("LevelID")) || null;
        params.unit_id = parseInt(this.localStorage.getItem("unitID")) || null;
        params.exercise_id = this.exerciseService.exercise.id;
        params.card_id = this.exerciseService.question.id;
        params.activity_type = this.sessionType;
        params.user_id = this.exerciseService.user.id;
        params.answar_type =
            this.exerciseService.userAnswer == this.AnswerType.CORRECT ? "right" : "wrong";
        params.popup_status = true; // show regardless of answer
        if (this.sessionType !== "review") {
            params.exercise_option_id = this.exerciseService.question.exerciseOptions.id;
        }
        //        params.popup_status = this.exerciseService.userAnswer == this.AnswerType.INCORRECT; // show only for wrong answers
        params.experiencecard_ids = [this.exerciseService.question.id].join();

        if (this.sessionType == "review") {
            params.prompt_type = this.exerciseService.exercise.promotetype;
            params.response_type = this.exerciseService.exercise.responsetype;
            params.exercise_type = this.exerciseService.exercise.exercise_type;
        }

        this.specifiedService.answerGiven(params);
        if (this.exerciseService.userAnswer == this.AnswerType.INCORRECT) {
            this.exerciseService.question.wrongAnswer = true;
            this.specifiedService.wrongAnswerGiven(this.exerciseService.question);
        }

        if (this.exerciseService.userAnswer == this.AnswerType.INCORRECT) {
            const wrongArray = [];
            if (
                this.sessionType == "review" ||
                this.exerciseService.question.exerciseOptions.type == "card" ||
                this.exerciseService.question.exerciseOptions.type == "group"
            ) {
                wrongArray.push(this.exerciseService.question);
            }
            this.specifiedService.setWrongCards(wrongArray);
        }
        this.setKeyboardListeners(false);
    }
}
