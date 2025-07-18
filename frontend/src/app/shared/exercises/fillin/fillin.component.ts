import {
    Component,
    OnInit,
    OnDestroy,
    Input,
    AfterViewInit,
    ViewEncapsulation,
    ViewChild,
} from "@angular/core";
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
import { environment } from "environments/environment";
import { VirtualKeyboardComponent } from "app/_partials/virtual-keyboard/virtual-keyboard.component";
import { SnackbarService } from "app/_services/snackbar.service";
import { BaseService } from "app/_services/base.service";
import { RegexConsts } from "app/_constants/app.constants";
declare let jQuery: any;

interface Choice {
    optionName: string;
    position: number;
    used?: boolean;
}

interface UiText {
    type: "blank" | "text";
    value?: string;
    optionName?: string;
    position?: number;
}

interface Answer {
    optionName: string;
    position: number;
    userMcqChoice: Choice | null;
    userTypingInputId: string;
}

interface AnswerParams {
    level_id: number | null;
    unit_id: number | null;
    card_id: number | string | null;
    activity_type: string;
    user_id: number | string;
    answar_type: string;
    popup_status: boolean;
    experiencecard_ids: string;
    // Optional depending on session type
    exercise_id?: number | string;
    exercise_option_id?: number | string;
    prompt_type?: string;
    response_type?: string;
    exercise_type?: string;
}

interface FlatIndex {
    groupIndex: number;
    partIndex: number;
    flatIndex: number;
    inputId: string;
}

@Component({
    selector: "app-fillin",
    encapsulation: ViewEncapsulation.None,
    templateUrl: "./fillin.component.html",
    styleUrls: ["./fillin.component.scss"],
    providers: [ExerciseService],
})
/**
 * Fill-in-the-blanks exercise component
 * @param {string} sessionType - Type of session (exercise or review)
 * Let's assume a fill-in-the-blanks exercise has the following structure:
 * "[Šúŋka] kiŋ, [sá pa] s[h]e [Šúŋka]?"
 * The UI response text array is an array of arrays of UI text objects:
 * uiResponseTextArray: [
 *    [
 *        { type: "blank", optionName: "Šúŋka", position: 0 },
 *    ],
 *    [
 *        { type: "text", value: "kiŋ," },
 *    ],
 *    [
 *        { type: "blank", optionName: "sá pa", position: 1 },
 *    ],
 *    [
 *        { type: "text", value: "s" },
 *        { type: "blank", optionName: "h", position: 2 },
 *        { type: "text", value: "e" },
 *    ],
 *    [
 *        { type: "blank", optionName: "Šúŋka", position: 3 },
 *    ],
 * ];
 * answer: [
 *    [
 *        { optionName: "Šúŋka", position: 0, userMcqChoice: null, userTypingInputId: "input_0_0" },
 *    ],
 *    null,
 *    [
 *        { optionName: "sá pa", position: 1, userMcqChoice: null, userTypingInputId: "input_2_0" },
 *    ],
 *    null,
 *    [
 *        null,
 *        { optionName: "h", position: 2, userMcqChoice: null, userTypingInputId: "input_4_1" },
 *        null,
 *    ],
 *    [
 *        { optionName: "Šúŋka", position: 3, userMcqChoice: null, userTypingInputId: "input_5_0" },
 *    ]
 * ];
 * blankFlatIndexMap: [
 *    { groupIndex: 0, partIndex: 0, flatIndex: 0, inputId: "input_0_0" },
 *    { groupIndex: 2, partIndex: 0, flatIndex: 1, inputId: "input_2_0" },
 *    { groupIndex: 4, partIndex: 1, flatIndex: 2, inputId: "input_4_1" },
 *    { groupIndex: 5, partIndex: 0, flatIndex: 3, inputId: "input_5_0" },
 * ];
 * answer.userMcqChoice gets set to a Choice object when a multiple choice option is selected.
 * answer.userTypingInputId is the ID of the input element for typing exercises.
 */
export class FillinComponent implements OnInit, OnDestroy {
    // Global info
    @Input() sessionType: string;
    private specifiedService: LessonsService | ReviewService;
    public isMobileOrTablet: boolean = false;
    public fillInType: string = "";

    // For virtual keyboard
    @ViewChild("virtualKeyboard") virtualKeyboard: VirtualKeyboardComponent;
    public inputs: any = {};
    private maxLengths: any = {};
    public environment: any = environment;

