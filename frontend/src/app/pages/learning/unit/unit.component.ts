import { Component, OnInit, OnDestroy, AfterViewInit, ViewChild } from "@angular/core";
import { Subscription } from "rxjs";
import { Router } from "@angular/router";

import { Settings } from "app/_constants/app.constants";
import { CookieService } from "app/_services/cookie.service";
import { KeyboardService } from "app/shared/keyboard/keyboard.service";
import { LessonsService } from "app/_services/lessons.service";
import { Loader } from "app/_services/loader.service";
import { LocalStorageService } from "app/_services/local-storage.service";
import { ReviewService } from "app/_services/review.service";
import { UnitProgressNavComponent } from "app/_partials/unit-progress-nav/unit-progress-nav.component";
import { environment } from "environments/environment";
import { Throttle } from "app/_decorators/throttle.decorator";
import { AudioService } from "app/_services/audio.service";

/**
 * Queue of events to be processed in order.
 * This is used to ensure that events are processed one at a time,
 * and that the UI is not blocked while waiting for an event to be processed.
 * Every user event is important in order to save the user's progress and answers, and
 * therefore needs to be processed in order.
 */
class EventQueue {
    // Queue of events to be processed
    private queue: Array<() => Promise<void>> = [];
    // Flag to indicate if the queue is currently being processed
    private isProcessing = false;

    /**
     * Add an event to the queue. If the queue is not currently being processed,
     * start processing it.
     * @param event The event to be processed. This should be a function that returns a Promise.
     */
    enqueue(event: () => Promise<void>): void {
        this.queue.push(event);
        if (!this.isProcessing) {
            void this.processQueue();
        }
    }

    /**
     * Process the events in the queue one at a time.
     * This function will wait for each event to be processed before moving on to the next one.
     * returns {Promise<void>}
     */
    private async processQueue(): Promise<void> {
        this.isProcessing = true;

        while (this.queue.length > 0) {
            // Get the next event from the queue
            const currentEvent = this.queue.shift();
            if (currentEvent) {
                try {
                    // Process the event and wait for it to complete
                    await currentEvent();
                } catch (error) {
                    // Log any errors that occur during event processing, but continue
                    // processing the next event in the queue, so the user can continue using the app
                    // without interruption.
                    console.error("Error processing event: ", error);
                }
            }
        }

        this.isProcessing = false;
    }
}

// #region Types
export type Answer = {
    activity_type: string;
    answar_type: string;
    card_id: number;
    exercise_id: number;
    exercise_option_id: number;
    experiencecard_ids: string;
    level_id: number;
    matchnpair: boolean;
    path_id: number;
    popup_status: boolean;
    unit_id: number;
    user_id: number;
};

type SaveQuestionAnswerResponse = {
    activity_type: string;
    correctHtmlResponse: string;
    exercise_id: number;
    exercise_type: string;
    level_id: number;
    listening_score: number;
    path_score: number;
    reading_score: number;
    speaking_score: number;
    total: number;
    type: string;
    unit_id: number;
    user_id: number;
    user_unit_activity_id: number;
    writing_score: number;
};

type Lesson = {
    id: number;
    lessonframes: any[];
    name: string;
};

export type QuestionQuestion = {
    PromptType: string; // html, card
    id: number;
    // Card type fields
    english: string | undefined;
    exerciseOptions: ExerciseOptions | undefined;
    lakota: string | undefined;
    // Html type fields
    exercise_option_id: number | undefined;
    response_html: string | undefined;
    //...more fields that we aren't using
};

type ExerciseCustomOption = {
    response_html: string;
};

type ExerciseOptions = {
    id: number;
    exercise_custom_options: ExerciseCustomOption[];
    card_id: number;
    text_option: string | null;
    type: string;
};

export type QuestionResponse = {
    lakota: string;
    exerciseOptions: ExerciseOptions;
    response_html: string;
};

export type Question = {
    question: QuestionQuestion;
    response: QuestionResponse;
    //...more fields that we aren't using
};

export type ExerciseStatus = {
    status: boolean;
    attempted: boolean;
};

export type Exercise = {
    id: number;
    bonus: number;
    card_type: string;
    exercise_type: string;
    instruction: string;
    name: string;
    noofcard: number;
    promotetype: string;
    promteresponsetype: string;
    responsetype: string;
    questions: Question[];
    IsCompleted: ExerciseStatus;
    assets: any;
};

export type UnitActivity = {
    id: number;
    flowType: string;
    complete: boolean; // either attempted and correct, or completed required attempts
    attempted: boolean; // attempted but not finished trying to get it right
    correct: boolean; // attempted and got it right, or completed but never got it correct
    lesson?: Lesson;
    lesson_id?: number;
    exercise?: Exercise;
    exercise_id?: number;
};

export type Unit = {
    id: number | undefined;
    name: string;
    activities: UnitActivity[];
};

type Level = {
    id: number | undefined;
    name: string;
    isClassroom: boolean;
};

enum MachineState {
    Start = "Start",
    StartingUnitFromWhereUserLeftOff = "Starting Unit From Where User Left Off",
    StartingUnitOver = "Starting Unit Over",
    DoingExerciseBlockInRepeatMode = "Doing Exercise Block In Repeat Mode",
    DoingExerciseBlockInSequentialMode = "Doing Exercise Block In Sequential Mode",
    DoingLesson = "Doing Lesson",
    DoingIncompleteExercisesInUnit = "Doing Incomplete Exercises In Unit",
    PresentingUnitEndOptions = "Presenting Unit End Options",
}

// Machine events lowerCamelCase
enum MachineEvent {
    Entry = "entry",
    LessonEndPopupClosed = "lessonPopupClosed",
    ExerciseQuestionPopupClosed = "exerciseQuestionPopupClosed",
    ExerciseSetPopupClosed = "exerciseSetPopupClosed",
    UnitPopupClosed = "unitPopupClosed",
    NextLessonFrameBtnPressed = "nextLessonFrameBtnPressed",
    PreviousLessonFrameBtnPressed = "previousLessonFrameBtnPressed",
    NextLessonBtnPressed = "nextLessonBtnPressed",
    NextExerciseBtnPressed = "nextExerciseBtnPressed",
    ExerciseActivityPressed = "exerciseActivityPressed",
    LessonActivityPressed = "lessonActivityPressed",
    GoToReviewBtnPressed = "goToReviewBtnPressed",
    QuestionAnswered = "questionAnswered",
    AllExerciseQuestionsAttempted = "allExerciseQuestionsAttempted",
    ExerciseBlockEndReached = "exerciseBlockEndReached",
    UnitEndNextBtnPressed = "unitEndNextBtnPressed",
    AllQuestionsInBlockCompleted = "allQuestionsInBlockCompleted",
    Exit = "exit",
}

export declare type TransitionFunction = (data?: any) => void | Promise<void>;

export declare type Transition = {
    [key: string]: TransitionFunction;
};

export declare type State = {
    [key: string]: Transition;
};

/**
 * State machine interface
 * The transitions are defined in the machine object and represent the main portion
 * of the state machine, with each state having its own set of transitions.
 */
