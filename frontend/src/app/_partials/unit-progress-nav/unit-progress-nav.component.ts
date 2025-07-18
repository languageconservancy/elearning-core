import { Component, OnInit, ViewChild, ElementRef, Input, Output, EventEmitter } from "@angular/core";
import { environment } from "environments/environment";
import { Settings } from "app/_constants/app.constants";
import { Answer, Unit, Question, QuestionQuestion, Exercise } from "app/pages/learning/unit/unit.component";

export enum ActivityState {
    NotAttempted = "Not Attempted",
    Attemptable = "Attemptable",
    AttemptedIncorrectly = "Attempted Incorrectly",
    CompletedIncorrectly = "Completed Incorrectly",
    CompletedCorrectly = "Completed Correctly",
}

export type ProgressQuestion = {
    numAttempts: number;
    correct: boolean;
};

export type ProgressQuestions = {
    [key: number]: ProgressQuestion;
};

export type ProgressFrame = {
    done: boolean;
};

export type ProgressActivity = {
    id: number;
    type: string;
    state: ActivityState;
    exerciseQuestions: ProgressQuestions | undefined;
    lessonFrames: ProgressFrame[] | undefined;
    exerciseType: string | undefined;
    name: string;
};

export type ExerciseBlockInfo = {
    firstExerciseIndex: number;
    lastExerciseIndex: number;
};

export type ExerciseBlockMap = {
    [index: number]: ExerciseBlockInfo | undefined;
};

@Component({
    selector: "app-unit-progress-nav",
    templateUrl: "./unit-progress-nav.component.html",
    styleUrls: ["./unit-progress-nav.component.css"],
})
export class UnitProgressNavComponent implements OnInit {
    @Input() unit: Unit;
    @Input() currentActivityIdx: number;
    @Input() currentExerciseQuestionIdx: number;
    @Input() currentLessonFrameIdx: number;

    @Output() activityPressedEvent = new EventEmitter<{ type: string; idx: number }>();

    @ViewChild("progressNavContainerHoriz") progressNavContainerHorizEl: ElementRef;
    @ViewChild("progressNavContainerVert") progressNavContainerVertEl: ElementRef;

    activities: ProgressActivity[] = [];
    ActivityState = ActivityState;
    exerciseBlocks: ExerciseBlockMap = {};
    // Boolean for whether all activities are complete or were completely in a previous session.
    // This is separate from allActivitiesCompleted since it is only set once and does not change,
    // and determines whether review is available.
    unitCompleteOrPreviouslyCompleted: boolean = false;
    // Boolean for whether all activities are complete this session
    // since any incorrectly completed activities will be reset upon entering the
    // unit or repeating the unit.
    allActivitiesCompleted: boolean = false;
    debug: boolean = !environment.production;

    constructor() {}

    ngOnInit(): void {
        this.exerciseBlocks = {};
    }

    activityPressed(activityType: string, activityIdx: number) {
        // Validate the activity index
        if ((activityIdx < 0 && activityType !== "review") || activityIdx >= this.activities.length) {
            throw new Error("Invalid activity index " + activityIdx + " for activity type " + activityType);
        }

        // Validate the activity type
        if (["lesson", "exercise", "review"].indexOf(activityType) < 0) {
            throw new Error("Invalid activity type " + activityType);
        }

        // Don't allow pressing on the current activity to trigger an event
        if (["lesson", "exercise"].indexOf(activityType) >= 0 && activityIdx !== this.currentActivityIdx) {
            if (this.activities[this.currentActivityIdx].state === ActivityState.NotAttempted) {
                this.setActivityState(this.currentActivityIdx, ActivityState.Attemptable);
            }
        }
        // Emit to parent component
        this.activityPressedEvent.emit({ type: activityType, idx: activityIdx });
    }

    resetData() {
        this.activities = [];
        this.exerciseBlocks = {};
        this.allActivitiesCompleted = false;
    }

