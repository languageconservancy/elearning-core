import { BehaviorSubject, Subject } from "rxjs";
import { Injectable } from "@angular/core";
import { CapacitorHttp } from "@capacitor/core";

import { BaseService } from "./base.service";
import * as API from "app/_constants/api.constants";

@Injectable()
export class LessonsService extends BaseService {
    private levelSubject = new BehaviorSubject<any>("");
    private unitSubject = new BehaviorSubject<any>("");
    private typeSubject = new BehaviorSubject<any>("");
    private levelDetailsSubject = new BehaviorSubject<any>({});
    private unitDetailsSubject = new BehaviorSubject<any>({});
    private frameSubject = new BehaviorSubject<any>({});
    private exerciseSubject = new BehaviorSubject<any>({});
    private questionSubject = new BehaviorSubject<any>({});
    private exeSubject = new BehaviorSubject<any>(false);
    private popupSubject = new BehaviorSubject<any>({});
    private answerSubject = new BehaviorSubject<any>({});
    private wrongAnswerSubject = new BehaviorSubject<any>({});
    private wrongCardSubject = new BehaviorSubject<any>({});
    private timerSubject = new BehaviorSubject<any>({});
    private stopTimerSubject = new BehaviorSubject<any>({});
    private unitReviewSubject = new BehaviorSubject<any>({});
    private recordingSubject = new BehaviorSubject<any>({});
    private recordAudioPathSubject = new BehaviorSubject<any>(false);
    private lastActiveUnitOrReviewSubject = new Subject<any>();

    public currentLevel = this.levelSubject.asObservable();
    public currentLevelDetails = this.levelDetailsSubject.asObservable();
    public currentUnit = this.unitSubject.asObservable();
    public currentUnitDetails = this.unitDetailsSubject.asObservable();
    public currentFrame = this.frameSubject.asObservable();
    public currentType = this.typeSubject.asObservable();
    public currentExercise = this.exerciseSubject.asObservable();
    public currentQuestion = this.questionSubject.asObservable();
    public nextExe = this.exeSubject.asObservable();
    public popup = this.popupSubject.asObservable();
    public answer = this.answerSubject.asObservable();
    public wrongAnswer = this.wrongAnswerSubject.asObservable();
    public wrongCard = this.wrongCardSubject.asObservable();
    public timer = this.timerSubject.asObservable();
    public stopTimerVar = this.stopTimerSubject.asObservable();
    public unitReview = this.unitReviewSubject.asObservable();
    public newRecord = this.recordingSubject.asObservable();
    public audioPath = this.recordAudioPathSubject.asObservable();
    public lastActiveUnitOrReview = this.lastActiveUnitOrReviewSubject.asObservable();

    setAudipPath(path: any) {
        this.recordAudioPathSubject.next(path);
    }
    setLevel(level: any) {
        this.levelSubject.next(level.id);
        this.levelDetailsSubject.next(level);
    }

    setLevelID(levelID: number) {
        this.levelSubject.next(levelID);
    }

    setUnit(unit: any) {
        this.unitSubject.next(unit.id);
        this.unitDetailsSubject.next(unit);
    }

    setType(type: string) {
        this.typeSubject.next(type);
    }

    setLessonFrame(frame: any) {
        this.frameSubject.next(frame);
    }

    setExercise(exercise: any) {
        this.exerciseSubject.next(exercise);
    }

    setQuestion(question: any) {
        this.questionSubject.next(question);
    }

    nextScreen(next: boolean) {
        this.exeSubject.next(next);
    }

    setPopup(params: any) {
        this.popupSubject.next(params);
    }

    answerGiven(params: any) {
        this.answerSubject.next(params);
    }

    wrongAnswerGiven(params: any) {
        this.wrongAnswerSubject.next(params);
    }

    setWrongCards(params: any) {
        this.wrongCardSubject.next(params);
    }

    startTimer(params: any) {
        this.timerSubject.next(params);
    }

    stopTimer(params: any) {
        this.stopTimerSubject.next(params);
    }

    setUnitReview(params: any) {
        this.unitReviewSubject.next(params);
    }

    setNewRecord(params: any) {
        this.recordingSubject.next(params);
    }

    setLastActiveUnitOrReview(params: any) {
        this.lastActiveUnitOrReviewSubject.next(params);
    }

    getLearningPathDetails(params: any) {
        return this.callApi(API.Path.PATH_DETAILS, "POST", params, {}, "site", true);
    }

    getUnitDetails(params: any) {
        return this.callApi(API.Path.UNIT_DETAILS, "POST", params, {}, "site", true);
    }

    getLesson(params: any) {
        return this.callApi(API.Lessons.FETCH, "POST", params, {}, "site", true);
    }

    getExercise(params: any) {
        return this.callApi(API.Exercises.FETCH, "POST", params, {}, "site", true);
    }

    questionAttemptedRequiredNumTimesMinusOne(params: any) {
        return this.callApi(
            API.Exercises.QUESTION_ATTEMPTED_REQUIRED_NUM_TIMES_MINUS_ONE,
            "POST",
            params,
            {},
            "site",
            true,
        );
    }

    lessonComplete(params: any) {
        return this.callApi(API.Points.ADD_ACTIVITY, "POST", params, {}, "site", true);
    }

    exerciseSetComplete(params: any) {
        return this.callApi(API.Points.SET_SCORE, "POST", params, {}, "site", true);
    }

    unitComplete(params: any) {
        return this.callApi(API.Points.UNIT_SCORE, "POST", params, {}, "site", true);
    }

    getTimerData(params): any {
        return this.callApi(API.Points.GET_TIMER, "POST", params, {}, "site", true);
    }

    setTimerData(params): any {
        return this.callApi(API.Points.SET_TIMER, "POST", params, {}, "site", true);
    }

    saveRecordedAudio(params: any) {
        return this.cookieService
            .get("AuthToken")
            .then((value) => {
                const headers = { Authorization: "Bearer " + value };
                return CapacitorHttp.post({
                    url: API.Exercises.SAVE_AUDIO,
                    data: params,
                    headers: headers,
                });
            })
            .catch((err) => {
                console.error(err);
            });
    }

    shareRecordedAudioForum(params: any) {
        return this.callApi(API.Forum.FORUM_SHARE, "POST", params, {}, "site", true);
    }

    shareRecordedAudioEmail(params: any) {
        return this.callApi(API.Exercises.EMAIL_SHARE, "POST", params, {}, "site", true);
    }
}
