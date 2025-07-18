import { Component, OnDestroy, ElementRef, ChangeDetectorRef } from "@angular/core";
import { Router } from "@angular/router";
import { Subscription } from "rxjs";
import { CookieService } from "app/_services/cookie.service";

import { ReviewService } from "app/_services/review.service";
import { Loader } from "app/_services/loader.service";
import { LocalStorageService } from "app/_services/local-storage.service";
import { LessonsService } from "app/_services/lessons.service";
import { ReviewType } from "app/shared/utils/elearning-types";

@Component({
    selector: "app-review-cards",
    templateUrl: "./review-cards.component.html",
    styleUrls: ["./review-cards.component.scss"],
})
export class ReviewCardsComponent implements OnDestroy {
    public numCompletedReviewActivitiesToUnlockNextUnit: number = 25;
    public user: any = {};
    public currentExercise: number = null;
    public reviewDetails: any = [];
    public lessonDetail: any = {};
    public noExercises: boolean = false;
    public newSet: boolean = true;
    public unitID: number = null;
    public reviewnextpopup: boolean = false;
    public review_counter_arr: any;
    public breadcrumb: any = [];
    public answerSubscription: Subscription;
    public popupSubscription: Subscription;
    public mnpSubscription: Subscription;
    public unitSubscription: Subscription;
    public reviewType: ReviewType = ReviewType.PATH;

    constructor(
        private cookieService: CookieService,
        private reviewService: ReviewService,
        private lessonService: LessonsService,
        private localStorage: LocalStorageService,
        private router: Router,
        private loader: Loader,
        private myElement: ElementRef,
        private ref: ChangeDetectorRef,
    ) {
        this.cookieService
            .get("AuthUser")
            .then((value) => {
                if (value == "") {
                    throw value;
                }
                this.user = JSON.parse(value);
                this.getReviewDetails();

                this.answerSubscription = this.reviewService.answer.subscribe((ans) => {
                    if (Object.keys(ans).length > 0) {
                        this.handleAnswers(ans);
                    }
                });

                this.popupSubscription = this.reviewService.popup.subscribe((res) => {
                    if (res.popUpClosed) {
                        if (this.reviewDetails[this.currentExercise].exercise_type != "match-the-pair") {
                            this.next();
                        }
                    }
                });

                this.mnpSubscription = this.reviewService.nextExe.subscribe((nextExe) => {
                    if (nextExe) {
                        this.next();
                    }
                });
            })
            .catch((err) => {
                console.warn("No AuthUser cookie", err);
                void this.router.navigate([""]);
            });

        this.unitSubscription = this.reviewService.unit.subscribe((unit) => {
            if (unit) {
                this.unitID = unit.unit_id;
                this.reviewType = ReviewType.UNIT;
            }
        });
    }

    ngOnDestroy() {
        this.answerSubscription.unsubscribe();
        this.popupSubscription.unsubscribe();
        this.mnpSubscription.unsubscribe();
        this.unitSubscription.unsubscribe();

        this.reviewService.setPopup({});
        this.reviewService.answerGiven({});
        this.reviewService.wrongAnswerGiven({});
        this.reviewService.setWrongCards([]);
    }

    private setLoader(val: boolean) {
        this.loader.setLoader(val);
    }

    getReviewDetails() {
        this.reviewDetails = [];
        const params: any = { user_id: this.user.id };
        //if unitID set by service or persistence, use that
        if (this.unitID) {
            params.unit_id = this.unitID;
            this.localStorage.setItem("reviewUnit", this.unitID);
            //else check localstorage for persistent review after refresh
        } else if (this.localStorage.getItem("reviewUnit")) {
            this.unitID = parseInt(this.localStorage.getItem("reviewUnit")) || null;
            params.unit_id = this.unitID;
            this.reviewType = ReviewType.UNIT;
        }
        this.setLoader(true);
        this.reviewService
            .getReviewDetails(params)
            .then((res) => {
                this.setLoader(false);
                if (res.data.status) {
                    this.reviewDetails = res.data.results;
                    this.ref.detectChanges();
                    if (this.reviewDetails.length > 0) {
                        this.currentExercise = 0;
                        this.noExercises = false;
                        this.setCurrentExercise();
                    } else {
                        this.noExercises = true;
                        this.reviewService.stopTimer({ stopTimer: true });
                    }
                }
            })
            .catch((err) => {
                this.setLoader(false);
                console.error(err);
            });
    }

    setCurrentExercise() {
        if (
            this.reviewDetails[this.currentExercise].exercise_type == "recording" &&
            !this.reviewDetails[this.currentExercise].question.audio
        ) {
            this.next();
        } else {
            console.debug("Setting exercise", this.reviewDetails[this.currentExercise]);
            this.reviewService.setExercise(this.reviewDetails[this.currentExercise]);
            if (this.newSet) {
                this.reviewService.startTimer({ user_id: this.user.id, path_id: this.user.learningpath_id });
                this.newSet = false;
            }
        }
    }

    functionCallOutput(ev) {
        this.reviewnextpopup = false;
        if (ev === "Continue") {
            this.continueReview();
        } else if (ev === "Next") {
            void this.router.navigate(["start-learning"]);
        }
    }

    private continueReview() {
        if (this.currentExercise < this.reviewDetails.length - 1) {
            this.currentExercise++;
            this.setCurrentExercise();
        } else {
            this.getReviewDetails();
        }
    }

    next() {
        if (this.reviewSessionFinishedWithCurrentExercise()) {
            this.reviewnextpopup = true;
        } else {
            this.continueReview();
            this.reviewnextpopup = false;
        }
    }

    handleAnswers(ans) {
        this.setLoader(true);
        this.reviewService
            .exerciseComplete(ans)
            .then((res) => {
                this.reviewService.setReviewProgress({ progressValue: res.data.results });
                this.review_counter_arr = res.data.results;
                this.numCompletedReviewActivitiesToUnlockNextUnit =
                    res.data.results.num_correct_review_answers_to_unlock_unit;
                this.setLoader(false);
                if (ans.popup_status) {
                    this.reviewService.setPopup({
                        type: "exercise",
                        status: ans.answar_type == "right",
                        review: ans.card_id != null,
                        data: res.data.results,
                    });
                } else if (this.reviewDetails[this.currentExercise].exercise_type == "match-the-pair") {
                    const params: any = {};
                    this.reviewService.nextSubExercise(params);
                } else {
                    this.next();
                }
            })
            .catch((err) => {
                this.setLoader(false);
                console.error(err);
            });
    }

    reviewSessionFinishedWithCurrentExercise() {
        const ex = this.reviewDetails[this.currentExercise];

        if (!ex || !ex.exercise_type) {
            if (!!ex) {
                console.log(JSON.stringify(ex));
            }
            return false;
        }

        if (
            this.review_counter_arr &&
            this.review_counter_arr.showModal &&
            this.review_counter_arr.review_counter &&
            this.review_counter_arr.review_counter == this.numCompletedReviewActivitiesToUnlockNextUnit
        ) {
            return true;
        } else if (
            ex.exercise_type &&
            ex.exercise_type == "match-the-pair" &&
            this.review_counter_arr.review_counter - ex.choices.length <
                this.numCompletedReviewActivitiesToUnlockNextUnit &&
            this.review_counter_arr.review_counter > this.numCompletedReviewActivitiesToUnlockNextUnit
        ) {
            /* if the just-completed exercises is a match-the-pair exercise, check to see if this activity, with each of its cards,
				 caused the review counter to reach 25. */
            return true;
        } else {
            return false;
        }
    }

    exitReview() {
        void this.router.navigate(["dashboard"]);
    }
}