export declare type StateMachine = {
    // Current state of the machine
    state: string;
    // Transitions for each state
    transitions: State;

    /**
     *
     * @param {MachineEvent} actionName - The action to send to the machine.
     * @param {any} data - Optional data to pass to the action.
     */
    dispatch: (actionName: MachineEvent, data?: any) => void;

    /**
     * Transition to a new state.
     * @param {MachineState} nextState - The next state to transition to.
     * @param {any} data - Optional data to pass to the transition function.
     */
    transition: (nextState: MachineState, data?: any) => void;

    /**
     * Set the state of the machine.
     * @param {MachineState} state - The new state to transition to.
     * @returns A promise that resolves when the state has been set.
     */
    setState: (state: MachineState) => Promise<void>;

    /**
     * Process an event in the machine.
     * @param {MachineEvent} actionName - The name of the action to process
     * @param {any} data - Optional data to pass to the action.
     * @return A promise that resolves when the event has been processed.
     */
    processEvent: (actionName: MachineEvent, data?: any) => Promise<void>;
};

// #endregion

/**
 * For repeat mode,
 * 1. User goes through all questions in that lesson block
 * 2. If user gets any questions wrong within an exercise,
 * they repeat those questions after attempting all exercises in the block.
 * 3. During the repeat session, user only does the questions they got wrong.
 * If they get any wrong again, they continue through the block and then repeat
 * again until all are correct or attempted required number of times.
 */

@Component({
    selector: "app-unit-component",
    templateUrl: "./unit.component.html",
    styleUrls: ["./unit.component.css"],
})
export class UnitComponent implements OnInit, OnDestroy, AfterViewInit {
    // A Rxjs subscriptions for conveniently managing multiple subscriptions
    // and unsubscribing from them all at once.
    private subscriptions: Subscription = new Subscription();
    // A separate subscription for listening to keyboard events, since this
    // subscription is added and removed based on the state of the unit.
    keyboardSubmitSubscription?: Subscription;

    // Stores the current level data and does not get modified
    level: Level = { id: undefined, name: "", isClassroom: false };
    // Stores the current unit data and does not get modified except to update
    // the activity exercise object when an exercise is fetched, since fetching
    // exercises only gets incomplete questions, and reshuffles them.
    unit: Unit = { id: undefined, name: "", activities: [] };
    // Currently active unit activity (lesson or exercise)
    _currentActivityIdx: number = 0;
    // Current frame within the lesson if the current activity is a lesson
    _currentLessonFrameIdx?: number;
    // Current question within the exercise if the current activity is an exercise
    _currentExerciseQuestionIdx?: number;
    // Current exercise if the current activity is an exercise
    _currentExercise?: Exercise;
    // Whether or not the unit data has been fetched from the API
    unitFetched: boolean = false;
    // Whether the current exercise has been fetched from the API
    // Used to prevent display error messages before the exercise is fetched
    exerciseFetched: boolean = false;
    // For debug console logs
    debug = !environment.production;
    // For debug info in the UI
    lastEvent: string = "";
    // Unit progress nav
    @ViewChild(UnitProgressNavComponent) unitProgressNav!: UnitProgressNavComponent;

    eventQueue = new EventQueue();

    // User data
    user: any = undefined;

