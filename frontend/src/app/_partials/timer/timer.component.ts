import { Component, OnInit, OnDestroy } from "@angular/core";
import { take, map } from "rxjs/operators";
import { Subscription, timer, interval } from "rxjs";
import { CookieService } from "app/_services/cookie.service";

import { LessonsService } from "app/_services/lessons.service";
import { LocalStorageService } from "app/_services/local-storage.service";
import { ReviewService } from "app/_services/review.service";
import { KeyboardService } from "app/shared/keyboard/keyboard.service";
import { environment } from "environments/environment";

declare let jQuery: any;
const OneMinuteMs: number = 1000 * 60;
const OneSecMs: number = 1000;

enum ModalType {
    None,
    IdleModal,
    TimeUpModal,
}

type AllocatedDailyTimeCountdownTimer = {
    time_left_flag: boolean;
    time_remaining: number;
};

@Component({
    selector: "app-timer",
    templateUrl: "./timer.component.html",
    styleUrls: ["./timer.component.scss"],
})
/**
 * Timer Component
 * Two types of timers:
 *  1. Allocated Daily Time countdown timer
 *    - When the allocated daily time countdown timer reaches 0, the allocated
 *      daily time reached modal is shown and the allocated daily time countdown
 *      is hidden and a global fire is shown.
 *  2. Idle timer
 *    - When the idle timer times out, the idle modal is shown and the
 *      allocated daily time countdown timer is paused.
 *    - The idle timer is reset when the user interacts completes an activity.
 *    - The idle timer is reset when the user closes the idle modal.
 *    - The idle timer is reset when the user closes the allocated daily time
 */
export class TimerComponent implements OnInit, OnDestroy {
    public countDown: any;
    public timeRemainingSec: number = 0;
    public timerTicks: number = 0;
    public minutesDisplay: number = 0;
    public hoursDisplay: number = 0;
    public secondsDisplay: number = 0;
    public pathID: number = null;
    public levelID: number = null;
    public unitID: number = null;
    public allocatedDailyTimeCountdownTimer: AllocatedDailyTimeCountdownTimer;
    public user: any = {};
    public showAllocatedDailyTimeCountdownTimer: boolean = false;
    public showFire: boolean = false;
    public idleTimerTimeout: any;
    public idleTimerDurationMs: number = OneMinuteMs * 2;
    public timeZoneOffset: number = 0;
    public fireData: any = {};
    public fireImage: string = "dead";
    public debug = !environment.production;

    public allocatedDailyTimerSubscription: Subscription;
    public timerSub: Subscription;
    public stopAllocatedDailyTimerSubscription: Subscription;
    public intervalSub: Subscription;
    private keyboardSubmitSubscription: Subscription;
    // Used for keyboard shortcuts to know which modal to close
    private currentModal: ModalType = ModalType.None;

    constructor(
        private cookieService: CookieService,
        private lessonService: LessonsService,
        private reviewService: ReviewService,
        private localStorage: LocalStorageService,
        private keyboardService: KeyboardService,
    ) {}

    async ngOnInit() {
        this.subscribeToStopTimerEvents();
        try {
            this.user = await this.getAuthUserFromCookie();
        } catch (err) {
            console.error("Failed to get auth user. ", err);
            return;
        }
        this.subscribeToTimerEvents();
    }

    private async getAuthUserFromCookie() {
        try {
            const value = await this.cookieService.get("AuthUser");
            if (value == "") {
                throw value;
            }
            return JSON.parse(value);
        } catch (err) {
            console.warn("No AuthUser cookie", err);
            throw err;
        }
    }

    subscribeToStopTimerEvents() {
        this.stopAllocatedDailyTimerSubscription = this.lessonService.stopTimerVar.subscribe((params: any) => {
            if (params.stopTimer) {
                this.stopAllocatedDailyTimeCountdownTimer();
                this.stopIdleTimer();
            }
        });
    }

    subscribeToTimerEvents() {
        this.timerSub = this.lessonService.timer.subscribe((params: any) => {
            if (params && Object.keys(params).length > 0) {
                if (!this.showAllocatedDailyTimeCountdownTimer) {
                    void this.handleNewTimerEvent(params);
                } else {
                    this.resetIdleTimer();
                }
            }
        });
    }