    /**
     * Set up the progress navigation object, including all the states
     * required to track the user's progress through the unit in the progress
     * navigation bar.
     */
    init() {
        this.unitCompleteOrPreviouslyCompleted = true;

        for (let i = 0; i < this.unit.activities.length; i++) {
            const progressActivity = {
                id: this.unit.activities[i].id,
                type: this.unit.activities[i].flowType,
                state: this.computeActivityState(this.unit.activities[i]),
                exerciseQuestions: {},
                lessonFrames: [],
                exerciseType: undefined,
                name:
                    this.unit.activities[i].flowType === "exercise"
                        ? `[${i}] ${this.unit.activities[i].exercise.name} (${this.unit.activities[i].exercise.id})`
                        : `[${i}] ${this.unit.activities[i].lesson.name} (${this.unit.activities[i].lesson.id})`,
            };
            if (
                progressActivity.state !== ActivityState.CompletedCorrectly &&
                progressActivity.state !== ActivityState.CompletedIncorrectly
            ) {
                this.unitCompleteOrPreviouslyCompleted = false;
            }
            this.activities.push(JSON.parse(JSON.stringify(progressActivity)));
        }
        this.initExerciseBlocks();
        if (this.debug) console.debug("Unit previously completed: " + this.unitCompleteOrPreviouslyCompleted);
    }

    initExerciseBlocks() {
        const currentBlock: ExerciseBlockInfo = { firstExerciseIndex: 0, lastExerciseIndex: 0 };
        for (let i = 0; i < this.activities.length; i++) {
            if (this.activities[i].type === "exercise") {
                currentBlock.firstExerciseIndex = i;
                for (let j = i; j >= 0; j--) {
                    if (this.activities[j].type !== "exercise") {
                        break;
                    }
                    currentBlock.firstExerciseIndex = j;
                }
                currentBlock.lastExerciseIndex = i;
                for (let j = i; j < this.activities.length; j++) {
                    if (this.activities[j].type !== "exercise") {
                        break;
                    }
                    currentBlock.lastExerciseIndex = j;
                }
                this.exerciseBlocks[i] = { ...currentBlock };
            }
        }
        if (this.debug) console.debug("Exercise blocks: ", this.exerciseBlocks);
    }

    removeQuestionsFromProgressActivity(activityIdx: number, exercise: Exercise) {
        // remove questions no longer in the exercise object
        for (const existingExerciseOptionIdStr in this.activities[activityIdx].exerciseQuestions) {
            const existingExerciseOptionId: number = parseInt(existingExerciseOptionIdStr);
            if (
                !exercise.questions.some((q: Question) => {
                    const exerciseOptionId: number = this.getExerciseOptionIdFromQuestion(q.question);
                    return exerciseOptionId == existingExerciseOptionId;
                })
            ) {
                delete this.activities[activityIdx].exerciseQuestions[existingExerciseOptionId];
            }
        }
    }

    /**
     *
     * @param activityIdx
     * @param exercise
     */
    addQuestionsToProgressActivity(activityIdx: number, exercise: Exercise) {
        // remove questions no longer in the exercise object
        this.removeQuestionsFromProgressActivity(activityIdx, exercise);
        // add new questions to progress activity if they don't exist
        for (const question of exercise.questions) {
            const exerciseOptionId: number = this.getExerciseOptionIdFromQuestion(question.question);
            if (exerciseOptionId !== undefined) {
                if (!this.activities[activityIdx].exerciseQuestions[exerciseOptionId]) {
                    this.activities[activityIdx].exerciseQuestions[exerciseOptionId] = {
                        numAttempts: 0,
                        correct: exercise.IsCompleted.status ? true : false,
                    };
                }
            }
        }
        if (exercise.IsCompleted.status) {
            this.setActivityState(activityIdx, ActivityState.CompletedCorrectly);
        }
        // set the exercise type for the activity
        this.activities[activityIdx].exerciseType = exercise.exercise_type;
        if (this.debug) {
            console.debug("Added questions to progress activity: ", this.activities[activityIdx].exerciseQuestions);
        }
    }

    numExercisesInBlock(): number {
        return this.getLastExerciseIndexInCurrentBlock() - this.getFirstExerciseIndexInCurrentBlock() + 1;
    }