    // #region State Machine
    machine: StateMachine = {
        state: MachineState.Start,
        transitions: {
            [MachineState.Start]: {},
            [MachineState.StartingUnitFromWhereUserLeftOff]: {
                [MachineEvent.Entry]: async () => {
                    this.resetUnitData();
                    await this.initUnitData();
                    const startingActivityIdx = this.unitProgressNav.getFirstUnattemptedOrIncompleteActivityIdx();
                    this.currentActivityIdx = startingActivityIdx;
                    if (this.debug) console.debug("Starting from activity idx: ", startingActivityIdx);
                    this.unitProgressNav.markCurrentActivityAsAttemptable();
                    const startingState = this.getMachineStateBasedOnActivityType(this.currentActivityType());
                    this.machine.transition(startingState);
                },
                [MachineEvent.Exit]: () => {
                    this.startAllocatedDailyTimeCountdownTimer();
                },
            },
            [MachineState.StartingUnitOver]: {
                [MachineEvent.Entry]: async () => {
                    this.resetUnitData();
                    await this.initUnitData();
                    this.currentActivityIdx = 0;
                    const startingState = this.getMachineStateBasedOnActivityType(this.currentActivityType());
                    this.machine.transition(startingState);
                },
            },
            [MachineState.DoingLesson]: {
                [MachineEvent.Entry]: () => {
                    // Always start lessons from the first frame, since the user can
                    // use the review, and it's simpler than figuring out where they left
                    // off.
                    this.currentLessonFrameIdx = 0;
                    this.setLessonFrame();
                    this.displayLessonFrame();
                    this.setKeyboardListeners(true);
                },
                [MachineEvent.NextLessonFrameBtnPressed]: async () => {
                    if (this.atLastLessonFrame()) {
                        console.warn("Next button displayed and pressed when already at last lesson frame");
                        return;
                    }
                    await this.saveLessonFrameCompletionToDatabase();
                    this.advanceLessonFrameIdx();
                    this.setLessonFrame();
                    this.displayLessonFrame();
                },
                [MachineEvent.PreviousLessonFrameBtnPressed]: () => {
                    if (this.currentLessonFrameIdx == 0) {
                        console.warn("Previous button displayed and pressed when at first lesson frame");
                        return;
                    }
                    this.decrementLessonFrameIdx();
                    this.setLessonFrame();
                    this.displayLessonFrame();
                },
                [MachineEvent.NextLessonBtnPressed]: async () => {
                    if (!this.atLastLessonFrame()) {
                        return;
                    }
                    await this.handleSavingLessonFrameAndDisplayingPopup();
                },
                [MachineEvent.NextExerciseBtnPressed]: async () => {
                    await this.handleSavingLessonFrameAndDisplayingPopup();
                },
                [MachineEvent.ExerciseActivityPressed]: (idx: number) => {
                    this.unitProgressNav.updateLessonState();
                    this.currentActivityIdx = idx;
                    this.machine.transition(MachineState.DoingExerciseBlockInSequentialMode);
                },
                [MachineEvent.LessonActivityPressed]: (idx: number) => {
                    this.unitProgressNav.updateLessonState();
                    this.currentActivityIdx = idx;
                    this.machine.transition(MachineState.DoingLesson);
                },
                [MachineEvent.GoToReviewBtnPressed]: () => {
                    if (!this.unitProgressNav.unitCompleteOrPreviouslyCompleted) {
                        throw new Error("Review button displayed and pressed when unit is not complete");
                    }
                    this.unitProgressNav.updateLessonState();
                    this.machine.transition(MachineState.PresentingUnitEndOptions);
                },
                [MachineEvent.LessonEndPopupClosed]: () => {
                    this.unitProgressNav.updateLessonState();

                    const nextActivityType = this.nextActivityType();
                    if (nextActivityType === "lesson") {
                        // Do the next lesson in the unit
                        this.advanceToNextActivityIdx();
                        this.machine.transition(MachineState.DoingLesson);
                    } else if (nextActivityType === "exercise") {
                        // Do the next exercise in the unit
                        this.advanceToNextActivityIdx();
                        this.machine.transition(MachineState.DoingExerciseBlockInSequentialMode);
                    } else if (this.atLastActivityInUnit() && !this.unitProgressNav.allActivitiesCompleted) {
                        // All activities attempted. Finish any incomplete exercises
                        this.machine.transition(MachineState.DoingIncompleteExercisesInUnit);
                    } else {
                        // Unit completed. Show unit end options.
                        this.machine.transition(MachineState.PresentingUnitEndOptions);
                    }
                },
                [MachineEvent.UnitEndNextBtnPressed]: async () => {
                    await this.handleSavingLessonFrameAndDisplayingPopup();
                },
                [MachineEvent.Exit]: () => {
                    this.currentLessonFrameIdx = undefined;
                },
            },
            [MachineState.DoingExerciseBlockInSequentialMode]: {
                [MachineEvent.Entry]: async () => {
                    // Display the first question in the exercise
                    try {
                        await this.fetchExercise();
                        this.currentExerciseQuestionIdx = 0;
                        this.setExerciseAndQuestion();
                        this.displayExercise();
                    } catch (error) {
                        console.error("[DoingExerciseBlockInSequentialMode]: ", error);
                    }
                },
                [MachineEvent.QuestionAnswered]: async (answer: Answer) => {
                    this.unitProgressNav.updateQuestionState(answer);
                    const saveAnswerResult: SaveQuestionAnswerResponse =
                        await this.saveExerciseQuestionAnswerToDatabase(answer);
                    this.displayExerciseQuestionPopup(saveAnswerResult, answer);
                },
                // FIXME Match The Pair exercise getting repeated despite answering correctly
                [MachineEvent.ExerciseQuestionPopupClosed]: (options: { matchThePairCompleted?: boolean }) => {
                    if (this.activityIsMatchThePairAndIncomplete(options)) return;
                    if (this.displayNextQuestionInExercise(options)) return;
                    this.machine.dispatch(MachineEvent.AllExerciseQuestionsAttempted);
                },
                [MachineEvent.AllExerciseQuestionsAttempted]: () => {
                    if (this.advanceToNextExerciseIfPossible()) return;
                    this.machine.dispatch(MachineEvent.ExerciseBlockEndReached);
                },
                [MachineEvent.ExerciseBlockEndReached]: async () => {
                    if (this.unitProgressNav.incompleteQuestionsExistInBlock()) {
                        return this.machine.transition(MachineState.DoingExerciseBlockInRepeatMode);
                    }
                    // All questions in the block have been answered correctly or attempted the required number of times
                    const exerciseSetData = await this.saveExerciseSetCompletionToDatabase();

                    // Only show the exercise set popup if there is more than one exercise in the block
                    // otherwise, it's redundant to show the popup for a single exercise
                    if (this.unitProgressNav.numExercisesInBlock() > 1) {
                        this.displayExerciseSetPopup(exerciseSetData);
                    } else {
                        this.machine.dispatch(MachineEvent.ExerciseSetPopupClosed);
                    }
                },
                [MachineEvent.ExerciseSetPopupClosed]: () => {
                    if (this.advanceToNextLessonIfPossible()) return;
                    // At the end of the unit
                    if (this.unitProgressNav.incompleteQuestionsExistInUnit()) {
                        return this.machine.transition(MachineState.DoingIncompleteExercisesInUnit);
                    }
                    // Unit is complete
                    this.machine.transition(MachineState.PresentingUnitEndOptions);
                },
                [MachineEvent.ExerciseActivityPressed]: (idx: number) => {
                    this.currentActivityIdx = idx;
                    this.machine.transition(MachineState.DoingExerciseBlockInSequentialMode);
                },
                [MachineEvent.LessonActivityPressed]: (idx: number) => {
                    this.currentActivityIdx = idx;
                    this.machine.transition(MachineState.DoingLesson);
                },
                [MachineEvent.GoToReviewBtnPressed]: () => {
                    this.machine.transition(MachineState.PresentingUnitEndOptions);
                },
                [MachineEvent.Exit]: () => {
                    this.unitProgressNav.updateExerciseState();
                    this.clearCurrentExercise();
                },
            },
            [MachineState.DoingExerciseBlockInRepeatMode]: {
                [MachineEvent.Entry]: async () => {
                    const incompleteActivityIdx =
                        this.unitProgressNav.getFirstIncompleteExerciseAndQuestionIndexInBlock();
                    if (incompleteActivityIdx == undefined) {
                        throw new Error("Transitioned to repeat mode, but no incomplete activity found");
                    }
                    this.currentActivityIdx = incompleteActivityIdx;
                    // Set question index to zero since fetch an exercise only gets incomplete questions
                    this.currentExerciseQuestionIdx = 0;
                    await this.fetchExercise();
                    this.setExerciseAndQuestion();
                    this.displayExercise();
                },
                [MachineEvent.QuestionAnswered]: async (answer: Answer) => {
                    await this.machine.transitions[MachineState.DoingExerciseBlockInSequentialMode].questionAnswered(
                        answer,
                    );
                },
                [MachineEvent.ExerciseQuestionPopupClosed]: async (options: { matchThePairCompleted?: boolean }) => {
                    if (this.activityIsMatchThePairAndIncomplete(options)) return;

                    if (!this.unitProgressNav.incompleteQuestionsExistInBlock()) {
                        this.unitProgressNav.updateExerciseState();
                        this.machine.dispatch(MachineEvent.AllQuestionsInBlockCompleted);
                        return;
                    }

                    const incompleteActivityIdx =
                        this.unitProgressNav.getNextIncompleteExerciseAndQuestionIndexInBlock();
                    if (incompleteActivityIdx === undefined) {
                        throw new Error("Incomplete questions exist, but no incomplete activity found");
                    }
                    if (this.movingToDifferentActivity(incompleteActivityIdx)) {
                        this.unitProgressNav.updateExerciseState();
                    }
                    this.currentActivityIdx = incompleteActivityIdx;
                    // Set question index to zero since fetch an exercise only gets incomplete questions
                    this.currentExerciseQuestionIdx = 0;
                    await this.fetchExercise();
                    this.setExerciseAndQuestion();
                    this.displayExercise();
                },
                [MachineEvent.AllQuestionsInBlockCompleted]: async () => {
                    const exerciseSetData = await this.saveExerciseSetCompletionToDatabase();
                    if (this.unitProgressNav.numExercisesInBlock() > 1) {
                        this.displayExerciseSetPopup(exerciseSetData);
                    } else {
                        this.machine.dispatch(MachineEvent.ExerciseSetPopupClosed);
                    }
                },
                [MachineEvent.ExerciseSetPopupClosed]: () => {
                    this.currentActivityIdx = this.unitProgressNav.getLastExerciseIndexInCurrentBlock();
                    if (this.advanceToNextLessonIfPossible()) return;
                    // At the end of the unit
                    if (this.atLastActivityInUnit() && this.unitProgressNav.incompleteQuestionsExistInUnit()) {
                        return this.machine.transition(MachineState.DoingIncompleteExercisesInUnit);
                    }
                    // Unit is complete
                    this.machine.transition(MachineState.PresentingUnitEndOptions);
                },
                [MachineEvent.ExerciseActivityPressed]: (idx: number) => {
                    void this.machine.transitions[
                        MachineState.DoingExerciseBlockInSequentialMode
                    ].exerciseActivityPressed(idx);
                },
                [MachineEvent.LessonActivityPressed]: (idx: number) => {
                    void this.machine.transitions[
                        MachineState.DoingExerciseBlockInSequentialMode
                    ].lessonActivityPressed(idx);
                },
                [MachineEvent.GoToReviewBtnPressed]: () => {
                    this.unitProgressNav.updateExerciseState();
                    this.machine.transition(MachineState.PresentingUnitEndOptions);
                },
                [MachineEvent.Exit]: () => {
                    this.unitProgressNav.updateExerciseState();
                    this.clearCurrentExercise();
                },
            },
            [MachineState.DoingIncompleteExercisesInUnit]: {
                [MachineEvent.Entry]: async () => {
                    const incompleteActivityIdx =
                        this.unitProgressNav.getFirstIncompleteExerciseAndQuestionIndexInUnit();
                    if (incompleteActivityIdx === undefined) {
                        throw new Error(
                            "Transitioned to doing incomplete exercises in unit mode, but no incomplete activity found",
                        );
                    }
                    this.currentActivityIdx = incompleteActivityIdx;
                    // Set question index to zero since fetch an exercise only gets incomplete questions
                    this.currentExerciseQuestionIdx = 0;
                    await this.fetchExercise();
                    this.setExerciseAndQuestion();
                    this.displayExercise();
                },
                [MachineEvent.QuestionAnswered]: (answer: Answer) => {
                    void this.machine.transitions[MachineState.DoingExerciseBlockInSequentialMode].questionAnswered(
                        answer,
                    );
                },
                [MachineEvent.ExerciseQuestionPopupClosed]: async (options: { matchThePairCompleted?: boolean }) => {
                    if (this.activityIsMatchThePairAndIncomplete(options)) return;

                    if (!this.unitProgressNav.incompleteQuestionsExistInUnit()) {
                        // All questions in the unit have been answered correctly or attempted 3 times
                        this.machine.transition(MachineState.PresentingUnitEndOptions);
                        return;
                    }

                    const incompleteActivityIdx =
                        this.unitProgressNav.getNextIncompleteExerciseAndQuestionIndexInUnit();
                    if (incompleteActivityIdx === undefined) {
                        throw new Error("Incomplete questions exist, but no incomplete activity found");
                    }
                    if (this.movingToDifferentActivity(incompleteActivityIdx)) {
                        this.unitProgressNav.updateExerciseState();
                    }
                    this.currentActivityIdx = incompleteActivityIdx;
                    // Set question index to zero since fetch an exercise only gets incomplete questions
                    this.currentExerciseQuestionIdx = 0;
                    await this.fetchExercise();
                    this.setExerciseAndQuestion();
                    this.displayExercise();
                    return;
                },
                [MachineEvent.ExerciseActivityPressed]: (idx: number) => {
                    void this.machine.transitions[
                        MachineState.DoingExerciseBlockInSequentialMode
                    ].exerciseActivityPressed(idx);
                },
                [MachineEvent.LessonActivityPressed]: (idx: number) => {
                    void this.machine.transitions[
                        MachineState.DoingExerciseBlockInSequentialMode
                    ].lessonActivityPressed(idx);
                },
                [MachineEvent.Exit]: () => {
                    this.unitProgressNav.updateExerciseState();
                    this.clearCurrentExercise();
                },
            },
            [MachineState.PresentingUnitEndOptions]: {
                [MachineEvent.Entry]: async () => {
                    const saveResponse = await this.saveUnitCompletionToDatabase();
                    this.lessonService.stopTimer({ stopTimer: true });
                    this.setKeyboardListeners(false);
                    this.displayUnitEndOptions(saveResponse);
                },
                [MachineEvent.UnitPopupClosed]: (data: any) => {
                    if (data.route == "review") {
                        this.goToReview();
                    } else if (data.route == "lessons-and-exercises") {
                        this.machine.transition(MachineState.StartingUnitOver);
                    } else {
                        // Should never happen
                        throw new Error(`Invalid route: ${data.route}`);
                    }
                },
            },
        },
        dispatch: (actionName: MachineEvent, data?: any): void => {
            this.eventQueue.enqueue(() => this.machine.processEvent(actionName, data));
        },
        processEvent: async (actionName: MachineEvent, data?: any): Promise<void> => {
            const action = this.machine.transitions[this.machine.state][actionName];
            if (action) {
                if (this.debug)
                    console.debug("Dispatching action: ", actionName, `(${this.machine.state}) `, data ?? "");
                this.lastEvent = actionName;
                await action.call(this, data);
            } else {
                if (this.debug) {
                    // Can possibly happen if the user clicks a button before the UI updates
                    throw new Error(`Invalid action: ${actionName} in state: ${this.machine.state}`);
                }
                console.warn("Invalid action: ", actionName, " in state: ", this.machine.state);
            }
            return Promise.resolve(); // Ensure the function returns a Promise<void>
        },
        transition: (nextState: MachineState, data: any): void => {
            if (!!this.machine.transitions[this.machine.state].exit) {
                if (this.debug) console.debug(`Exiting ${this.machine.state}`);
                this.machine.dispatch(MachineEvent.Exit, data);
            }
            this.eventQueue.enqueue(() => this.machine.setState(nextState));
            this.machine.dispatch(MachineEvent.Entry, data);
        },
        setState: async (state: MachineState): Promise<void> => {
            if (this.debug) console.debug(`Transitioning to "${state}"`);
            this.machine.state = state;
            return Promise.resolve();
        },
    };
    // #endregion

