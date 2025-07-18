import {
    UnitProgressNavComponent,
    ActivityState,
    ProgressQuestion,
    ProgressFrame,
    ProgressActivity,
} from "./unit-progress-nav.component";
// import { mount } from 'cypress/angular'

const createLessonActivity = (numFrames: number): ProgressActivity => {
    return {
        id: 0,
        type: "lesson",
        state: ActivityState.NotAttempted,
        exerciseQuestions: undefined,
        lessonFrames: Array.from({ length: numFrames }, () => ({ done: false })) as ProgressFrame[],
        exerciseType: undefined,
        name: "Lesson " + 0,
    };
};

const createExerciseActivity = (exerciseType: string, numQuestions: number): ProgressActivity => {
    return {
        id: 0,
        type: "exercise",
        state: ActivityState.NotAttempted,
        exerciseQuestions: Array.from({ length: numQuestions }, () => ({
            numAttempts: 0,
            correct: false,
        })) as ProgressQuestion[],
        lessonFrames: undefined,
        exerciseType: exerciseType,
        name: "Exercise " + 0,
    };
};

describe("UnitProgressNavComponent", () => {
    context("A unit of only exercises", () => {
        const newUnitActivities = [
            createExerciseActivity("mcq", 5),
            createExerciseActivity("fill-in-the-blanks-typing", 1),
            createExerciseActivity("match-pair", 4),
        ];
        const component = new UnitProgressNavComponent();
        component.activities = newUnitActivities;
        component.ngOnInit();
        component.initExerciseBlocks();
        it("Should have correct activities array", () => {
            expect(component.activities).to.have.length(3);
            expect(component.activities[0].exerciseQuestions).to.have.length(5);
            expect(component.activities[1].exerciseQuestions).to.have.length(1);
            expect(component.activities[2].exerciseQuestions).to.have.length(4);
        });
        it("Should have correct exercise blocks", () => {
            expect(Object.keys(component.exerciseBlocks)).to.have.length(3);
            expect(component.exerciseBlocks[0].firstExerciseIndex).to.equal(0);
            expect(component.exerciseBlocks[0].lastExerciseIndex).to.equal(2);
            expect(component.exerciseBlocks[1].firstExerciseIndex).to.equal(0);
            expect(component.exerciseBlocks[1].lastExerciseIndex).to.equal(2);
            expect(component.exerciseBlocks[2].firstExerciseIndex).to.equal(0);
            expect(component.exerciseBlocks[2].lastExerciseIndex).to.equal(2);
        });
        context("With only one exercise block", () => {
            it("Should have the correct first unattempted or incomplete activity index", () => {
                expect(component.getFirstUnattemptedOrIncompleteActivityIdx()).to.equal(0);
                component.activities[0].state = ActivityState.CompletedCorrectly;
                expect(component.getFirstUnattemptedOrIncompleteActivityIdx()).to.equal(1);
                component.activities[0].state = ActivityState.AttemptedIncorrectly;
                expect(component.getFirstUnattemptedOrIncompleteActivityIdx()).to.equal(1);
                component.activities[0].state = ActivityState.CompletedIncorrectly;
                expect(component.getFirstUnattemptedOrIncompleteActivityIdx()).to.equal(1);
                component.activities[0].state = ActivityState.Attemptable;
                expect(component.getFirstUnattemptedOrIncompleteActivityIdx()).to.equal(1);
            });
            it("Should find the correct next exercise and question index in the block", () => {
                component.currentActivityIdx = 0;
                // Set exercise 0 to completed correctly
                component.activities[0].state = ActivityState.CompletedCorrectly;
                component.activities[0].exerciseQuestions.fill({ numAttempts: 1, correct: true });
                console.log("Activities: ", component.activities);
                console.log("current activity idx: ", component.currentActivityIdx);
                expect(component.getNextIncompleteExerciseAndQuestionIndexInBlock()).to.deep.equal({
                    activityIdx: 1,
                    questionIdx: 0,
                });
                component.activities[1].state = ActivityState.AttemptedIncorrectly;
                component.activities[1].exerciseQuestions[0] = { numAttempts: 1, correct: true };
                expect(component.getNextIncompleteExerciseAndQuestionIndexInBlock()).to.deep.equal({
                    activityIdx: 2,
                    questionIdx: 0,
                });
                component.activities[2].state = ActivityState.AttemptedIncorrectly;
                component.activities[2].exerciseQuestions[0] = { numAttempts: 1, correct: true };
                expect(component.getNextIncompleteExerciseAndQuestionIndexInBlock()).to.deep.equal({
                    activityIdx: 2,
                    questionIdx: 1,
                });
                component.activities[2].exerciseQuestions.fill({ numAttempts: 1, correct: true });
                expect(component.getNextIncompleteExerciseAndQuestionIndexInBlock()).to.equal(undefined);
                component.activities[0].exerciseQuestions[2] = { numAttempts: 1, correct: false };
                component.activities[0].state = ActivityState.AttemptedIncorrectly;
                expect(component.getNextIncompleteExerciseAndQuestionIndexInBlock()).to.deep.equal({
                    activityIdx: 0,
                    questionIdx: 2,
                });
            });
            it("Should find the correct next exercise and question index in the unit", () => {
                component.activities[0].state = ActivityState.CompletedCorrectly;
                component.activities[0].exerciseQuestions.fill({ numAttempts: 1, correct: true });
                component.activities[1].state = ActivityState.AttemptedIncorrectly;
                component.activities[1].exerciseQuestions.fill({ numAttempts: 1, correct: false });
                component.activities[2].state = ActivityState.AttemptedIncorrectly;
                component.activities[2].exerciseQuestions[0] = { numAttempts: 1, correct: false };
                component.currentActivityIdx = 2;
                component.currentExerciseQuestionIdx = 3;
                expect(component.getNextIncompleteExerciseAndQuestionIndexInUnit()).to.deep.equal({
                    activityIdx: 1,
                    questionIdx: 0,
                });
                component.activities[1].exerciseQuestions[0] = { numAttempts: 2, correct: false };
                expect(component.getNextIncompleteExerciseAndQuestionIndexInUnit()).to.deep.equal({
                    activityIdx: 1,
                    questionIdx: 0,
                });
                component.activities[1].exerciseQuestions[0] = { numAttempts: 3, correct: false };
                expect(component.getNextIncompleteExerciseAndQuestionIndexInUnit()).to.deep.equal({
                    activityIdx: 2,
                    questionIdx: 0,
                });
                component.activities[2].exerciseQuestions[0] = { numAttempts: 3, correct: false };
                expect(component.getNextIncompleteExerciseAndQuestionIndexInUnit()).to.equal(undefined);
                component.activities[2].exerciseQuestions[0] = { numAttempts: 4, correct: false };
                expect(component.getNextIncompleteExerciseAndQuestionIndexInUnit()).to.equal(undefined);
            });
        });
    });
    context("A unit of only lessons", () => {
        const newUnitActivities = [createLessonActivity(1), createLessonActivity(4), createLessonActivity(1)];
        const component = new UnitProgressNavComponent();
        component.activities = newUnitActivities;
        component.ngOnInit();
        component.initExerciseBlocks();
        it("Should have no exercise blocks", () => {
            expect(Object.keys(component.exerciseBlocks)).to.have.length(0);
        });
    });
    context("A unit of lessons and exercises starting with a lesson", () => {
        const newUnitActivities = [
            createLessonActivity(1),
            createExerciseActivity("mcq", 3),
            createExerciseActivity("mcq", 4),
            createLessonActivity(1),
            createExerciseActivity("mcq", 4),
            createLessonActivity(1),
            createExerciseActivity("mcq", 4),
            createExerciseActivity("mcq", 4),
            createExerciseActivity("mcq", 4),
            createExerciseActivity("mcq", 4),
        ];
        const component = new UnitProgressNavComponent();
        component.activities = newUnitActivities;
        component.ngOnInit();
        component.initExerciseBlocks();
        it("Should have correct exercise blocks", () => {
            expect(Object.keys(component.exerciseBlocks)).to.have.length(7);
            expect(component.exerciseBlocks[1].firstExerciseIndex).to.equal(1);
            expect(component.exerciseBlocks[1].lastExerciseIndex).to.equal(2);
            expect(component.exerciseBlocks[2].firstExerciseIndex).to.equal(1);
            expect(component.exerciseBlocks[2].lastExerciseIndex).to.equal(2);
            expect(component.exerciseBlocks[4].firstExerciseIndex).to.equal(4);
            expect(component.exerciseBlocks[4].lastExerciseIndex).to.equal(4);
            expect(component.exerciseBlocks[6].firstExerciseIndex).to.equal(6);
            expect(component.exerciseBlocks[6].lastExerciseIndex).to.equal(9);
            expect(component.exerciseBlocks[7].firstExerciseIndex).to.equal(6);
            expect(component.exerciseBlocks[7].lastExerciseIndex).to.equal(9);
            expect(component.exerciseBlocks[8].firstExerciseIndex).to.equal(6);
            expect(component.exerciseBlocks[8].lastExerciseIndex).to.equal(9);
            expect(component.exerciseBlocks[9].firstExerciseIndex).to.equal(6);
            expect(component.exerciseBlocks[9].lastExerciseIndex).to.equal(9);
            expect(component.getFirstUnattemptedOrIncompleteActivityIdx()).to.equal(0);
            component.activities[0].state = ActivityState.CompletedCorrectly;
            expect(component.getFirstUnattemptedOrIncompleteActivityIdx()).to.equal(1);
            component.activities[0].state = ActivityState.AttemptedIncorrectly;
            expect(component.getFirstUnattemptedOrIncompleteActivityIdx()).to.equal(1);
            component.activities[0].state = ActivityState.CompletedIncorrectly;
            expect(component.getFirstUnattemptedOrIncompleteActivityIdx()).to.equal(1);
            component.activities[0].state = ActivityState.Attemptable;
            expect(component.getFirstUnattemptedOrIncompleteActivityIdx()).to.equal(1);
        });
    });
    context("A unit of lessons and exercises starting with an exercise", () => {
        const newUnitActivities = [
            createExerciseActivity("mcq", 3),
            createExerciseActivity("mcq", 4),
            createLessonActivity(1),
            createExerciseActivity("mcq", 4),
            createLessonActivity(1),
            createExerciseActivity("mcq", 4),
            createExerciseActivity("mcq", 4),
            createExerciseActivity("mcq", 4),
            createExerciseActivity("mcq", 4),
        ];
        const component = new UnitProgressNavComponent();
        component.activities = newUnitActivities;
        component.ngOnInit();
        component.initExerciseBlocks();
        it("Should be correct for unit of lessons and exercises starting with exercise", () => {
            expect(Object.keys(component.exerciseBlocks)).to.have.length(7);
            expect(component.exerciseBlocks[0].firstExerciseIndex).to.equal(0);
            expect(component.exerciseBlocks[0].lastExerciseIndex).to.equal(1);
            expect(component.exerciseBlocks[1].firstExerciseIndex).to.equal(0);
            expect(component.exerciseBlocks[1].lastExerciseIndex).to.equal(1);
            expect(component.exerciseBlocks[3].firstExerciseIndex).to.equal(3);
            expect(component.exerciseBlocks[3].lastExerciseIndex).to.equal(3);
            expect(component.exerciseBlocks[5].firstExerciseIndex).to.equal(5);
            expect(component.exerciseBlocks[5].lastExerciseIndex).to.equal(8);
            expect(component.exerciseBlocks[6].firstExerciseIndex).to.equal(5);
            expect(component.exerciseBlocks[6].lastExerciseIndex).to.equal(8);
            expect(component.exerciseBlocks[7].firstExerciseIndex).to.equal(5);
            expect(component.exerciseBlocks[7].lastExerciseIndex).to.equal(8);
            expect(component.exerciseBlocks[8].firstExerciseIndex).to.equal(5);
            expect(component.exerciseBlocks[8].lastExerciseIndex).to.equal(8);
        });
    });
});