    /**
     * Find where the user left off in the unit. If the unit is not completed,
     * Go through activities in order,
     * 1. if it's a lesson,
     *   1a. if it's not completed, return this index
     *   1b. if it's completed, move on to the next activity
     * 2. if it's an exercise,
     *   2a. if it's completed, move on to the next activity
     *   2b. if it's attempted incorrectly, store the index and move onto the next activity
     *     2bloop:
     *     2b1. if the next activity is a lesson, return the stored index
     *     2b2. if the next activity is an exercise
     *       2b2a. if it's not attempted, return this index
     *       2b2b. if it's attempted incorrectly, move on to the next activity, repeat 2bloop
     *       2b2c. if it's completed, repeat 2bloop
     *   2c. if it's not attempted, return this index
     * 3. If all activities are completed, return the first activity index
     * @returns number - Index of the first unattempted or incomplete activity in the unit
     */
    getFirstUnattemptedOrIncompleteActivityIdx(): number {
        if (this.allActivitiesCompleted) {
            return 0;
        }
        let indexOfFirstExerciseAttemptedIncorrectly = -1;
        for (let i = 0; i < this.activities.length; i++) {
            if (this.activities[i].type === "lesson") {
                if (this.activities[i].state !== ActivityState.CompletedCorrectly) {
                    if (indexOfFirstExerciseAttemptedIncorrectly >= 0) {
                        // if we have an exercise attempted incorrectly before this lesson, return it
                        return indexOfFirstExerciseAttemptedIncorrectly;
                    }
                    // if this lesson is not completed, return it
                    return i;
                }
            } else if (this.activities[i].type === "exercise") {
                if (this.activities[i].state === ActivityState.CompletedCorrectly) {
                    continue;
                }
                if (
                    this.activities[i].state === ActivityState.AttemptedIncorrectly
                    // || this.activities[i].state === ActivityState.CompletedIncorrectly
                ) {
                    if (indexOfFirstExerciseAttemptedIncorrectly < 0) {
                        // store the index of the first exercise attempted incorrectly
                        indexOfFirstExerciseAttemptedIncorrectly = i;
                    }
                } else if (this.activities[i].state === ActivityState.NotAttempted) {
                    // if this exercise is not attempted, return it
                    return i;
                }
            }
        }
        if (indexOfFirstExerciseAttemptedIncorrectly >= 0) {
            // if we have an exercise attempted incorrectly, return it
            return indexOfFirstExerciseAttemptedIncorrectly;
        }
        return 0;
    }

    markCurrentActivityAsAttemptable() {
        if (this.activities[this.currentActivityIdx].state === ActivityState.NotAttempted) {
            this.setActivityState(this.currentActivityIdx, ActivityState.Attemptable);
        }
    }

    /**
     * @param {boolean} answer - Response object from server save endpoint
     */
    updateQuestionState(answer: Answer) {
        if (this.currentActivityType() !== "exercise") {
            throw new Error("[updateQuestion] Invalid activity type: " + this.currentActivityType());
        }
        // Increment number of attempts for the question
        this.activities[this.currentActivityIdx].exerciseQuestions[answer.exercise_option_id].numAttempts++;
        // Set whether the question was answered correctly
        if (!this.allActivitiesCompleted) {
            this.activities[this.currentActivityIdx].exerciseQuestions[answer.exercise_option_id].correct =
                answer.answar_type === "right";
        }
    }

    numAttemptsForQuestion(activityIdx: number, questionIdx: number): number {
        // Get exercise option id from question
        const questionExerciseOptionId = this.getExerciseOptionIdFromQuestion(
            this.unit.activities[activityIdx].exercise.questions[questionIdx].question,
        );
        if (!questionExerciseOptionId) {
            console.error(
                "[numAttemptsForQuestion] Invalid question. Expected exercise_option_id: ",
                this.unit.activities[activityIdx].exercise.questions[questionIdx].question,
            );
            return 0;
        }
        return this.activities[activityIdx].exerciseQuestions[questionExerciseOptionId].numAttempts;
    }

    activityIsIncomplete(activityIdx: number): boolean {
        return (
            this.activities[activityIdx].state !== ActivityState.CompletedCorrectly &&
            this.activities[activityIdx].state !== ActivityState.CompletedIncorrectly
        );
    }