    // State machine and it's data
    unitStateMachine: StateMachine = Object.create(this.machine);

    constructor(
        private cookieService: CookieService,
        private keyboardService: KeyboardService,
        private lessonService: LessonsService,
        private loader: Loader,
        private localStorage: LocalStorageService,
        private reviewService: ReviewService,
        private router: Router,
        private audioService: AudioService,
    ) {}

    ngOnInit(): void {
        this.processBreaadcrumbFromLocalStorage();
        this.subscribeToLevelIdAndName();
        this.subscribeToUnitIdAndName();
        this.subscribeToPopupClosedEvents();
        this.subscribeToMatchThePairExerciseCompletion();
        this.subscribeToWrongAnswers();
        this.subscribeToAnswers();
    }

    ngAfterViewInit() {
        setTimeout(() => {
            this.unitStateMachine.transition(MachineState.StartingUnitFromWhereUserLeftOff);
        }, 0);
    }

    ngOnDestroy() {
        this.subscriptions.unsubscribe();
        this.setKeyboardListeners(false);

        this.lessonService.answerGiven({});
        this.lessonService.setPopup({});
        this.lessonService.wrongAnswerGiven({});
        this.lessonService.startTimer({});
        this.lessonService.nextScreen(false);
    }

    async initUnitData() {
        try {
            await this.getUser();
            this.getUnitDataFromLocalStorage();
            await this.fetchUnitActivities();
            if (this.handleEmptyUnit()) {
                return;
            }
            if (this.debug) console.debug("Unit fetched: ", this.unit);
            this.unitFetched = true;
            this.unitProgressNav.init();
        } catch (error) {
            console.error("[initUnitData]: ", error);
        }
    }