    // Set up information for fill-in-the-blanks exercise
    public uiGroupedTextArray: UiText[][] = []; // Array of arrays of UI text objects
    public answer: (Answer | null)[][] = []; // Array of arrays of Answer objects, contains nulls
    public blankFlatIndexMap: FlatIndex[] = []; // Array of mappings from blank index to group and part indices
    public activeBlankIndex: number = 0; // Index of the active blank in the flat map
    public maxAnswerLength: number = 0; // Length of the longest answer
    private keyboardIsReady: boolean = false;

    // Manipulated by user
    public answerSubmitted: boolean = false;
    private activeInputEl = null;

    // Answer validation
    public answerIsCorrect: AnswerType[][] = [];
    public AnswerType = AnswerType;

    // Subscriptions
    private subscriptions: Subscription = new Subscription();

    // Physical keyboard items
    private keyboardSubscriptions: Subscription[] = [];
    public keyboardHighlightedMcqChoiceIndex: number = -1;

    constructor(
        private baseService: BaseService,
        private cookieService: CookieService,
        private lessonService: LessonsService,
        private reviewService: ReviewService,
        public exerciseService: ExerciseService,
        public audioService: AudioService,
        private localStorage: LocalStorageService,
        private keyboardService: KeyboardService,
        private deviceDetector: DeviceDetectorService,
        private snackbarService: SnackbarService,
    ) {
        this.getDeviceInfo();
        this.getAuthUser();
    }

    ngOnInit() {
        this.setService();
        this.subscribeToCurrentExercise();
        this.subscribeToCurrentQuestion();
        this.subscribeToPopupEvents();
    }

    keyboardReady() {
        this.keyboardIsReady = true;
        this.virtualKeyboard.keyboard.setOptions({
            maxLength: this.maxLengths,
        });
    }

    private getDeviceInfo() {
        this.isMobileOrTablet = this.deviceDetector.isMobile() || this.deviceDetector.isTablet();
    }

