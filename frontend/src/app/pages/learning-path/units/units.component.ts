import { Component, OnInit, OnDestroy } from "@angular/core";
import { CookieService } from "app/_services/cookie.service";
import { Router } from "@angular/router";
import Swal from "sweetalert2";

import { Loader } from "app/_services/loader.service";
import { LessonsService } from "app/_services/lessons.service";
import { LocalStorageService } from "app/_services/local-storage.service";
import { Subscription } from "rxjs";
import { ReviewService } from "app/_services/review.service";
import { ForumService } from "app/_services/forum.service";
import { environment } from "environments/environment";

@Component({
    selector: "app-units",
    templateUrl: "./units.component.html",
    styleUrls: ["./units.component.scss"],
})
export class UnitsComponent implements OnInit, OnDestroy {
    public level: any = {};
    public fireData: any = {};
    public user: any = {};

    public levelSubscription: Subscription;
    public unitReviewSubscription: Subscription;
    public lastActiveUnitOrReviewSubscription: Subscription;

    private continueUnitEnabledImg: string = "./assets/images/continue-icon.png";
    private continueUnitLockedImg: string = "./assets/images/unit-lock-icon.png";
    private continueUnitScheduledImg: string = "/assets/images/timed-icon.png";
    protected villageImageUrl: string = "./assets/images/list-village.png";

    constructor(
        protected router: Router,
        protected cookieService: CookieService,
        protected loader: Loader,
        protected lessonService: LessonsService,
        protected reviewService: ReviewService,
        protected localStorage: LocalStorageService,
        protected forumService: ForumService,
    ) {
        this.subscribeToLastActiveUnitOrReview();

        this.subscribeToUnitReview();
    }

    ngOnInit() {
        this.subscribeToCurrentLevel();

        this.cookieService
            .get("AuthUser")
            .then((value) => {
                if (value == "") {
                    throw value;
                }
                this.user = JSON.parse(value);
            })
            .catch((err) => {
                console.warn("No AuthUser cookie", err);
            });
    }

    ngOnDestroy() {
        this.levelSubscription.unsubscribe();
        this.unitReviewSubscription.unsubscribe();
        this.lastActiveUnitOrReviewSubscription.unsubscribe();
    }

    protected subscribeToCurrentLevel() {
        this.levelSubscription = this.lessonService.currentLevelDetails.subscribe((level) => {
            if (level && Object.keys(level).length > 0) {
                this.level = level;
                this.promoteNextActivity();
                if (level.id == this.localStorage.getItem("CurrentLevelID")) {
                    // this.scrollUnits(false);
                } else {
                    // this.scrollUnits(true);
                }
            }
        });
    }

    protected subscribeToLastActiveUnitOrReview() {
        // If this gets trigged, go to the last place the user left off, based on whether it's
        // a unit lesson or review and the unit id.
        this.lastActiveUnitOrReviewSubscription = this.lessonService.lastActiveUnitOrReview.subscribe((params) => {
            if (!params.unit || !params.type) {
                return;
            }
            if (params.type == "unit") {
                this.goToLesson(params.unit);
            } else if (params.type == "review") {
                this.review(params.unit);
            } else {
                console.error("Error: unhandled type for last active unit or review: " + params.type);
            }
        });
    }

    protected subscribeToUnitReview() {
        this.unitReviewSubscription = this.lessonService.unitReview.subscribe((fireData) => {
            if (fireData && Object.keys(fireData).length > 0) {
                this.fireData = fireData;
            }
        });
    }

    scrollUnits(top) {
        //only here for logging, to see whats going on in developer console
        const increm = 1;
        let idx = 0;
        const interval = setInterval(checkDOM, 1000);
        //wait till we have DOM unit contents, then scroll to 'current' unit
        function checkDOM() {
            if (document.querySelectorAll(".ng-unlocked-unit").length) {
                if (top) {
                    document.querySelector(".units-container").scrollTo(0, 0);
                } else {
                    const unlockedUnitList = document.querySelectorAll(".ng-unlocked-unit");
                    idx = unlockedUnitList.length - 1;
                    unlockedUnitList[idx].scrollIntoView({ behavior: "smooth", block: "nearest", inline: "start" });
                }
                clearInterval(interval);
            } else if (increm > 100) {
                clearInterval(interval);
            }
        }
    }

    goToLesson(unit) {
        /*** breadcrumb code start***/
        const getbreadcrumb = localStorage.getItem("breadcrumb");
        let params: any = [];
        if (getbreadcrumb) {
            params = JSON.parse(getbreadcrumb);
            params[2] = {
                ID: unit.id,
                Name: unit.name,
                URL: "/lessons-and-exercises",
            };
        }
        // this.localStorage.setItem('breadcrumb', JSON.stringify(params));
        this.reviewService.setBreadcrumb(params);
        /*** breadcrumb code end***/

        this.lessonService.setUnit(unit);
        this.localStorage.setItem("unitID", unit.id);
        this.localStorage.setItem("LevelID", this.level.id);
        void this.router.navigate(["/lessons-and-exercises"]);
    }
    //this function serves to highlight needed review and should eventually contain other needed processing for flagging next activity.
    promoteNextActivity() {
        for (let i = 0; i < this.level.units.length; i++) {
            if (
                (this.level.units[i + 1] == null || this.level.units[i + 1]["enable"] == false) &&
                this.level.units[i]["unitPercentage"] >= 100
            ) {
                this.level.units[i]["flagNeedsReview"] = true;
                break;
            }
        }
    }