    activityIsComplete(activityIdx: number): boolean {
        return (
            this.activities[activityIdx].state === ActivityState.CompletedCorrectly ||
            this.activities[activityIdx].state === ActivityState.CompletedIncorrectly
        );
    }

    getMatchThePairQuestionIndexFromAnswer(answer: Answer): number {
        return this.unit.activities[this.currentActivityIdx].exercise.questions.findIndex((q: Question) => {
            const questionExerciseOptionId = this.getExerciseOptionIdFromQuestion(q.question);
            return questionExerciseOptionId == answer.exercise_option_id;
        });
    }

    getExerciseOptionIdFromQuestion(qQuestion: QuestionQuestion): number | undefined {
        if (!!qQuestion.exerciseOptions) {
            return qQuestion.exerciseOptions.id;
        } else if (!!qQuestion.exercise_option_id) {
            return qQuestion.exercise_option_id;
        }

        console.error(
            "[getExerciseOptionIdFromQuestion] Invalid question. Expected exercise_option_id or exerciseOptions.id: ",
            qQuestion,
        );
        return undefined;
    }

    updateLessonFrameState() {
        if (this.currentActivityType() !== "lesson") {
            throw new Error("[updateLessonFrame] Invalid activity type: " + this.currentActivityType());
        }
        // update current lesson frame as done
        // this.activities[this.currentActivityIdx]
        // .lessonFrames[this.currentLessonFrameIdx].done = true;
    }

    updateExerciseState() {
        if (this.allActivitiesCompleted) {
            this.updateUnitCompletion();
            return;
        }
        if (this.currentActivityType() !== "exercise") {
            throw new Error("[updateExercise] Invalid activity type: " + this.currentActivityType());
        }
        if (this.activities[this.currentActivityIdx].state === ActivityState.CompletedCorrectly) {
            // if exercise is already completed correctly, don't worry if they got it wrong this time
            return;
        }
        // update state for exercise just finished or left
        const exerciseQuestions = this.activities[this.currentActivityIdx].exerciseQuestions;
        const progressQuestionsArr: ProgressQuestion[] = Object.values(exerciseQuestions);
        if (progressQuestionsArr.every((q) => q.correct)) {
            this.setActivityState(this.currentActivityIdx, ActivityState.CompletedCorrectly);
        } else if (
            progressQuestionsArr.every(
                (q: ProgressQuestion) => q.numAttempts >= Settings.REQUIRED_NUM_INCORRECT_ATTEMPTS_TO_COMPLETE_QUESTION,
            )
        ) {
            this.setActivityState(this.currentActivityIdx, ActivityState.CompletedIncorrectly);
        } else if (progressQuestionsArr.some((q: ProgressQuestion) => q.numAttempts > 0)) {
            this.setActivityState(this.currentActivityIdx, ActivityState.AttemptedIncorrectly);
        } else if (
            !this.unit.activities[this.currentActivityIdx].exercise.IsCompleted.status &&
            this.unit.activities[this.currentActivityIdx].exercise.IsCompleted.attempted
        ) {
            this.setActivityState(this.currentActivityIdx, ActivityState.AttemptedIncorrectly);
        } else {
            this.setActivityState(this.currentActivityIdx, ActivityState.Attemptable);
        }
        this.updateUnitCompletion();
    }

    updateLessonState() {
        // Only run this function if current activity is a lesson
        if (this.currentActivityType() !== "lesson") {
            throw new Error("[updateLesson] Invalid activity type: " + this.currentActivityType());
        }
        // Mark lesson at complete if it isn't already
        if (this.currentActivity().state !== ActivityState.CompletedCorrectly) {
            this.setActivityState(this.currentActivityIdx, ActivityState.CompletedCorrectly);
        }
        this.updateUnitCompletion();
    }

