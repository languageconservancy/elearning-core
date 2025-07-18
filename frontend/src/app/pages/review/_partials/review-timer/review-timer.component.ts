import { Component, OnDestroy } from "@angular/core";
import { take, map } from "rxjs/operators";
import { Subscription, timer, interval } from "rxjs";
import { Router } from "@angular/router";

import { LessonsService } from "app/_services/lessons.service";
import { Loader } from "app/_services/loader.service";
import { LocalStorageService } from "app/_services/local-storage.service";
import { CookieService } from "app/_services/cookie.service";
import { ReviewService } from "app/_services/review.service";
import { KeyboardService } from "app/shared/keyboard/keyboard.service";

declare let jQuery: any;

enum ModalType {
    None,
    IdleModal,
    TimeUpModal,
}

@Component({
    selector: "app-review-timer",
    templateUrl: "./review-timer.component.html",
    styleUrls: ["./review-timer.component.scss"],
})
export class ReviewTimerComponent implements OnDestroy {
    public countDown: any;
    public count: number = 0;
    public ticks: number = 0;
    public minutesDisplay: number = 0;
    public hoursDisplay: number = 0;
    public secondsDisplay: number = 0;
    public pathID: number = null;
    public levelID: any = null;
    public unitID: any = null;
    public timerData: any = {};
    public user: any = {};
    public showTimer: boolean = false;
    public showFire: boolean = false;
    public closeModalTimeout: any;
    public idleClearTime: number = 1000 * 60 * 2;
    public timeZoneOffset: number = 0;
    public fireData: any = {};
    public fireImage: string = "dead";
    private currentModal: ModalType = ModalType.None;

    public sub: Subscription;
    public timerSub: Subscription;
    public intervalSub: Subscription;
    public stopTimerSub: Subscription;
    public popupSubscription: Subscription;
    private keyboardSubmitSubscription: Subscription;
    public totaldata: any = {};
    public unitPercentageFlag: boolean;
    public numCorrectlyAnsweredReviewQuestions: any;
    public numRemainingReviewQuestionsToAnswerCorrectly: any;

    constructor(
        private cookieService: CookieService,
        private lessonService: LessonsService,
        private reviewService: ReviewService,
        private localStorage: LocalStorageService,
        private keyboardService: KeyboardService,
        private loader: Loader,
        private router: Router,
    ) {
        this.stopTimerSub = this.reviewService.stopTimerVar.subscribe((params) => {
            if (params.stopTimer) {
                this.stopTimer();
            }
        });

        this.cookieService
            .get("AuthUser")
            .then((value) => {
                if (value != "") {
                    this.user = JSON.parse(value);
                    this.timerSub = this.reviewService.timer.subscribe((params) => {
                        if (params && Object.keys(params).length > 0 && !this.showTimer) {
                            this.setUpTimer(params);
                        }
                    });
                }
            })
            .catch((err) => {
                console.warn("No AuthUser cookie", err);
            });

        this.popupSubscription = this.reviewService.reviewProgress.subscribe((res) => {
            if (res && res.progressValue && res.progressValue.showModal) {
                if (res && res.progressValue && res.progressValue.review_counter) {
                    const numCorrectReviewAnswersToUnlockUnit =
                        res.progressValue?.num_correct_review_answers_to_unlock_unit;
                    this.numCorrectlyAnsweredReviewQuestions = res.progressValue.review_counter;
                    if (this.numCorrectlyAnsweredReviewQuestions > numCorrectReviewAnswersToUnlockUnit) {
                        this.unitPercentageFlag = false;
                    } else {
                        this.unitPercentageFlag = true;
                        this.numRemainingReviewQuestionsToAnswerCorrectly =
                            numCorrectReviewAnswersToUnlockUnit - parseInt(this.numCorrectlyAnsweredReviewQuestions);
                    }
                } else {
                    this.unitPercentageFlag = false;
                }
            } else {
                this.unitPercentageFlag = false;
            }
        });
    }

    ngOnDestroy() {
        this.stopTimer();

        if (this.timerSub) {
            this.timerSub.unsubscribe();
        }

        if (this.stopTimerSub) {
            this.stopTimerSub.unsubscribe();
        }

        if (this.intervalSub) {
            this.intervalSub.unsubscribe();
        }

        this.reviewService.startTimer({});
        this.setKeyboardListeners(false);
    }

    private setKeyboardListeners(turnOn: boolean) {
        if (turnOn) {
            this.keyboardSubmitSubscription = this.keyboardService.submitOrCloseEvent.subscribe(() => {
                if (this.currentModal == ModalType.IdleModal) {
                    this.closeTimeOutModal();
                } else if (this.currentModal == ModalType.TimeUpModal) {
                    this.closeTimeUpModal();
                }
            });
        } else {
            if (!!this.keyboardSubmitSubscription) this.keyboardSubmitSubscription.unsubscribe();
        }
    }

    private setLoader(val: boolean) {
        this.loader.setLoader(val);
    }

    private setIdleZero() {
        if (this.closeModalTimeout) {
            clearTimeout(this.closeModalTimeout);
        }
        this.closeModalTimeout = setTimeout(() => {
            jQuery("#idleModal").modal("show");
            this.currentModal = ModalType.IdleModal;
            this.stopTimer();
            this.setKeyboardListeners(true);
        }, this.idleClearTime);
    }