    async handleNewTimerEvent(params: any) {
        try {
            this.allocatedDailyTimeCountdownTimer = await this.fetchAllocatedDailyTimerData(params);
            if (this.allocatedDailyTimeCountdownTimer.time_left_flag) {
                this.handleUserHasntReachedAllocatedDailyTime();
            } else {
                this.handleUserHasReachedAllocatedDailyTime();
            }
        } catch (err) {
            console.error(err);
            return;
        }
    }

    handleUserHasntReachedAllocatedDailyTime() {
        this.showAllocatedDailyTimeCountdownTimer = true;
        this.timeRemainingSec = this.allocatedDailyTimeCountdownTimer.time_remaining /*min*/ * 60 /*sec/min*/;
        this.resetIdleTimer();
        this.createAndStartTimer();
        this.subscribeToTimer();
    }

    handleUserHasReachedAllocatedDailyTime() {
        this.showAllocatedDailyTimeCountdownTimer = false;
        this.showFire = true;
        this.getFireData();
        this.startLearningTimeTracker();
    }

    private resetIdleTimer() {
        if (this.idleTimerTimeout) {
            if (this.debug) console.debug("Resetting idle timer");
            clearTimeout(this.idleTimerTimeout);
        }
        this.idleTimerTimeout = setTimeout(() => {
            this.idleTimeReached();
        }, this.idleTimerDurationMs);
    }

    private idleTimeReached() {
        // show idle modal
        jQuery("#idleTimeoutModal").modal("show");
        this.currentModal = ModalType.IdleModal;
        // stop the allocated daily time countdown timer
        this.stopAllocatedDailyTimeCountdownTimer();
        this.stopIdleTimer();
        // enable keyboard shortcuts to close the modal
        this.setKeyboardListeners(true);
    }

    private setKeyboardListeners(turnOn: boolean) {
        if (turnOn) {
            this.keyboardSubmitSubscription = this.keyboardService.submitOrCloseEvent.subscribe(() => {
                if (this.currentModal == ModalType.IdleModal) {
                    this.closeIdleTimeoutModal();
                } else if (this.currentModal == ModalType.TimeUpModal) {
                    this.closeAllocatedDailyTimeReachedModal();
                }
            });
        } else {
            if (!!this.keyboardSubmitSubscription) this.keyboardSubmitSubscription.unsubscribe();
        }
    }

    async fetchAllocatedDailyTimerData(params: any): Promise<any> {
        const timeZoneOffset = new Date().getTimezoneOffset() * 60;
        this.timeZoneOffset = timeZoneOffset > 0 ? -Math.abs(timeZoneOffset) : Math.abs(timeZoneOffset);
        this.pathID = parseInt(params.path_id);
        this.levelID = parseInt(this.localStorage.getItem("LevelID"));
        this.unitID = parseInt(this.localStorage.getItem("unitID"));
        params.timestamp_offset = this.timeZoneOffset;
        try {
            const res = await this.lessonService.getTimerData(params);
            return res.data.results;
        } catch (err) {
            console.error(err);
            throw err;
        }
    }

    private createAndStartTimer() {
        // create observable timer that starts emitting immediately and
        // emits an incrementing number every 1000ms
        this.countDown = timer(0, OneSecMs).pipe(
            take(this.timeRemainingSec), // limits the number of emissions to this value
            map(() => --this.timeRemainingSec), // decreases the value by one each time the observable emits
        );
    }

    private subscribeToTimer() {
        if (!!this.allocatedDailyTimerSubscription && !this.allocatedDailyTimerSubscription.closed) {
            return;
        }
        this.allocatedDailyTimerSubscription = this.countDown.subscribe((t: number) => {
            // counting down and triggered every second
            if (t % 60 == 0) {
                void this.addMinuteToUsersDailyLearningTimeInPath();
            }
            if (t < 0) {
                return;
            }

            this.timerTicks = t;
            this.updateCountdownTimerDisplay();

            if (t == 0) {
                this.handleAllocatedDailyTimeReached();
            }
        });
    }