    updateUnitCompletion() {
        if (this.allActivitiesCompleted && this.unitCompleteOrPreviouslyCompleted) {
            return;
        }

        this.allActivitiesCompleted = this.activities.every((activity) => {
            if (activity.type === "exercise") {
                return (
                    activity.state === ActivityState.CompletedCorrectly ||
                    activity.state === ActivityState.CompletedIncorrectly
                );
            } else if (activity.type === "lesson") {
                return activity.state == ActivityState.CompletedCorrectly;
            } else {
                throw new Error("[updateUnitCompletion] Invalid activity type: " + activity.type);
            }
        });

        if (!this.unitCompleteOrPreviouslyCompleted && this.allActivitiesCompleted) {
            this.unitCompleteOrPreviouslyCompleted = true;
        }
    }

    allActivitiesAttempted(): boolean {
        return this.activities.every((activity) => activity.state !== ActivityState.NotAttempted);
    }

    incompleteQuestionsExistInUnit(): boolean {
        return this.getFirstIncompleteExerciseAndQuestionIndexInUnit() !== undefined;
    }

    getFirstIncompleteExerciseAndQuestionIndexInBlock(): number | undefined {
        return this.getFirstIncompleteExerciseAndQuestionIndex(
            this.getFirstExerciseIndexInCurrentBlock(),
            this.getLastExerciseIndexInCurrentBlock(),
        );
    }

    getFirstIncompleteExerciseAndQuestionIndexInUnit(): number | undefined {
        return this.getFirstIncompleteExerciseAndQuestionIndex(0, this.activities.length - 1);
    }

    getFirstIncompleteExerciseAndQuestionIndex(
        startingActivityIdx: number,
        endingActivityIdx: number,
    ): number | undefined {
        for (let i = startingActivityIdx; i <= endingActivityIdx; i++) {
            if (this.activities[i].type !== "exercise") {
                // skip lessons
                continue;
            }

            if (this.exerciseIsComplete(i)) {
                // skip if exercise is already completed
                continue;
            }

            const exerciseQuestions = Object.values(this.activities[i].exerciseQuestions);
            if (exerciseQuestions.length === 0) {
                // exercise is not complete and has no questions tracking yet,
                // so it much be incomplete
                return i;
            }
            for (let j = 0; j < exerciseQuestions.length; j++) {
                const question = exerciseQuestions[j];
                if (
                    question.numAttempts < Settings.REQUIRED_NUM_INCORRECT_ATTEMPTS_TO_COMPLETE_QUESTION &&
                    !question.correct
                ) {
                    return i;
                }
            }
        }
        return undefined;
    }

    getNextIncompleteExerciseAndQuestionIndexInBlock(): number | undefined {
        // Go through each question in each exercise in the current block starting
        // with the current current activity and next question. If none are found,
        // start from the beginning of the block to ensure no previous questions
        // are missed.
        return (
            this.getNextIncompleteExerciseAndQuestionIndex(this.getLastExerciseIndexInCurrentBlock()) ??
            this.getFirstIncompleteExerciseAndQuestionIndexInBlock()
        );
    }

    getNextIncompleteExerciseAndQuestionIndexInUnit(): number | undefined {
        // Go through each question in each exercise in the unit starting with the
        // current current activity and next question. If none are found,
        // start from the beginning of the unit to ensure no previous questions
        // are missed.
        return (
            this.getNextIncompleteExerciseAndQuestionIndex(this.activities.length - 1) ??
            this.getFirstIncompleteExerciseAndQuestionIndexInUnit()
        );
    }

    getNextIncompleteExerciseAndQuestionIndex(endingActivityIdx: number): number | undefined {
        for (let i = this.currentActivityIdx; i <= endingActivityIdx; i++) {
            if (this.activities[i].type !== "exercise") {
                // skip if not an exercise
                continue;
            }

            if (i === this.currentActivityIdx && this.activities[i].exerciseType === "match-the-pair") {
                // skip if match-the-pair exercise, since we never advance questions with this type
                continue;
            }

            if (this.exerciseIsComplete(i)) {
                // skip if exercise is already completed
                continue;
            }

            // start from question 0 if we are in any subsequent activities
            let startingQuestionIdx = 0;
            if (i === this.currentActivityIdx) {
                // start from the next question if we are in the current activity
                startingQuestionIdx = this.currentExerciseQuestionIdx + 1;
            }

            const exerciseQuestions = Object.values(this.activities[i].exerciseQuestions);
            if (exerciseQuestions.length === 0) {
                // exercise is not complete and has no questions tracking yet,
                // so it much be incomplete
                return i;
            }
            for (let j = startingQuestionIdx; j < exerciseQuestions.length; j++) {
                const question = exerciseQuestions[j];
                if (
                    question.numAttempts < Settings.REQUIRED_NUM_INCORRECT_ATTEMPTS_TO_COMPLETE_QUESTION &&
                    !question.correct
                ) {
                    return i;
                }
            }
        }
        return undefined;
    }