    // #region Rxjs Subscriptions

    subscribeToLevelIdAndName() {
        // When setting current level for the current user (i.e. last one they were on)
        this.subscriptions.add(
            this.lessonService.currentLevelDetails.subscribe((level: any) => {
                if (Object.keys(level).length) {
                    this.level.id = level.id;
                    this.level.name = level.name;
                }
            }),
        );
    }

    subscribeToUnitIdAndName() {
        this.subscriptions.add(
            this.lessonService.currentUnitDetails.subscribe((unit: any) => {
                if (Object.keys(unit).length) {
                    this.unit.id = parseInt(unit.id);
                    this.unit.name = unit.name;
                    this.localStorage.setItem("unitName", this.unit.name);
                }
            }),
        );
    }

    subscribeToMatchThePairExerciseCompletion() {
        // Triggered when all match-the-pair pairs are finished and it is time to
        // go to the next exercise.
        this.subscriptions.add(
            this.lessonService.nextExe.subscribe((matchThePairFinished) => {
                if (!!matchThePairFinished) {
                    this.unitStateMachine.dispatch(MachineEvent.ExerciseQuestionPopupClosed, {
                        matchThePairCompleted: true,
                    });
                }
            }),
        );
    }

    subscribeToPopupClosedEvents() {
        // When popup is closed. Popup could be for end of a set of exercises,
        // for end of a unit and to go to review for that unit,
        // for end review session, with option to continue review or start learning
        this.subscriptions.add(
            this.lessonService.popup.subscribe((res: any) => {
                this.setKeyboardListeners(res?.popUpClosed);
                if (!res?.popUpClosed) {
                    return;
                }
                if (res.type === "lesson") {
                    this.unitStateMachine.dispatch(MachineEvent.LessonEndPopupClosed);
                } else if (res.type === "exercise question") {
                    this.unitStateMachine.dispatch(MachineEvent.ExerciseQuestionPopupClosed, res);
                } else if (res.type === "exercise set") {
                    this.unitStateMachine.dispatch(MachineEvent.ExerciseSetPopupClosed);
                } else if (res.type === "unit") {
                    this.unitStateMachine.dispatch(MachineEvent.UnitPopupClosed, res);
                } else {
                    console.warn("Unknown popup type: ", res.type);
                }
            }),
        );
    }

    subscribeToWrongAnswers() {
        this.subscriptions.add(
            this.lessonService.wrongAnswer.subscribe((res: any) => {
                if (Object.keys(res).length <= 0 || this.unit?.activities[this.currentActivityIdx]?.complete) {
                    return;
                }
                // User got the answer wrong. Update progress.
                // this.unitStateMachine.dispatch(MachineEvent.WrongAnswer, res);
            }),
        );
    }

    subscribeToAnswers() {
        this.subscriptions.add(
            this.lessonService.answer.subscribe((answer: Answer) => {
                if (Object.keys(answer).length <= 0) {
                    return;
                }
                this.unitStateMachine.dispatch(MachineEvent.QuestionAnswered, answer);
            }),
        );
    }

    @Throttle(Settings.RETURN_KEY_THROTTLE_DELAY_MS)
    subscribeToKeyboardSubmitOrCloseEvent() {
        // Previous and next buttons
        this.keyboardSubmitSubscription = this.keyboardService.submitOrCloseEvent.subscribe((event: KeyboardEvent) => {
            if (this.unit.activities[this.currentActivityIdx].flowType == "lesson") {
                if (this.showPreviousLessonFrameBtn() && event.shiftKey) {
                    this.goToPreviousLessonFrameBtnPressed();
                } else if (this.showNextLessonFrameBtn() && !event.shiftKey) {
                    this.goToNextLessonFrameBtnPressed();
                } else if (this.showNextLessonBtn()) {
                    this.goToNextLessonBtnPressed();
                } else if (this.showNextExerciseBtn()) {
                    this.goToNextExerciseBtnPressed();
                } else if (this.showUnitEndNextBtn()) {
                    this.unitEndNextBtnPressed();
                } else if (this.showStartReviewBtn()) {
                    this.startReviewBtnPressed();
                }
            }
        });
    }

    setKeyboardListeners(turnOn: boolean) {
        if (turnOn) {
            // Don't double subscribe
            if (!this.keyboardSubmitSubscription || this.keyboardSubmitSubscription.closed) {
                this.subscribeToKeyboardSubmitOrCloseEvent();
            }
        } else {
            if (!!this.keyboardSubmitSubscription) {
                this.keyboardSubmitSubscription.unsubscribe();
            }
        }
    }
    // #endregion

    // #region Rxjs Observable updates

    advanceLessonFrameIdx() {
        if (this.currentLessonFrameIdx === undefined) {
            // Should never happen
            throw new Error("currentLessonFrameIdx is undefined");
        }
        this.currentLessonFrameIdx = this.currentLessonFrameIdx + 1;
    }

    decrementLessonFrameIdx() {
        if (this.currentLessonFrameIdx === undefined) {
            // Should never happen
            throw new Error("currentLessonFrameIdx is undefined");
        }
        this.currentLessonFrameIdx = this.currentLessonFrameIdx - 1;
    }

    get currentLessonFrameIdx(): number {
        return this._currentLessonFrameIdx;
    }

    set currentLessonFrameIdx(idx: number) {
        this._currentLessonFrameIdx = idx;
    }

    get currentExercise(): Exercise {
        return this._currentExercise;
    }

    set currentExercise(exercise: Exercise) {
        this._currentExercise = exercise;
        if (!!exercise) {
            this.unit.activities[this.currentActivityIdx].exercise = exercise;
        }
    }

    clearCurrentExercise() {
        this.exerciseFetched = false;
        this.currentExercise = undefined;
    }

    /**
     * Fetches the exercise for the current activity and sets it in the currentExercise property.
     * This receives the exercise and any incomplete questions. If all questions are complete,
     * it will receive all questions.
     * IsComplete.status is true if all questions are complete, false if not.
     */
    async fetchExercise() {
        const params = {
            exercise_id: this.unit.activities[this.currentActivityIdx].exercise_id,
            unit_id: this.unit.id,
            level_id: this.level.id,
            user_id: this.user.id,
        };
        try {
            const res = await this.lessonService.getExercise(params);
            if (!res.data.status) {
                throw new Error(res.message);
            }
            if (this.debug) console.debug("Exercise fetched: ", res.data.results);
            this.currentExercise = res.data.results;
            if (!this.currentExercise) {
                // Should never happen. Means the API failed to return an exercise
                throw new Error("Exercise fetch returned null for activity " + this.currentActivityIdx);
            }
            this.unitProgressNav.addQuestionsToProgressActivity(this.currentActivityIdx, this.currentExercise);
        } catch (err) {
            console.error("[fetchUnitExercise] ", err);
        } finally {
            this.exerciseFetched = true;
        }
    }