    handleAllocatedDailyTimeReached() {
        // user allocated daily time reached. present modal.
        jQuery("#allocatedDailyTimeReachedModal").modal("show");
        this.currentModal = ModalType.TimeUpModal;
        this.stopAllocatedDailyTimeCountdownTimer();
        this.stopIdleTimer();
        this.updateUserLearningStreak();
        this.startLearningTimeTracker();
        this.showAllocatedDailyTimeCountdownTimer = false;
        this.showFire = true;
        this.setKeyboardListeners(true);
    }

    private updateCountdownTimerDisplay() {
        this.secondsDisplay = this.getSeconds(this.timerTicks);
        this.minutesDisplay = this.getMinutes(this.timerTicks);
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

    private stopAllocatedDailyTimeCountdownTimer() {
        if (!!this.allocatedDailyTimerSubscription && !this.allocatedDailyTimerSubscription.closed) {
            // stop the allocated daily time countdown timer
            this.allocatedDailyTimerSubscription.unsubscribe();
        }
    }

    private stopIdleTimer() {
        if (this.idleTimerTimeout) {
            // stop the idle timer
            clearTimeout(this.idleTimerTimeout);
        }
    }

    private startLearningTimeTracker() {
        // Emit every minute, thereby adding a minute to the user's daily
        // learning time each emission.
        this.intervalSub = interval(OneMinuteMs).subscribe(() => {
            void this.addMinuteToUsersDailyLearningTimeInPath(); // it's okay not to wait here
        });
    }

    private async addMinuteToUsersDailyLearningTimeInPath() {
        const params = {
            path_id: this.pathID,
            level_id: this.levelID,
            unit_id: this.unitID,
            user_id: this.user.id,
            timer_type: "path",
            minute_spent: 1,
            timestamp_offset: this.timeZoneOffset,
        };
        try {
            await this.lessonService.setTimerData(params);
            if (this.timerTicks > this.idleTimerDurationMs) {
                this.resetIdleTimer();
            }
        } catch (err: any) {
            console.error(err);
        }
    }

    private updateUserLearningStreak() {
        const params = {
            user_id: this.user.id,
            type: "achievement",
            timestamp_offset: this.timeZoneOffset,
        };
        this.reviewService
            .globalFire(params)
            .then((res) => {
                if (res.data.status) {
                    this.getFireData();
                }
            })
            .catch((err: any) => {
                console.error("[updateUserLearningStreak] " + err);
            });
    }

    private getFireData() {
        this.reviewService
            .getFire({ user_id: this.user.id })
            .then((res: any) => {
                if (res.data.status) {
                    this.fireData = res.data.results;
                    this.localStorage.setItem("timerOff", "true");
                    if (this.fireData.haveReviewExercise) {
                        this.reviewService.setReviewMenu(true);
                        this.setFireImage();
                    }
                }
            })
            .catch((err: any) => {
                console.error("[getFireData] " + err);
            });
    }

    private setFireImage() {
        const fireDays = this.fireData.FireData.fire_days;
        if (fireDays < 3) {
            this.fireImage = "low";
        } else if (fireDays < 7) {
            this.fireImage = "medium";
        } else if (fireDays < 14) {
            this.fireImage = "high";
        } else {
            this.fireImage = "ultra";
        }
    }

    closeIdleTimeoutModal() {
        jQuery("#idleTimeoutModal").modal("hide");
        this.currentModal = ModalType.None;
        this.subscribeToTimer();
        this.resetIdleTimer();
        this.setKeyboardListeners(false);
    }

    closeAllocatedDailyTimeReachedModal() {
        jQuery("#allocatedDailyTimeReachedModal").modal("hide");
        this.currentModal = ModalType.None;
        this.setKeyboardListeners(false);
    }

    ngOnDestroy() {
        this.stopAllocatedDailyTimeCountdownTimer();
        this.stopIdleTimer();

        if (this.timerSub) {
            this.timerSub.unsubscribe();
        }

        if (this.stopAllocatedDailyTimerSubscription) {
            this.stopAllocatedDailyTimerSubscription.unsubscribe();
        }

        if (this.intervalSub) {
            this.intervalSub.unsubscribe();
        }

        this.lessonService.startTimer({});
        this.setKeyboardListeners(false);
    }
}