    incompleteQuestionsExistInBlock(): boolean {
        return this.getFirstIncompleteExerciseAndQuestionIndexInBlock() !== undefined;
    }

    getFirstExerciseIndexInCurrentBlock(): number {
        return this.exerciseBlocks[this.currentActivityIdx].firstExerciseIndex;
    }

    getLastExerciseIndexInCurrentBlock(): number {
        return this.exerciseBlocks[this.currentActivityIdx].lastExerciseIndex;
    }

    exerciseIsComplete(idx: number): boolean {
        if (this.unit.activities[idx].exercise.IsCompleted?.status) {
            // skip if exercise is already completed
            return true;
        }

        if (
            this.activities[idx].state === ActivityState.CompletedCorrectly ||
            this.activities[idx].state === ActivityState.CompletedIncorrectly
        ) {
            // skip if exercise is already completed
            return true;
        }

        if (this.unit.activities[idx].complete) {
            // skip if exercise is already completed
            return true;
        }

        return false;
    }

    computeActivityState(activity: any): ActivityState {
        if (activity.complete) {
            return ActivityState.CompletedCorrectly;
        }

        if (activity.attempted) {
            return ActivityState.AttemptedIncorrectly;
        }

        return ActivityState.NotAttempted;
    }

    setActivityState(activityIdx: number, state: ActivityState) {
        this.activities[activityIdx].state = state;
    }

    markActivityAsAttempted(activityIdx: number) {
        this.setActivityState(activityIdx, ActivityState.AttemptedIncorrectly);
    }

    markActivityAsCompletedCorrectly(activityIdx: number) {
        this.setActivityState(activityIdx, ActivityState.CompletedCorrectly);
    }

    markActivityAsCompletedIncorrectly(activityIdx: number) {
        this.setActivityState(activityIdx, ActivityState.CompletedIncorrectly);
    }

    currentUnitExercise(): any {
        return this.unit?.activities[this.currentActivityIdx]?.exercise;
    }

    currentActivity(): ProgressActivity {
        return this.activities[this.currentActivityIdx];
    }

    /**
     * Lesson just depends on
     *   - current activity idx
     *   - activity attempted
     * Exercise depends on
     *   - current activity idx
     *   - activity attempted
     *   - activity correct
     *   - activity complete
     * Review depends on
     *   - unit.complete
     * @param activity
     * @param idx
     * @returns
     */
    getProgressIconUrl(activity: ProgressActivity, idx: number): string {
        const UrlPrefix = "./assets/images/";
        let fileName = "";
        if (activity.type == "lesson") {
            // lesson icons
            if (idx == this.currentActivityIdx) {
                fileName = "book-icon-active.png"; // dark blue book
            } else {
                fileName = "book-icon.png"; // blue book
            }
        } else if (activity.type == "exercise") {
            // exercise icons
            if (idx == this.currentActivityIdx) {
                if (
                    activity.state == ActivityState.CompletedIncorrectly ||
                    activity.state == ActivityState.AttemptedIncorrectly
                ) {
                    fileName = "question-icon-active-wrong.png"; // dark blue border, red question mark
                } else if (
                    activity.state == ActivityState.CompletedCorrectly ||
                    activity.state == ActivityState.NotAttempted ||
                    activity.state == ActivityState.Attemptable
                ) {
                    fileName = "question-icon-active.png"; // dark blue border, dark blue question mark
                } else {
                    throw new Error("[getProgressIconUrl] Invalid activity state");
                }
            } else {
                if (
                    activity.state == ActivityState.NotAttempted ||
                    activity.state == ActivityState.CompletedCorrectly ||
                    activity.state == ActivityState.Attemptable
                ) {
                    fileName = "question-icon.png"; // blue border, blue question mark
                } else if (activity.state == ActivityState.CompletedIncorrectly) {
                    fileName = "question-icon-wrong-complete.png"; // blue border with red question mark
                } else if (activity.state == ActivityState.AttemptedIncorrectly) {
                    fileName = "question-icon-wrong.png"; // red border, red question mark
                } else {
                    throw new Error("[getProgressIconUrl] Invalid activity state");
                }
            }
        } else {
            throw new Error("Invalid activity type: " + activity.type);
        }
        return UrlPrefix + fileName;
    }

