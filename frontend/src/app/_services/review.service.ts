import { BehaviorSubject, Subject } from "rxjs";
import { Injectable } from "@angular/core";
import { CapacitorHttp } from "@capacitor/core";

import { BaseService } from "./base.service";
import * as API from "app/_constants/api.constants";

@Injectable()
export class ReviewService extends BaseService {
    private exerciseSubject = new BehaviorSubject<any>({});
    private answerSubject = new BehaviorSubject<any>({});
    private wrongAnswerSubject = new BehaviorSubject<any>({});
    private wrongCardSubject = new BehaviorSubject<any>({});
    private popupSubject = new BehaviorSubject<any>({});
    private exeSubject = new Subject<any>();
    private timerSubject = new BehaviorSubject<any>({});
    private stopTimerSubject = new BehaviorSubject<any>({});
    private unitSubject = new BehaviorSubject<any>({});
    private reviewMenuSubject = new BehaviorSubject<any>(false);
    private recordingSubject = new BehaviorSubject<any>({});
    private reviewProgressBar = new BehaviorSubject<any>({});
    private breadcrumbSubject = new BehaviorSubject<any>({});
    private nextSubExerciseSubject = new BehaviorSubject<any>({});

    public currentExercise = this.exerciseSubject.asObservable();
    public answer = this.answerSubject.asObservable();
    public wrongAnswer = this.wrongAnswerSubject.asObservable();
    public wrongCard = this.wrongCardSubject.asObservable();
    public popup = this.popupSubject.asObservable();
    public reviewProgress = this.reviewProgressBar.asObservable();
    public nextExe = this.exeSubject.asObservable();
    public timer = this.timerSubject.asObservable();
    public stopTimerVar = this.stopTimerSubject.asObservable();
    public unit = this.unitSubject.asObservable();
    public reviewMenu = this.reviewMenuSubject.asObservable();
    public newRecord = this.recordingSubject.asObservable();
    public breadcrumb = this.breadcrumbSubject.asObservable();
    public nextSubExe = this.nextSubExerciseSubject.asObservable();

    setBreadcrumb(params: any) {
        this.breadcrumbSubject.next(params);
    }

    nextSubExercise(params: any) {
        this.nextSubExerciseSubject.next(params);
    }

    setNewRecord(params: any) {
        this.recordingSubject.next(params);
    }

    setExercise(exercise: any) {
        this.exerciseSubject.next(exercise);
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

    setPopup(params: any) {
        this.popupSubject.next(params);
    }

    setReviewProgress(params: any) {
        this.reviewProgressBar.next(params);
    }

    nextScreen(next: boolean) {
        this.exeSubject.next(next);
    }

    startTimer(params: any) {
        this.timerSubject.next(params);
    }

    stopTimer(params: any) {
        this.stopTimerSubject.next(params);
    }

    setUnit(params: any) {
        this.unitSubject.next(params);
    }

    setReviewMenu(param: boolean) {
        this.reviewMenuSubject.next(param);
    }

    getReviewDetails(params: any) {
        return this.callApi(API.Review.FETCH, "POST", params, {}, "site", true);
    }

    exerciseComplete(params: any) {
        return this.callApi(API.Points.ADD_ACTIVITY, "POST", params, {}, "site", true);
    }

    globalFire(params): any {
        return this.callApi(API.Points.GLOBAL_FIRE, "POST", params, {}, "site", true);
    }

    getFire(params): any {
        return this.callApi(API.Review.GET_FIRE, "POST", params, {}, "site", true);
    }

    getReviewScore(params): any {
        return this.callApi(API.Points.REVIEW_SCORE, "POST", params, {}, "site", true);
    }

    saveRecordedAudio(params: any) {
        return this.cookieService
            .get("AuthToken")
            .then((value) => {
                if (!value) {
                    throw new Error("Empty AuthToken cookie");
                }
                const headers = { Authorization: "Bearer " + value };
                return CapacitorHttp.post({
                    url: API.Exercises.SAVE_AUDIO,
                    headers: headers,
                    data: params,
                });
            })
            .catch((err) => {
                console.error(err);
            });
    }
}