    setExerciseAndQuestion() {
        if (!this.currentExercise) {
            // Should never happen
            throw new Error("No exercise found for current activity of idx " + this.currentActivityIdx);
        }
        if (!this.currentExerciseQuestion()) {
            // Should never happen
            throw new Error(
                "No question found for current exercise of idx " +
                    this.currentActivityIdx +
                    " and question idx " +
                    this.currentExerciseQuestionIdx,
            );
        }
        this.lessonService.setExercise(this.currentExercise);
        if (this.currentExercise.exercise_type != "match_the_pair") {
            this.lessonService.setQuestion(this.currentExerciseQuestion());
        }
    }

    setLessonFrame() {
        if (!this.currentLessonFrame()) {
            // Should never happen
            throw new Error(
                "No lesson frame found for current lesson of idx " +
                    this.currentActivityIdx +
                    " and lesson frame idx " +
                    this.currentLessonFrameIdx,
            );
        }
        this.lessonService.setLessonFrame(this.currentLessonFrame());
    }

    displayLessonFrame() {
        this.lessonService.setType("lesson");
    }

    displayExercise() {
        this.lessonService.setType("exercise");
    }

    displayExerciseQuestionPopup(saveAnswerResult: SaveQuestionAnswerResponse, answer: Answer) {
        const correctHtmlResponse = this.getHtmlResponse(this.currentExercise);
        if (!!correctHtmlResponse) {
            saveAnswerResult["correctHtmlResponse"] = correctHtmlResponse;
        }
        this.lessonService.setPopup({
            type: "exercise",
            status: saveAnswerResult.type == "right",
            review: !!answer.card_id != null || answer["correctHtmlResponse"] != "",
            data: saveAnswerResult,
        });
    }

    getExerciseIdsInBlock(): any {
        return this.unit.activities
            .filter((activity) => activity.flowType === "exercise")
            .map((activity) => activity.exercise_id);
    }

    async saveExerciseSetCompletionToDatabase() {
        const exerciseIdsInBlock = this.getExerciseIdsInBlock();
        const params = {
            user_id: this.user.id,
            activity_type: "exercise",
            exercise_id: exerciseIdsInBlock.join(),
            unit_id: this.unit.id,
            level_id: this.level.id,
        };
        try {
            const res: any = await this.lessonService.exerciseSetComplete(params);
            if (!res.data.status) {
                throw new Error(res.message);
            }
            return res.data.results;
        } catch (error) {
            console.error("[displayExerciseSetPopup]: ", error);
        }
    }

    displayExerciseSetPopup(exerciseSetData: any) {
        this.lessonService.setPopup({
            type: !!exerciseSetData.exercise_set ? "exerciseSetPerfect" : "exerciseSet",
            data: {
                cardSet: [],
                points: exerciseSetData,
            },
        });
    }

    displayLessonEndPopup(lessonPoints: any) {
        this.lessonService.setPopup({
            type: "lesson",
            data: { lesson: this.currentLesson() },
            points: lessonPoints,
        });
    }

    displayUnitEndOptions(unitSaveResponse: any = null) {
        this.lessonService.setPopup({
            type: "unit",
            popup_status: true,
            status: true,
            data: unitSaveResponse,
        });
    }

    showPopup() {
        this.lessonService.setPopup({
            type: "lesson",
            popup_status: true,
            status: true,
        });
    }

    processBreaadcrumbFromLocalStorage() {
        const breadcrumb = localStorage.getItem("breadcrumb");
        let params: any = [];
        if (!breadcrumb) {
            this.reviewService.setBreadcrumb([]);
        } else {
            params = JSON.parse(breadcrumb);
            if (params[3]?.URL === "/review") {
                params.splice(3, 1);
            }
            const isClassroom: string | null = localStorage.getItem("isClassroom");
            if (isClassroom) {
                this.level.isClassroom = parseInt(isClassroom) === 1;
            }
            this.reviewService.setBreadcrumb(params);
        }
    }

    // #endregion Rxjs Observable updates

    // #region State Machine Helpers
    async handleSavingLessonFrameAndDisplayingPopup() {
        const lessonPoints: any = await this.saveLessonFrameCompletionToDatabase();
        this.unitProgressNav.updateLessonFrameState();
        this.displayLessonEndPopup(lessonPoints);
    }

    activityIsMatchThePairAndIncomplete(options: { matchThePairCompleted?: boolean }) {
        if (this.currentExercise.exercise_type === "match-the-pair" && !options.matchThePairCompleted) {
            return true; // Early return if the match-the-pair exercise isn't completed
        }
        return false;
    }

    advanceToNextExerciseIfPossible() {
        if (this.nextActivityType() === "exercise") {
            this.advanceToNextActivityIdx();
            this.machine.transition(MachineState.DoingExerciseBlockInSequentialMode);
            return true;
        }
        return false;
    }

    advanceToNextLessonIfPossible() {
        if (this.nextActivityType() === "lesson") {
            this.advanceToNextActivityIdx();
            this.machine.transition(MachineState.DoingLesson);
            return true;
        }
        return false;
    }

    displayNextQuestionInExercise(options: { matchThePairCompleted?: boolean }) {
        if (options?.matchThePairCompleted) {
            return false;
        }
        if (this.questionsLeftInExercise()) {
            // Move on to the next question in the exercise set
            this.advanceToNextExerciseQuestion();
            this.setExerciseAndQuestion();
            this.displayExercise();
            return true;
        }
    }

    movingToDifferentActivity(activityIdx: number) {
        return activityIdx !== this.currentActivityIdx;
    }

    handleIfIncompleteExercisesInUnit() {
        if (this.unitProgressNav.incompleteQuestionsExistInUnit()) {
            this.machine.transition(MachineState.DoingIncompleteExercisesInUnit);
            return true;
        }
        return false;
    }

    // #endregion State Machine Helpers

    // #region API calls

    async fetchUnitActivities() {
        const params = {
            path_id: this.user.learningpath_id,
            level_id: this.level.id,
            unit_id: this.unit.id,
            user_id: this.user.id,
        };
        this.loader.setLoader(true);

        try {
            const res: any = await this.lessonService.getUnitDetails(params);
            if (!res.data.status) {
                throw new Error(res.message);
            }
            this.unit.activities = res.data.results;
        } catch (error) {
            console.error("[fetchUnitActivities] ", error);
        } finally {
            this.loader.setLoader(false);
        }
    }

    handleEmptyUnit(): boolean {
        if (this.unit.activities.length === 0) {
            this.unitFetched = true;
            return true;
        }
        return false;
    }

