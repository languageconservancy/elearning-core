import { Component, Output, EventEmitter } from "@angular/core";
import { Router } from "@angular/router";
import { Subscription } from "rxjs";
import { CookieService } from "app/_services/cookie.service";

import { Loader } from "app/_services/loader.service";
import { LessonsService } from "app/_services/lessons.service";
import { ReviewService } from "app/_services/review.service";
import { LocalStorageService } from "app/_services/local-storage.service";
import { OwoksapeUtils } from "app/shared/utils/owoksape-utils";
import { KeyboardService } from "app/shared/keyboard/keyboard.service";

@Component({
    selector: "app-review-next-popup",
    templateUrl: "./review-next-popup.component.html",
    styleUrls: ["./review-next-popup.component.scss"],
})
export class ReviewNextPopupComponent {
    @Output() abcd: EventEmitter<any> = new EventEmitter();

    public popupSubscription: Subscription;
    public wrongAnswerSubscription: Subscription;
    private keyboardSubmitSubscription: Subscription;
    private keyboardToggleSelectionSubscription: Subscription;

    public showPopup: boolean = false;
    public popupType: string = "";
    public popupData: any = {};
    public reviewCards: any = [];
    public activeCard: any = {};
    public activeIndex: number = 0;
    public nextDisable: boolean = false;
    public previousDisable: boolean = true;
    public popupStatus: boolean = false;
    public review: boolean = false;
    public audio: any = new Audio();
    public wrongCard: any = {};
    public lessonPoints: any = {};
    public wrongAnsHeader: string = "";
    private wrongAnsHeaderArray: any = ["Haúŋ!", "Huští!"];
    public fireData: any = {};
    public fireImage: string = "dead";
    public user: any = {};
    public activeButtonIndex: number = -1;
    public isClassroom: boolean = false;
    readonly BUTTON_CHOICES: any = ["Next", "Continue"];

    constructor(
        private lessonService: LessonsService,
        private loader: Loader,
        private reviewService: ReviewService,
        private cookieService: CookieService,
        private localStorage: LocalStorageService,
        private router: Router,
        private keyboardService: KeyboardService,
    ) {
        this.cookieService
            .get("AuthUser")
            .then((value) => {
                if (value != "") {
                    this.user = JSON.parse(value);
                    this.getFireData();
                }
            })
            .catch((err) => {
                console.warn("No AuthUser cookie", err);
            });

        if (parseInt(localStorage.getItem("isClassroom")) == 1) {
            this.isClassroom = true;
        }
        this.setKeyboardListeners(true);
    }

    ngonDestroy() {
        this.setKeyboardListeners(false);
    }

    setKeyboardListeners(turnOn: boolean) {
        if (turnOn) {
            // Turn on toggling of option keyboard shortcut
            this.keyboardToggleSelectionSubscription = this.keyboardService.toggleSelectionEvent.subscribe((event) => {
                if (event.shiftKey) {
                    this.activeButtonIndex = OwoksapeUtils.decrementWrap(
                        this.activeButtonIndex,
                        0,
                        this.BUTTON_CHOICES.length - 1,
                    );
                } else {
                    this.activeButtonIndex = OwoksapeUtils.incrementWrap(
                        this.activeButtonIndex,
                        0,
                        this.BUTTON_CHOICES.length - 1,
                    );
                }
            });
            // Turn on submit keyboard shortcut
            this.keyboardSubmitSubscription = this.keyboardService.submitOrCloseEvent.subscribe(() => {
                if (this.activeButtonIndex < 0) {
                    this.activeButtonIndex = 0;
                }
                this.gotoContinue(this.BUTTON_CHOICES[this.activeButtonIndex]);
            });
        } else {
            // Turn on keyboard listeners
            if (this.keyboardToggleSelectionSubscription) this.keyboardToggleSelectionSubscription.unsubscribe();
            if (this.keyboardSubmitSubscription) this.keyboardSubmitSubscription.unsubscribe();
        }
    }

    private setLoader(val: boolean) {
        this.loader.setLoader(val);
    }

    private getFireData() {
        this.setLoader(true);
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
                console.error("[review-next-popup] Error getting fire data. ", err);
            })
            .finally(() => {
                this.loader.setLoader(false);
            });
    }

    gotoContinue(text: any) {
        if (text == "Classroom") {
            void this.router.navigate(["classroom"]);
        } else {
            this.abcd.emit(text);
        }
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
}