    private setUpTimer(params) {
        const timeZoneOffset = new Date().getTimezoneOffset() * 60;
        this.timeZoneOffset = timeZoneOffset > 0 ? -Math.abs(timeZoneOffset) : Math.abs(timeZoneOffset);
        this.pathID = parseInt(params.path_id) || null;
        this.levelID = parseInt(this.localStorage.getItem("LevelID")) || null;
        this.unitID = parseInt(this.localStorage.getItem("unitID")) || null;
        params.timestamp_offset = this.timeZoneOffset;

        this.setTimerData(params);
    }

    private setTimerData(params) {
        this.setLoader(true);
        this.lessonService
            .getTimerData(params)
            .then((res) => {
                this.setLoader(false);
                this.timerData = res.data.results;

                if (this.timerData.time_left_flag) {
                    this.showTimer = true;
                    this.showFire = false;
                    this.count = this.timerData.time_remaining * 60;
                    this.setIdleZero();
                    this.setTimer();
                } else {
                    this.showTimer = false;
                    this.showFire = true;
                    this.getFireData();
                    this.startInterval();
                }
            })
            .catch((err) => {
                this.setLoader(false);
                console.error(err);
            });
    }

    private setTimer() {
        this.countDown = timer(0, 1000).pipe(
            take(this.count),
            map(() => --this.count),
        );
        this.subscribeToTimer();
    }

    private subscribeToTimer() {
        this.sub = this.countDown.subscribe((t) => {
            if (t >= 0) {
                if (t % 60 == 0) {
                    this.intervalUpdate();
                }
                this.ticks = t;
                this.secondsDisplay = this.getSeconds(this.ticks);
                this.minutesDisplay = this.getMinutes(this.ticks);

                if (t == 0) {
                    this.setLoader(true);
                    const params: any = {
                        user_id: this.user.id,
                    };
                    if (this.localStorage.getItem("reviewUnit")) {
                        params.unit_id = parseInt(this.localStorage.getItem("reviewUnit"));
                    }
                    this.reviewService
                        .getReviewScore(params)
                        .then((res) => {
                            this.totaldata = res.data.results.review_score_total;
                            this.setLoader(false);
                            jQuery("#timeUpModal").modal("show");
                            this.currentModal = ModalType.TimeUpModal;
                            this.stopTimer();
                            this.globalFire();
                            this.startInterval();
                            this.showTimer = false;
                            this.showFire = true;
                            this.setKeyboardListeners(true);
                        })
                        .catch((err) => {
                            console.error(err);
                        })
                        .finally(() => {
                            this.setLoader(false);
                        });
                }
            }
        });
    }

    private startInterval() {
        this.intervalSub = interval(1000 * 60).subscribe(() => {
            this.intervalUpdate();
        });
    }

    private intervalUpdate() {
        const params = {
            path_id: this.pathID,
            user_id: this.user.id,
            timer_type: "review",
            minute_spent: 1,
            timestamp_offset: this.timeZoneOffset,
        };
        this.lessonService
            .setTimerData(params)
            .then(() => {
                if (this.ticks > this.idleClearTime) {
                    this.setIdleZero();
                }
            })
            .catch((err) => {
                console.error(err);
            });
    }

    private getSeconds(ticks: number) {
        return this.pad(ticks % 60);
    }

    private getMinutes(ticks: number) {
        return this.pad(Math.floor(ticks / 60) % 60);
    }

    private pad(digit: any) {
        return digit <= 9 ? "0" + digit : digit;
    }

    private stopTimer() {
        if (this.sub) {
            this.sub.unsubscribe();
        }

        if (this.closeModalTimeout) {
            clearTimeout(this.closeModalTimeout);
        }
    }

    private globalFire() {
        const params = {
            user_id: this.user.id,
            type: "achievement",
            timestamp_offset: this.timeZoneOffset,
        };
        this.reviewService
            .globalFire(params)
            .then((res) => {
                if (res.data.status) {
                    this.stopTimer();
                    this.getFireData();
                }
            })
            .catch((err) => {
                console.error(err);
            })
            .finally(() => {
                this.setLoader(false);
            });
    }

    private getFireData() {
        this.reviewService
            .getFire({ user_id: this.user.id })
            .then((res) => {
                if (res.data.status) {
                    this.fireData = res.data.results;
                    if (this.fireData.haveReviewExercise) {
                        this.setFireImage();
                    }
                }
            })
            .catch((err) => {
                console.error(err);
            })
            .finally(() => {
                this.setLoader(false);
            });
    }

    private setFireImage() {
        if (this.fireData.FireData.fire_days > 0 && this.fireData.FireData.fire_days < 3) {
            this.fireImage = "low";
        } else if (this.fireData.FireData.fire_days >= 3 && this.fireData.FireData.fire_days < 7) {
            this.fireImage = "medium";
        } else if (this.fireData.FireData.fire_days >= 7 && this.fireData.FireData.fire_days < 14) {
            this.fireImage = "high";
        } else if (this.fireData.FireData.fire_days >= 14) {
            this.fireImage = "ultra";
        }
    }

    closeTimeOutModal() {
        jQuery("#idleModal").modal("hide");
        this.currentModal = ModalType.None;
        this.subscribeToTimer();
        this.setKeyboardListeners(false);
    }

    closeTimeUpModal() {
        jQuery("#timeUpModal").modal("hide");
        this.currentModal = ModalType.None;
        this.setKeyboardListeners(false);
    }
    redirectLearning() {
        jQuery("#timeUpModal").modal("hide");
        void this.router.navigate(["start-learning"]);
    }
}