    async saveLessonFrameCompletionToDatabase(): Promise<any> {
        if (!this.currentLesson()) {
            // Should never happen
            throw new Error(
                "Trying to complete lesson, but current lesson undefined. activityIdx: " +
                    this.currentActivityIdx +
                    ", lessonFrameIdx: " +
                    this.currentLessonFrameIdx,
            );
        }
        if (!this.currentLessonFrame()) {
            // Should never happen
            throw new Error(
                "Trying to complete lesson, but current lesson frame undefined. activityIdx: " +
                    this.currentActivityIdx +
                    ", lessonFrameIdx: " +
                    this.currentLessonFrameIdx,
            );
        }

        this.resetIdleTimer();

        const params = {
            path_id: this.user.learningpath_id,
            level_id: this.level.id,
            unit_id: this.unit.id,
            lesson_id: this.currentLesson()?.id,
            activity_type: "lesson",
            user_id: this.user.id,
            lessonframe_id: this.currentLessonFrame().id,
        };
        // Add user activity to database
        try {
            const res = await this.lessonService.lessonComplete(params);
            if (!res.data.status) {
                throw new Error(res.message);
            }
            return res.data.results;
        } catch (err) {
            console.error("[saveLessonFrameCompletionToDatabase] ", err);
        }
    }

    getCurrentExerciseQuestionExerciseOptionsId(): number | undefined {
        if (this.currentExerciseQuestionIdx === undefined) {
            // Should never happen
            throw new Error("currentExerciseQuestionIdx is undefined");
        }
        return this.getExerciseOptionsIdFromQuestion(this.currentExerciseQuestion().question);
    }