    private getAuthUser() {
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
                void this.baseService.logout();
            });
    }

    setService() {
        if (this.sessionType == "exercise") {
            this.specifiedService = this.lessonService;
        } else if (this.sessionType == "review") {
            this.specifiedService = this.reviewService;
        } else {
            throw new Error("Unhandled session type: " + this.sessionType);
        }
    }

    get activeGroupIndex() {
        if (this.activeBlankIndex < 0) {
            return 0;
        }
        return this.blankFlatIndexMap[this.activeBlankIndex].groupIndex;
    }

    get activePartIndex() {
        if (this.activeBlankIndex < 0) {
            return 0;
        }
        return this.blankFlatIndexMap[this.activeBlankIndex].partIndex;
    }

    // #region Subscriptions
    private subscribeToCurrentExercise() {
        // Exercise subscription
        this.subscriptions.add(
            this.specifiedService.currentExercise.subscribe((exercise) => {
                this.exerciseService.exercise = {};
                this.exerciseService.promptTypes = [];
                this.exerciseService.responseTypes = [];

                if (
                    Object.keys(exercise).length <= 0 ||
                    exercise.exercise_type != "fill_in_the_blanks"
                ) {
                    return;
                }

                this.turnOnKeyboardListeners();
                this.exerciseService.exercise = exercise;
                const promptResponse = exercise.promteresponsetype.split("-");
                if (this.sessionType == "review") {
                    this.fillInType = "typing";
                    this.exerciseService.promptTypes.push(promptResponse[0]);
                    this.exerciseService.responseTypes.push(promptResponse[1]);
                    this.initUi();
                }
            }),
        );
    }

    private subscribeToCurrentQuestion() {
        if (this.sessionType !== "exercise") {
            return;
        }

        this.subscriptions.add(
            this.lessonService.currentQuestion.subscribe((ques) => {
                this.exerciseService.question = {};
                this.fillInType = "";

                if (
                    Object.keys(ques).length <= 0 ||
                    this.exerciseService.exercise.exercise_type != "fill_in_the_blanks"
                ) {
                    return;
                }

                this.exerciseService.question = ques.question;
                if (!ques.question.exerciseOptions?.fill_in_the_blank_type) {
                    console.error("No fill-in-the-blank type specified in question");
                    throw new Error("No fill-in-the-blank type specified in question");
                }

                this.fillInType = ques.question.exerciseOptions?.fill_in_the_blank_type ?? "typing";
                this.exerciseService.choices = ques.choices;
                this.answerSubmitted = false;
                this.exerciseService.setPromptResponseTypes();
                this.autoPlayAudioPrompt();
                this.initUi();
            }),
        );
    }

    private subscribeToPopupEvents() {
        // Popup subscription
        this.subscriptions.add(
            this.specifiedService.popup.subscribe((res) => {
                if (res.popUpClosed) {
                    this.audioService.pauseAudio();
                }
            }),
        );
    }
    // #endregion

    // #region Initialization functions
    /**
     * Initialize choices for multiple choice options by changing the server
     * response to a format that is easier to work with, using the format of the
     * Choice interface.
     */
    private initializeChoices() {
        this.exerciseService.choices.forEach((choice: any) => {
            choice.used = false;
            choice.optionName = choice.option_name;
            delete choice.option_name;
        });
    }

    /**
     * Initialize the UI for the fill-in-the-blanks exercise.
     * Creates the main UI response text array, sets up the blank model,
     * and initializes the choices for multiple choice options.
     */
    private initUi() {
        this.keyboardHighlightedMcqChoiceIndex = 0;
        this.inputs = {};
        this.maxLengths = {};
        this.answer = [];
        this.answerIsCorrect = [];
        this.answerSubmitted = false;
        this.blankFlatIndexMap = [];
        this.activeBlankIndex = 0;

        this.initializeChoices();
        this.createUiResponseTextArray();
        this.setUpBlankModel();
    }

    /**
     * Parse text into UI text objects.
     * @param {string} text - Text to parse
     * @param {number} positionCounter - Position counter for blanks
     * @returns {UiText[]} - Array of UI text objects
     */
    private parseText(
        text: string,
        positionCounter: number,
    ): { parsedText: UiText[]; positionCounter: number } {
        if (!text) {
            throw new Error("Text is empty");
        }
        if (positionCounter < 0) {
            throw new Error("Position counter must be a positive integer");
        }

        const matches = text.match(RegexConsts.SPLIT_AT_BRACKETS) || [];

        const result: UiText[] = [];

        matches.forEach((match: string) => {
            if (match.startsWith("[") && match.endsWith("]")) {
                result.push({
                    type: "blank",
                    optionName: match.slice(1, -1),
                    position: positionCounter++,
                });
            } else {
                result.push({ type: "text", value: match });
            }
        });

        return { parsedText: result, positionCounter };
    }

    /**
     * Start with string with spaces and brackets. Spaces are allowed in bracketed
     * options.
     * Example: "I went [to the] st[o]re [yesterday]."
     * Split at spaces, except within brackets.
     */
    private createUiResponseTextArray() {
        let positionCounter = 0;
        this.uiGroupedTextArray = [];

        // Get raw text (e.g. "[Šúŋka] kiŋ [sá pa] s[h]e?")
        const textWithBrackets =
            this.sessionType == "exercise"
                ? this.exerciseService.question.exerciseOptions.text_option
                : this.exerciseService.exercise.question.question;

        // Split at spaces, excluding spaces inside brackets,
        // into array: ['[Šúŋka]', 'kiŋ', '[sá pa]', 's[h]e?']
        const wordsAndBracketsArray = textWithBrackets.split(
            new RegExp(RegexConsts.SPLIT_AT_SPACES_REGEX),
        );

        // Create UI response text array
        wordsAndBracketsArray.forEach((text: string) => {
            // Parse text into UI text objects
            const result = this.parseText(text, positionCounter);
            positionCounter = result.positionCounter;
            this.uiGroupedTextArray.push(result.parsedText);
        });
    }

    /**
     * Set up the blank model for the fill-in-the-blanks exercise.
     * Initialize the answer array with blank objects and set up the
     * input fields for typing exercises.
     */
    private setUpBlankModel() {
        let isFirstBlank = true;
        let blankCounter = 0;

        this.uiGroupedTextArray.forEach((group: UiText[], groupIndex: number) => {
            const answerGroup: (Answer | null)[] = [];
            const answerIsCorrectGroup: AnswerType[] = [];
            group.forEach((part: UiText, partIndex: number) => {
                if (part.type === "blank") {
                    const inputId = `input_${groupIndex}_${partIndex}`;
                    this.blankFlatIndexMap.push({
                        groupIndex,
                        partIndex,
                        flatIndex: blankCounter,
                        inputId,
                    });
                    answerGroup.push({
                        optionName: part.optionName,
                        position: part.position,
                        userTypingInputId: inputId,
                        userMcqChoice: null,
                    });

                    answerIsCorrectGroup.push(AnswerType.NONE);

                    this.maxAnswerLength = Math.max(this.maxAnswerLength, part.optionName.length);
                    this.inputs[inputId] = "";

                    if (isFirstBlank) {
                        this.activeBlankIndex = blankCounter;
                        isFirstBlank = false;
                    }
                    blankCounter++;
                } else {
                    // text
                    answerGroup.push(null);
                    answerIsCorrectGroup.push(AnswerType.NONE);
                }
            });

            this.answer.push(answerGroup);
            this.answerIsCorrect.push(answerIsCorrectGroup);
        });

        this.setInputsMaxLengths(this.maxAnswerLength);

        setTimeout(() => {
            this.setBlanksWidths();
        }, 200);
    }

    private setInputsMaxLengths(maxLength: number) {
        this.blankFlatIndexMap.forEach((blank: FlatIndex) => {
            const part = this.uiGroupedTextArray[blank.groupIndex][blank.partIndex];
            if (part.type === "blank") {
                this.maxLengths[blank.inputId] = maxLength;
            }
        });
    }

    /**
     * Set the widths of the blanks based on the size of the container.
     */
    private setBlanksWidths() {
        const containerWidth = jQuery(".input-field-area").outerWidth();
        let totalWidth = 0.0;

        // Set up size of blanks
        jQuery(".word-block").each(function () {
            const _thisWidth = jQuery(this).outerWidth();
            totalWidth += _thisWidth;
            if (totalWidth > containerWidth) {
                jQuery(this).prepend("<br>");
                totalWidth = 0.0;
            }
        });

        // If typing version, focus on the first blank
        if (this.fillInType === "typing") {
            jQuery(`#input_${this.activeGroupIndex}_${this.activePartIndex}`).focus();
        }
    }
    // #endregion

    /**
     * Play the audio prompt if it is an audio prompt.
     */
    private autoPlayAudioPrompt() {
        if (this.exerciseService.promptTypes.indexOf("a") > -1) {
            this.audioService.playPauseAudio(this.exerciseService.question.FullAudioUrl, "prompt");
        }
    }

    // #region User interaction
    /**
     * Sets the active input field to the one clicked on,
     * and shows the virtual keyboard.
     * @param {number} groupIndex - Index of the group in the UI response text array
     * @param {number} partIndex - Index of the part in the group in the UI response text array
     * @param {any} event - Event that caused this function to be called
     */
    typingResponseBlankClicked(groupIndex: number, partIndex: number, event: any) {
        if (event) {
            this.setActiveBlankIndex(groupIndex, partIndex);
            if (this.keyboardIsReady) {
                this.virtualKeyboard.onInputFocus(event);
                this.virtualKeyboard.show();
                this.activeInputEl = event.target;
            }
        }
    }

    isMcqBlankEmpty(groupIndex: number, partIndex: number): boolean {
        return !this.answer[groupIndex][partIndex]?.userMcqChoice;
    }

    /**
     * Sets the active input field to the one clicked on.
     * If the blank is already filled in, remove the answer.
     * @param {number} groupIndex - Index of the group in the UI response text array
     * @param {number} partIndex - Index of the part in the group in the UI response text array
     */
    mcqResponseBlankClicked(groupIndex: number, partIndex: number) {
        this.setActiveBlankIndex(groupIndex, partIndex);
        if (this.isMcqBlankEmpty(groupIndex, partIndex)) {
            // Nothing to do, since blank is already empty
            return;
        }
        // Find which choice is in this blank and remove it
        const choiceIndex = this.getUserMcqChoiceIdx(this.activeBlankIndex);
        if (choiceIndex > -1) {
            this.removeUserChoice(this.activeBlankIndex);
            this.setChoiceUsed(choiceIndex, false);
        }
    }

    /**
     * If the choice is used, remove it from whichever blank it is in.
     * If the choice is unused, add it to the active blank.
     * @param {Choice} choice - Choice object
     * @param {number} i - Index of the choice in the choices array
     */
    mcqChoiceClicked(choice: Choice, choiceIndex: number) {
        if (choice.used) {
            const blankIndex = this.removeMcqChoiceFromBlank(choice);
            if (blankIndex > -1) {
                this.activeBlankIndex = blankIndex;
            }
        } else {
            this.addMcqChoiceToBlank(choice);
            this.setNextActiveMcqInput();
        }

        this.setChoiceUsed(choiceIndex, !choice.used);
    }

    /**
     * Handles when the user submits their answer.
     * @param event - Event that caused this function to be called
     */
    submitButtonClicked(event: any = null) {
        if (this.answerSubmitted) {
            return;
        }
        this.answerSubmitted = true;
        this.virtualKeyboard.hide();

        if (event) {
            event.stopPropagation();
        }

        let result = AnswerType.CORRECT;

        this.blankFlatIndexMap.forEach((blank: FlatIndex) => {
            const ans = this.answer[blank.groupIndex][blank.partIndex];
            if (!ans) {
                return;
            }

            let actualAnswer = "";
            let userAnswer = "";

            if (this.fillInType == "typing") {
                actualAnswer = OwoksapeUtils.replaceNonStandardChars(ans.optionName);
                userAnswer = OwoksapeUtils.replaceNonStandardChars(
                    jQuery(`#${ans.userTypingInputId}`).val(),
                );
            } else {
                // mcq
                actualAnswer = ans.optionName;
                userAnswer = ans.userMcqChoice?.optionName || "";
            }

            if (actualAnswer == "") {
                throw new Error("Actual answer is empty");
            }

            // Set correct/incorrect for each blank so they color correctly
            if (actualAnswer != userAnswer) {
                this.answerIsCorrect[blank.groupIndex][blank.partIndex] = AnswerType.INCORRECT;
                result = AnswerType.INCORRECT;
            } else {
                this.answerIsCorrect[blank.groupIndex][blank.partIndex] = AnswerType.CORRECT;
            }
        });

        this.submitAnswer(result);
    }
    // #endregion

    /**
     * Sets the active blank index based on the group and part indices.
     * @param {number} groupIndex - Index of the group in the UI response text array
     * @param {number} partIndex - Index of the part in the group in the UI response text array
     */
    setActiveBlankIndex(groupIndex: number, partIndex: number) {
        const flatIndex = this.blankFlatIndexMap.findIndex(
            (item) => item.groupIndex === groupIndex && item.partIndex === partIndex,
        );

        if (flatIndex !== -1) {
            this.activeBlankIndex = flatIndex;
        } else {
            throw new Error("Flat index not found for group and part index");
        }
    }

    /**
     * Mark the choice as used or unused.
     * @param {number} choiceIndex - Index of the choice in the choices array
     * @param {boolean} used - Whether the choice is used or unused
     */
    setChoiceUsed(choiceIndex: number, used: boolean) {
        this.exerciseService.choices[choiceIndex].used = used;
    }

    /**
     * Get the index of the user's multiple choice choice for a blank.
     * @param {number} idx - Index of the blank in the UI response text array
     * @returns {number} - Index of the user's multiple choice choice
     */
    getUserMcqChoiceIdx(idx: number): number {
        const flatIndex = this.blankFlatIndexMap[idx];
        const userChoice = this.answer[flatIndex.groupIndex][flatIndex.partIndex].userMcqChoice;
        if (!userChoice) {
            console.error("User choice not found for blank that should have a choice. Idx:", idx);
            return -1;
        }

        return this.exerciseService.choices.findIndex((choice: Choice) => {
            return (
                choice.optionName == userChoice.optionName && choice.position == userChoice.position
            );
        });
    }

    /**
     * Remove the user's multiple choice choice for a blank.
     * @param {number} responseTextIdx - Index of the blank in the UI response text array
     */
    removeUserChoice(idx: number) {
        const flatIndex = this.blankFlatIndexMap[idx];
        this.answer[flatIndex.groupIndex][flatIndex.partIndex].userMcqChoice = null;
    }

    /**
     * Sets the choice to the active blank.
     * @param {Choice} choice - Choice object
     */
    addMcqChoiceToBlank(choice: Choice) {
        if (this.activeBlankIndex < 0) {
            this.snackbarService.showSnackbar({
                msg: "Please select a blank to fill in.",
                status: false,
            });
            return;
        }

        // Assign choice to the active blank
        this.answer[this.activeGroupIndex][this.activePartIndex].userMcqChoice = choice;
    }

    /**
     * Remove the choice from the active blank.
     * @param {Choice} choice - Choice object
     * @returns {number} - Index of the blank in the answer array
     */
    removeMcqChoiceFromBlank(choice: Choice): number {
        // Need to find which blank the choice is in
        let blankIndex: number = 0;
        this.blankFlatIndexMap.forEach((blank: FlatIndex) => {
            const ans = this.answer[blank.groupIndex][blank.partIndex];
            if (ans.userMcqChoice?.optionName == choice.optionName) {
                ans.userMcqChoice = null;
                blankIndex = blank.flatIndex;
                return;
            }
        });

        return blankIndex;
    }

    /**
     * Highlight the next empty blank.
     */
    setNextActiveMcqInput() {
        // If current blank is empty, stay on it
        if (!this.answer[this.activeGroupIndex][this.activePartIndex]?.userMcqChoice) {
            // If we're on a blank and it's empty, return
            return;
        }

        // Make sure the blanks aren't all full
        let blanksFull = true;
        this.blankFlatIndexMap.forEach((blank: FlatIndex) => {
            if (!this.answer[blank.groupIndex][blank.partIndex]?.userMcqChoice) {
                blanksFull = false;
            }
        });

        if (blanksFull) {
            return;
        }

        // While loop through blanks wrapping if necessary to find next empty blank
        let newActiveSet: boolean = false;
        let checkedAllBlanks: boolean = false;
        let index = this.activeBlankIndex++;
        let blank: FlatIndex;

        while (!newActiveSet && !checkedAllBlanks) {
            blank = this.blankFlatIndexMap[index];
            if (!this.answer[blank.groupIndex][blank.partIndex]?.userMcqChoice) {
                this.activeBlankIndex = blank.flatIndex;
                newActiveSet = true;
                break;
            }

            index = OwoksapeUtils.incrementWrap(index, 0, this.blankFlatIndexMap.length - 1);
            checkedAllBlanks = index === this.activeBlankIndex;
        }
    }

    /**
     * Submit the user's answer.
     * @param {AnswerType} result - Result of the user's answer
     */
    submitAnswer(result: AnswerType) {
        this.turnOffKeyboardListeners();
        setTimeout(() => {
            this.handleAnswer(result);
            this.virtualKeyboard.hide();
            // Release focus from active input so keyboard doesn't reappear on next typing exercise
            if (this.activeInputEl) {
                this.activeInputEl.blur();
            }
        }, 100);
    }

    /**
     * Check if all blanks are filled in with choices.
     * @returns {boolean} - Whether all blanks are filled in
     */
    areAllMcqBlanksFilledIn(): boolean {
        let allBlanksFilledIn: boolean = true;
        let breakOuterLoop: boolean = false;

        for (let i = 0; i < this.answer.length; ++i) {
            for (let j = 0; j < this.answer[i].length; ++j) {
                const ans = this.answer[i][j];
                if (!ans) {
                    continue;
                }

                const userAnswer = ans.userMcqChoice?.optionName || "";

                if (userAnswer === "") {
                    allBlanksFilledIn = false;
                    breakOuterLoop = true;
                    break;
                }
            }
            if (breakOuterLoop) {
                break;
            }
        }

        return allBlanksFilledIn;
    }

    /**
     * Plays the prompt audio if present, and submits the answer to the server.
     * @param {AnswerType} result - Result of the user's answer
     */
    handleAnswer(result: AnswerType) {
        this.exerciseService.userAnswer = result;

        if (
            result === this.AnswerType.CORRECT &&
            this.exerciseService.responseTypes.indexOf("a") > -1
        ) {
            this.audioService.playPauseAudio(this.exerciseService.question.FullAudioUrl, "prompt");
        }

        const params: AnswerParams = this.buildAnswerParams(result);
        this.specifiedService.answerGiven(params);

        if (result === this.AnswerType.INCORRECT) {
            this.exerciseService.question.wrongAnswer = true;
            this.specifiedService.wrongAnswerGiven(this.exerciseService.question);

            const { exercise, question } = this.exerciseService;
            const options = question.exerciseOptions;
            const cardType = exercise.card_type;

            const isCardOrGroup = options?.type === "card" || options?.type === "group";
            const isCustomCard = cardType === "custom";
            const isPromptCard = question.PromptType === "card";

            const questionIsACard =
                (isCardOrGroup && !isCustomCard) || (isCustomCard && isPromptCard);

            if (questionIsACard) {
                this.specifiedService.setWrongCards([question]);
            }
        }
    }

    /**
     * Build the parameters for the answer to be submitted to the server.
     * @param {AnswerType} result - Result of the user's answer
     * @returns {AnswerParams} - Parameters for the answer
     */
    private buildAnswerParams(result: AnswerType): AnswerParams {
        const question = this.exerciseService.question;
        const exercise = this.exerciseService.exercise;

        const isCustomType = this.exerciseService.exercise.card_type === "custom";
        const promptIsCard = this.exerciseService.question.PromptType === "card";

        return {
            level_id: parseInt(this.localStorage.getItem("LevelID") ?? "") || null,
            unit_id: parseInt(this.localStorage.getItem("unitID") ?? "") || null,
            card_id: !isCustomType || promptIsCard ? question.id : null,
            activity_type: this.sessionType,
            user_id: this.exerciseService.user.id,
            answar_type: result == this.AnswerType.CORRECT ? "right" : "wrong",
            popup_status: true,
            experiencecard_ids: [question.id].join(),
            ...(this.sessionType == "exercise"
                ? {
                      exercise_id: exercise.id,
                      exercise_option_id:
                          question.exerciseOptions?.id || question.exercise_option_id || null,
                  }
                : {
                      prompt_type: exercise.promotetype,
                      response_type: exercise.responsetype,
                      exercise_type: exercise.exercise_type,
                  }),
        };
    }

    /**
     * Callback for event emitted for child app-virtual-keyboard component
     * for when a key is pressed on the virtual keyboard.
     * @param {string} key - SimpleKeyboard string code for pressed key
     */
    public virtualKeyPressed(key: string): void {
        if (key === "{enter}") {
            this.submitButtonClicked();
        }
    }

    /**
     * Callback for event emitted for child app-virtual-keyboard component
     * for when a key is pressed on the physical keyboard.
     * @param {string} key - Key pressed on the physical keyboard
     */
    public physicalKeyPressed(key: string): void {
        if (key === "Enter") {
            this.submitButtonClicked();
        }
    }

    /**
     * Handle left and right arrows to navigate between blanks
     * @param {KeyboardEvent} event - Keyboard event
     */
    private handleTypingLogic(event: KeyboardEvent) {
        if (["ArrowLeft", "ArrowRight"].indexOf(event.code) === -1) {
            return;
        }

        let newActiveSet: boolean = false;
        let wrapped: boolean = false;
        let activeIdx = this.activeBlankIndex;
        const numIndices = this.blankFlatIndexMap.length;

        // Move to the next or previous blank, whether it's empty or not
        while (!newActiveSet && !wrapped) {
            if ("ArrowRight" == event.code) {
                activeIdx = OwoksapeUtils.incrementWrap(activeIdx, 0, numIndices - 1);
                const groupIndex = this.blankFlatIndexMap[activeIdx].groupIndex;
                const partIndex = this.blankFlatIndexMap[activeIdx].partIndex;
                const part: UiText = this.uiGroupedTextArray[groupIndex][partIndex];

                if (part.type === "blank") {
                    this.activeBlankIndex = activeIdx;
                    newActiveSet = true;
                    break;
                }
            } else {
                activeIdx = OwoksapeUtils.decrementWrap(activeIdx, 0, numIndices - 1);
                const groupIndex = this.blankFlatIndexMap[activeIdx].groupIndex;
                const partIndex = this.blankFlatIndexMap[activeIdx].partIndex;
                const part: UiText = this.uiGroupedTextArray[groupIndex][partIndex];

                if (part.type === "blank") {
                    this.activeBlankIndex = activeIdx;
                    newActiveSet = true;
                    break;
                }
            }

            if (activeIdx === this.activeBlankIndex) {
                wrapped = true;
            }
        }

        if (newActiveSet && this.fillInType === "typing") {
            const idx = this.blankFlatIndexMap[this.activeBlankIndex];
            const id = `#input_${idx.groupIndex}_${idx.partIndex}`;
            jQuery(id).focus();
        }
    }

    // #region Keyboard listeners
    /**
     * Add a subscription to main keyboard subscription set,
     * and increment the number of keyboard subscriptions.
     * @param {Subscription} sub - Subscription to add
     */
    addKeyboardSubscription(sub: Subscription) {
        this.keyboardSubscriptions.push(sub);
    }

    /**
     * Check if already listening to keyboard events.
     * @returns {boolean} - Whether already listening to keyboard events
     */
    alreadyListeningToKeyboardEvents(): boolean {
        return this.keyboardSubscriptions.length > 0;
    }

    /**
     * Turn off keyboard listeners.
     */
    turnOffKeyboardListeners() {
        this.keyboardSubscriptions.forEach((sub) => {
            sub.unsubscribe();
        });
        this.keyboardSubscriptions = [];
    }

    /**
     * Turn on keyboard listeners.
     */
    turnOnKeyboardListeners() {
        // If on mobile or tablet, don't set keyboard listeners
        if (this.isMobileOrTablet) {
            return;
        }

        if (this.alreadyListeningToKeyboardEvents()) {
            return;
        }

        // Toggle Selection
        this.addKeyboardSubscription(
            this.keyboardService.toggleSelectionEvent.subscribe((event: any) => {
                if (!this.exerciseService.question.exerciseOptions) {
                    return false;
                }
                if (this.fillInType === "mcq") {
                    // Toggle selection among options. If blanks are filled in,
                    // then include Submit button as an option
                    const max = this.areAllMcqBlanksFilledIn()
                        ? this.exerciseService.choices.length
                        : this.exerciseService.choices.length - 1;
                    if (event.shiftKey) {
                        this.highlightNextUnusedChoice(max);
                    } else {
                        this.highlightPreviousUnusedChoice(max);
                    }
                } else {
                    // Custom HTML historically didn't have fill_in_type set,
                    // but only typing is possible.
                    this.focusNextTypingInput(event);
                }
            }),
        );

        // Toggle media
        this.addKeyboardSubscription(
            this.keyboardService.toggleMediaEvent.subscribe(() => {
                if (this.exerciseService.promptTypes.indexOf("a") > -1) {
                    this.audioService.playPauseAudio(
                        this.exerciseService.question.FullAudioUrl,
                        "prompt",
                    );
                }
            }),
        );

        // Submit
        this.addKeyboardSubscription(
            this.keyboardService.submitOrCloseEvent.subscribe(() => {
                const choiceIndex = this.keyboardHighlightedMcqChoiceIndex;
                const choices = this.exerciseService.choices;
                // Sumbit answer
                if (this.fillInType == "mcq") {
                    if (!this.answerSubmitted && choiceIndex == choices.length) {
                        // Submit button is highlighted
                        this.submitButtonClicked();
                        // Add or remove an option
                    } else if (choiceIndex > -1) {
                        this.mcqChoiceClicked(choices[choiceIndex], choiceIndex);
                    }
                } else {
                    this.submitButtonClicked();
                }
            }),
        );

        // Typing
        this.addKeyboardSubscription(
            this.keyboardService.typingEvent.subscribe((event) => {
                this.handleTypingLogic(event);
            }),
        );
    }

    /**
     * Highlight the next unused choice.
     * @param {number} max - Maximum index of the choices array
     */
    highlightNextUnusedChoice(max: number) {
        let newChoiceIndex = this.keyboardHighlightedMcqChoiceIndex;
        do {
            newChoiceIndex = OwoksapeUtils.decrementWrap(newChoiceIndex, -1, max);
        } while (this.exerciseService.choices[newChoiceIndex]?.disabled);
        this.keyboardHighlightedMcqChoiceIndex = newChoiceIndex;
    }

    /**
     * Highlight the previous unused choice.
     * @param {number} max - Maximum index of the choices array
     */
    highlightPreviousUnusedChoice(max: number) {
        let newChoiceIndex = this.keyboardHighlightedMcqChoiceIndex;
        do {
            newChoiceIndex = OwoksapeUtils.incrementWrap(newChoiceIndex, -1, max);
        } while (this.exerciseService.choices[newChoiceIndex]?.disabled);
        this.keyboardHighlightedMcqChoiceIndex = newChoiceIndex;
    }

    /**
     * Focus on the next input field.
     * @param {KeyboardEvent} event - Keyboard event
     */
    focusNextTypingInput(event: KeyboardEvent) {
        let activeIndex = this.activeBlankIndex;
        const numIndices = this.blankFlatIndexMap.length;

        activeIndex = event.shiftKey
            ? OwoksapeUtils.decrementWrap(activeIndex, 0, numIndices - 1)
            : OwoksapeUtils.incrementWrap(activeIndex, 0, numIndices - 1);

        const idx = this.blankFlatIndexMap[activeIndex];
        const idOfBlank = jQuery(`#input_${idx.groupIndex}_${idx.partIndex}`);
        const input = jQuery(idOfBlank);
        if (input.length == 0) {
            console.error("Input not found: ", idOfBlank);
            return;
        }
        input.focus();
        this.activeBlankIndex = activeIndex;
    }
    // #endregion

    /**
     * Unsubscribe from all subscriptions and turn off keyboard listeners.
     */
    ngOnDestroy() {
        this.turnOffKeyboardListeners();
        this.subscriptions.unsubscribe();
    }
}