    review(unit) {
        /*** breadcrumb code start***/
        const getbreadcrumb = localStorage.getItem("breadcrumb");
        let params: any = [];
        if (getbreadcrumb) {
            params = JSON.parse(getbreadcrumb);
            params[2] = {
                ID: unit.id,
                Name: unit.name,
                URL: "/lessons-and-exercises",
            };
            params[3] = {
                ID: unit.id,
                Name: "Review",
                URL: "/review",
            };
        }

        // this.localStorage.setItem('breadcrumb', JSON.stringify(params));

        this.reviewService.setBreadcrumb(params);
        /*** breadcrumb code end***/

        this.localStorage.setItem("unitID", unit.id);
        // this.reviewService.setReviewProgress({});
        this.setReviewProgress(unit.id);
        this.reviewService.setUnit({ unit_id: unit.id });
        void this.router.navigate(["review"]);
    }

    private setReviewProgress(unitId) {
        /* Set parameters to send to API */
        const params: any = { user_id: this.user.id };
        if (unitId) {
            params.unit_id = unitId;
            this.localStorage.setItem("reviewUnit", unitId);

            /* Retrieve review score data from API */
            this.reviewService
                .getReviewScore(params)
                .then((res) => {
                    /* Alert others of new data */
                    if (res && res.data && res.data.results) {
                        res.data.results.showModal = true;
                        this.reviewService.setReviewProgress({ progressValue: res.data.results });
                    } else {
                        console.error("Error: unable to set boolean to show progress bar. res.data.results not valid.");
                    }
                })
                .catch((err) => {
                    console.error(err);
                });
        } else {
            /* In lesson (global) review, so don't show progress bar */
        }
    }

    setForumParams(unit) {
        this.localStorage.removeItem("forumId");
        const params = {
            path_id: this.user.learningpath_id,
            level_id: this.level.id,
            unit_id: unit.id,
            user_id: this.user.id,
        };
        this.forumService.setForumParams(params);
        void this.router.navigate(["/village"]);
    }

    lockedContinueIconClicked(releaseDate = null) {
        if (!!releaseDate) {
            void Swal.fire(environment.SITE_NAME, "This unit is locked until " + releaseDate.substring(0, 10), "error");
        } else {
            void Swal.fire(
                environment.SITE_NAME,
                "This unit is locked. In order to unlock this unit you must " +
                    "complete the previous lesson with all questions answered " +
                    "correctly or repeated 3 times. You must also complete 25 " +
                    "review questions in the unit review.",
                "error",
            );
        }
    }

    getContinueUnitIcon(unit) {
        if (unit.flagIsScheduled) {
            return this.continueUnitScheduledImg;
        } else if (this.unitEnabled(unit)) {
            return this.continueUnitEnabledImg;
        } else {
            return this.continueUnitLockedImg;
        }
    }

    getContinueIconTooltip(unit) {
        if (unit.flagIsScheduled) {
            return this.trimDate(unit.classroomLevelUnits.release_date);
        } else {
            return "Start Unit";
        }
    }

    continueClicked(unit) {
        if (this.unitScheduled(unit)) {
            this.lockedContinueIconClicked(unit.classroomLevelUnits.release_date);
        } else if (this.unitEnabled(unit)) {
            this.goToLesson(unit);
        } else {
            this.lockedContinueIconClicked();
        }
    }

    trimDate(date) {
        return date.substring(0, 10);
    }

    unitScheduled(unit) {
        return !!unit.flagIsScheduled;
    }

    unitEnabled(unit) {
        return unit.enable && !this.unitScheduled(unit);
    }

    unitDisabled(unit) {
        return !unit.enable && !this.unitScheduled(unit);
    }

    unitInactive(unit) {
        return !!unit.flagInactive;
    }

    getReviewImageUrl(percent) {
        const prefix = "./assets/images/";
        if (percent == 0) {
            return prefix + "fire_dead.png";
        } else if (percent < 20) {
            return prefix + "fire_low.png";
        } else if (percent < 60) {
            return prefix + "fire_med.png";
        } else if (percent < 90) {
            return prefix + "fire_high.png";
        } else {
            return prefix + "fire_ulta.png";
        }
    }

    getVillageImageUrl() {
        return this.villageImageUrl;
    }

    unitNotCompleted(unit) {
        return unit.unitPercentage < 100 || unit.flagIsScheduled;
    }
}