    getExerciseOptionsIdFromQuestion(qQuestion: QuestionQuestion): number | undefined {
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

    async saveExerciseQuestionAnswerToDatabase(answer: Answer): Promise<any> {
        if (this.currentActivityIdx === undefined || this.currentExerciseQuestionIdx === undefined) {
            throw new Error("currentActivityIdx or currentExerciseQuestionIdx is undefined");
        }

        this.resetIdleTimer();

        const originalAnswerType = answer.answar_type;

        if (this.unitProgressNav.activityIsIncomplete(this.currentActivityIdx)) {
            if (
                this.unitProgressNav.numAttemptsForQuestion(this.currentActivityIdx, this.currentExerciseQuestionIdx) >=
                Settings.REQUIRED_NUM_INCORRECT_ATTEMPTS_TO_COMPLETE_QUESTION
            ) {
                answer.answar_type = "right";
            }
        }

        answer.path_id = this.user.learningpath_id;

        try {
            const res = await this.lessonService.lessonComplete(answer);
            if (!res.data.status) {
                throw new Error(res.message);
            }
            // reset answer type to original so popup is displayed correctly
            res.data.results.type = originalAnswerType;
            return res.data.results;
        } catch (err) {
            console.error("[saveAnswerToDatabase] ", err);
        }
    }

    async saveUnitCompletionToDatabase(): Promise<any> {
        this.loader.setLoader(true);
        const params = {
            is_classroom: this.level.isClassroom,
            unit_id: this.unit.id,
            level_id: this.level.id,
            user_id: this.user.id,
        };
        try {
            const res = await this.lessonService.unitComplete(params);
            if (!res.data.status) {
                throw new Error(res.message);
            }
            return res.data.results;
        } catch (err) {
            console.error("[saveUnitCompletionToDatabase] ", err);
            return null;
        } finally {
            this.loader.setLoader(false);
        }
    }
    // #endregion

    private getMachineStateBasedOnActivityType(activityType: string): MachineState {
        if (activityType === "lesson") {
            return MachineState.DoingLesson;
        } else if (activityType === "exercise") {
            return MachineState.DoingExerciseBlockInSequentialMode;
        } else {
            throw new Error(`Invalid activity type: ${activityType}`);
        }
    }

    @Throttle(Settings.RETURN_KEY_THROTTLE_DELAY_MS)
    goToPreviousLessonFrameBtnPressed() {
        this.unitStateMachine.dispatch(MachineEvent.PreviousLessonFrameBtnPressed);
    }

    @Throttle(Settings.RETURN_KEY_THROTTLE_DELAY_MS)
    goToNextLessonFrameBtnPressed() {
        this.unitStateMachine.dispatch(MachineEvent.NextLessonFrameBtnPressed);
    }

    @Throttle(Settings.RETURN_KEY_THROTTLE_DELAY_MS)
    goToNextLessonBtnPressed() {
        this.unitStateMachine.dispatch(MachineEvent.NextLessonBtnPressed);
    }

    @Throttle(Settings.RETURN_KEY_THROTTLE_DELAY_MS)
    goToNextExerciseBtnPressed() {
        this.unitStateMachine.dispatch(MachineEvent.NextExerciseBtnPressed);
    }

    @Throttle(Settings.RETURN_KEY_THROTTLE_DELAY_MS)
    startReviewBtnPressed() {
        this.unitStateMachine.dispatch(MachineEvent.GoToReviewBtnPressed);
    }

    @Throttle(Settings.RETURN_KEY_THROTTLE_DELAY_MS)
    unitEndNextBtnPressed() {
        this.unitStateMachine.dispatch(MachineEvent.UnitEndNextBtnPressed);
    }
    // #endregion State Machine Helpers

    // #region Getters

    currentLesson() {
        return this.unit.activities[this.currentActivityIdx]?.lesson;
    }

    currentLessonFrame() {
        if (this.currentLessonFrameIdx == null || this.currentLessonFrameIdx == undefined) {
            return null;
        }
        return this.unit.activities[this.currentActivityIdx]?.lesson?.lessonframes[this.currentLessonFrameIdx];
    }

    nextActivity() {
        return this.unit.activities[this.currentActivityIdx + 1];
    }

    nextActivityType() {
        return this.nextActivity()?.flowType;
    }

    currentExerciseQuestion(): Question | undefined {
        if (this.currentExerciseQuestionIdx === undefined) {
            throw new Error("currentExerciseQuestionIdx is undefined");
        }
        return this.currentExercise?.questions[this.currentExerciseQuestionIdx];
    }

    currentActivityType() {
        return this.unit?.activities[this.currentActivityIdx]?.flowType;
    }

    questionsLeftInExercise(): boolean {
        if (this.currentExerciseQuestionIdx === undefined) {
            throw new Error("currentExerciseQuestionIdx is undefined");
        }
        return this.currentExerciseQuestionIdx < this.currentExercise.questions.length - 1;
    }

    atLastLessonFrame(): boolean {
        if (this.currentLessonFrameIdx === undefined) {
            return false;
        }
        if (this.numLessonFrames() === undefined) {
            console.error("numLessonFrames is undefined");
            return false;
        }
        return this.currentLessonFrameIdx >= (this.numLessonFrames() ?? 0) - 1;
    }

    atLastActivityInUnit(): boolean {
        const numActivities = this.numActivities();
        if (numActivities === null) {
            return false;
        }
        return this.currentActivityIdx >= numActivities - 1;
    }

    getUnitDataFromLocalStorage() {
        this.unit.id = this.unit.id || parseInt(this.localStorage.getItem("unitID"));
        this.unit.name = this.unit.name || this.localStorage.getItem("unitName");
        this.level.id = this.level?.id || parseInt(this.localStorage.getItem("LevelID"));
    }

    unitFetchedAndHasContent(): boolean {
        return this.unitFetched && this.unit.activities.length > 0;
    }

    async getUser() {
        try {
            const value = await this.cookieService.get("AuthUser");
            if (value == "") {
                throw new Error(value);
            }
            this.user = JSON.parse(value);
            if (!this.localStorage.getItem("unitID")) {
                // If there is no unit ID in local storage,
                // then the user hasn't entered the unit correctly.
                await this.router.navigate(["start-learning"]);
            }
        } catch (error) {
            console.error("[getUser] ", error);
            await this.router.navigate([""]);
        }
    }

    // #endregion Getters

    // #region Num Getters

    numActivities(): number | null {
        if (!this.unit?.activities) {
            console.error("[numActivities] Unit activities not set");
            return null;
        }
        return this.unit.activities.length;
    }

    numLessonFrames(): number | null {
        const lesson = this.currentLesson();
        if (!lesson?.lessonframes) {
            return null;
        }
        return lesson.lessonframes.length;
    }

    // #endregion Num Getters

    // #region Setters
    get currentActivityIdx() {
        if (this._currentActivityIdx === undefined) {
            throw new Error("currentActivityIdx is undefined");
        }
        return this._currentActivityIdx;
    }

    set currentActivityIdx(idx: number) {
        this._currentActivityIdx = idx;
        void this.unitProgressNav.scrollToCurrentActivity();
    }

    get currentExerciseQuestionIdx(): number | undefined {
        return this._currentExerciseQuestionIdx;
    }

    set currentExerciseQuestionIdx(idx: number) {
        this._currentExerciseQuestionIdx = idx;
    }

    advanceToNextActivityIdx() {
        this.currentActivityIdx = this.currentActivityIdx + 1;
    }

    advanceToNextExerciseQuestion() {
        if (this.currentExerciseQuestionIdx === undefined) {
            throw new Error("currentExerciseQuestionIdx is undefined");
        }
        this.currentExerciseQuestionIdx = this.currentExerciseQuestionIdx + 1;
    }

    resetUnitData() {
        // Don't reset unit and level name and id, since we won't get those again in our subscriptions
        this.unit.activities = [];
        this.currentActivityIdx = 0;
        this.currentLessonFrameIdx = undefined;
        this.currentExerciseQuestionIdx = undefined;
        this.unitFetched = false;
        this.unitProgressNav.resetData();
    }
    // #endregion Setters

    // #region Progress Navigation
    activityPressed(activity: { type: string; idx: number }) {
        if (activity.type === "lesson") {
            this.audioService.pauseAndClearAudioSrc();
            this.unitStateMachine.dispatch(MachineEvent.LessonActivityPressed, activity.idx);
        } else if (activity.type === "exercise") {
            this.audioService.pauseAndClearAudioSrc();
            this.unitStateMachine.dispatch(MachineEvent.ExerciseActivityPressed, activity.idx);
        } else if (activity.type === "review") {
            this.audioService.pauseAndClearAudioSrc();
            this.unitStateMachine.dispatch(MachineEvent.GoToReviewBtnPressed);
        } else {
            throw new Error(`Invalid activity type: ${activity.type}`);
        }
    }
    // #endregion Progress Navigation

    private getHtmlResponse(exercise: Exercise) {
        if (this.currentExerciseQuestionIdx === undefined) {
            throw new Error("currentActivityQuestionIdx is undefined");
        }
        const answeredEx: Question = exercise.questions[this.currentExerciseQuestionIdx];
        let correctHtml: string | undefined = undefined;
        const promptResponse: Array<string> = exercise.promteresponsetype.split("-");

        if (!!answeredEx.response && !!answeredEx.response.lakota && answeredEx.response.lakota !== "") {
            return undefined;
        }

        if (!!answeredEx.response) {
            correctHtml =
                answeredEx.response.response_html ||
                answeredEx.response.exerciseOptions.exercise_custom_options[0].response_html;
        } else if (!!answeredEx.question && !!answeredEx.question.exerciseOptions) {
            const textOption = answeredEx.question.exerciseOptions.text_option?.trim().replace(/[\[\]]/g, "");
            const text =
                promptResponse[1] === "l" ? answeredEx.question.lakota?.trim() : answeredEx.question.english?.trim();

            if (textOption && text && textOption === text) {
                correctHtml = undefined;
            } else {
                correctHtml = textOption;
            }
        }

        if (!!correctHtml) {
            correctHtml = correctHtml.replace(/<[^>]*>/g, "");
        }

        return correctHtml;
    }

    // These function are separate for clarity, but the difference is handled
    // in the timer component.
    startAllocatedDailyTimeCountdownTimer() {
        this.resetIdleTimer();
    }

    resetIdleTimer() {
        this.lessonService.startTimer({
            path_id: this.user.learningpath_id,
            user_id: this.user.id,
        });
    }

    // #region Navigation to other pages
    goToReview() {
        const getbreadcrumb = localStorage.getItem("breadcrumb");
        let params: any = [];
        if (getbreadcrumb) {
            params = JSON.parse(getbreadcrumb);
            params[2] = {
                ID: this.unit.id,
                Name: this.unit.name,
                URL: "/lessons-and-exercises",
            };
            params[3] = {
                ID: this.unit.id,
                Name: "Review",
                URL: "/review",
            };
        }
        this.reviewService.setBreadcrumb(params);
        this.localStorage.setItem("unitID", this.unit.id);
        this.reviewService.setUnit({ unit_id: this.unit.id });
        void this.router.navigate(["review"]);
    }
    // #endregion Navigation to other pages

    // #region Btn visibility

    showPreviousLessonFrameBtn(): boolean {
        if (this.currentLessonFrameIdx === undefined) {
            return false;
        }
        return !!this.currentLesson() && !!this.currentLessonFrame() && this.currentLessonFrameIdx > 0;
    }

    showNextLessonFrameBtn(): boolean {
        return !!this.currentLesson() && !!this.currentLessonFrame() && !this.atLastLessonFrame();
    }

    showNextLessonBtn(): boolean {
        return !!this.currentLesson() && !!this.atLastLessonFrame() && this.nextActivityType() == "lesson";
    }

    showNextExerciseBtn(): boolean {
        return !!this.currentLesson() && this.atLastLessonFrame() && this.nextActivityType() == "exercise";
    }

    showStartReviewBtn(): boolean {
        return (
            !!this.currentLesson() &&
            this.atLastLessonFrame() &&
            this.atLastActivityInUnit() &&
            this.unitProgressNav.allActivitiesCompleted
        );
    }

    /**
     * We need this in case the user skips ahead without finishing all exercises
     * @returns
     */
    showUnitEndNextBtn(): boolean {
        return (
            !!this.currentLesson() &&
            this.atLastLessonFrame() &&
            this.atLastActivityInUnit() &&
            !this.unitProgressNav.allActivitiesCompleted &&
            !this.unitProgressNav.unitCompleteOrPreviouslyCompleted
        );
    }
    // #endregion Btn visibility
}