    getProgressIconTooltip(activity: any): string {
        return activity.name ?? "";
    }

    currentActivityType() {
        return this.unit?.activities[this.currentActivityIdx]?.flowType;
    }

    delay(ms: number) {
        return new Promise((resolve) => setTimeout(resolve, ms));
    }

    getCurrentVerticalActivityElement(): HTMLElement {
        return document.querySelector('[data-current-vert-activity="true"]');
    }

    getCurrentHorizontalActivityElement(): HTMLElement {
        return document.querySelector('[data-current-horiz-activity="true"]');
    }

    async scrollToCurrentActivity() {
        let currentActivityEl: HTMLElement;
        do {
            await this.delay(100);
            currentActivityEl = this.getCurrentVerticalActivityElement();
        } while (!this.progressNavContainerVertEl?.nativeElement || !currentActivityEl);

        this.scrollChildToCenterVertically(this.progressNavContainerVertEl.nativeElement, currentActivityEl);

        do {
            await this.delay(100);
            currentActivityEl = this.getCurrentHorizontalActivityElement();
        } while (!this.progressNavContainerHorizEl?.nativeElement || !currentActivityEl);

        this.scrollChildToCenterHorizontally(this.progressNavContainerHorizEl.nativeElement, currentActivityEl);
    }

    scrollChildToCenterVertically(parent: HTMLElement, child: HTMLElement) {
        const parentRect = parent.getBoundingClientRect();
        const childRect = child.getBoundingClientRect();

        // Calculate the scroll position
        let targetScroll = child.offsetTop + childRect.height / 2 - parentRect.height / 2;
        targetScroll = Math.min(Math.max(0, targetScroll), parent.scrollHeight - parentRect.height);

        const increment = 10; //targetScroll / 90;

        // Start the animation
        this.animateScroll(parent, increment, parent.scrollTop, targetScroll, "vertical");
    }

    scrollChildToCenterHorizontally(parent: HTMLElement, child: HTMLElement) {
        const parentRect = parent.getBoundingClientRect();
        const childRect = child.getBoundingClientRect();

        // Calculate the scroll position
        let targetScroll = child.offsetLeft + childRect.width / 2 - parentRect.width / 2;
        targetScroll = Math.min(Math.max(0, targetScroll), parent.scrollWidth - parentRect.width);

        const increment = 10; //targetScroll / 90;

        // Start the animation
        this.animateScroll(parent, increment, parent.scrollLeft, targetScroll, "horizontal");
    }

    animateScroll(
        element: HTMLElement,
        increment: number,
        currentScroll: number,
        targetScroll: number,
        direction: string = "vertical",
    ) {
        currentScroll += increment * (targetScroll < currentScroll ? -1 : 1);

        if (direction === "vertical") {
            element.scrollTop = currentScroll;
        } else {
            element.scrollLeft = currentScroll;
        }

        if (Math.abs(targetScroll - currentScroll) > increment) {
            window.requestAnimationFrame(() =>
                this.animateScroll(element, increment, currentScroll, targetScroll, direction),
            );
            return;
        }
    }

    getNumUnitAttemptableActivities(): number {
        const attemptableActivities = this.activities.filter(
            (activity) => activity.state !== ActivityState.NotAttempted,
        );
        const addOne = this.activities[this.currentActivityIdx]?.state === ActivityState.NotAttempted;
        return Math.max(0, attemptableActivities.length - 1 + (addOne ? 1 : 0));
    }
}
