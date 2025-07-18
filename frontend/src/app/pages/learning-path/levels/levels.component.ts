import { Component, OnInit, OnDestroy } from "@angular/core";
import { CookieService } from "app/_services/cookie.service";
import { Router } from "@angular/router";
import { Subscription } from "rxjs";

import { Loader } from "app/_services/loader.service";
import { LearningPathService } from "app/_services/learning-path.service";
import { LessonsService } from "app/_services/lessons.service";
import { LocalStorageService } from "app/_services/local-storage.service";
import { SettingsService } from "app/_services/settings.service";
import { ReviewService } from "app/_services/review.service";
import { RegionPolicyService } from "app/_services/region-policy.service";

@Component({
    selector: "app-levels",
    templateUrl: "./levels.component.html",
    styleUrls: ["./levels.component.scss"],
})
export class LevelsComponent implements OnInit, OnDestroy {
    public user: any = {};
    public path: any = {};
    public allLevels: any = [];
    public currentLevel: any = {};
    public selectedLevel: number;
    public noLevel: boolean = false;
    public noPath: boolean = false;
    public timeZoneOffset: number = 0;
    public fireData: any = {};
    public noReviews: boolean = false;
    public fireImage: string = "dead";
    protected defaultLevelImageUrl: string = "./assets/images/menu-3.png";
    public levelSubscription: Subscription;

    constructor(
        private router: Router,
        private cookieService: CookieService,
        private loader: Loader,
        private lessonService: LessonsService,
        private settingsService: SettingsService,
        private localStorage: LocalStorageService,
        private reviewService: ReviewService,
        private learningPathService: LearningPathService,
        public regionPolicyService: RegionPolicyService,
    ) {
        this.cookieService
            .get("AuthToken")
            .then((value) => {
                if (value == "") {
                    throw value;
                }
            })
            .catch(() => {
                void this.router.navigate([""]);
            });

        this.levelSubscription = this.lessonService.currentLevel.subscribe((level) => (this.selectedLevel = level));

        if (this.localStorage.getItem("LevelID")) {
            this.selectedLevel = parseInt(this.localStorage.getItem("LevelID"));
        }
    }
    ngOnInit() {
        this.getUser();
        //reset unit persistence variables when navigating to new unit
        localStorage.removeItem("unitID");
        localStorage.removeItem("reviewUnit");
        this.localStorage.setItem("isClassroom", 0);
    }

    ngOnDestroy() {
        this.levelSubscription.unsubscribe();
    }

    private alreadyDeleted() {
        void this.cookieService.deleteAll();
        this.localStorage.clear();
        setTimeout(() => {
            void this.router.navigate([""]);
        }, 1000);
    }

    private getFireData() {
        this.reviewService
            .getFire({ user_id: this.user.id })
            .then((res) => {
                if (res.data.status) {
                    this.fireData = res.data.results;
                    this.lessonService.setUnitReview(this.fireData);
                    if (this.fireData.haveReviewExercise) {
                        this.reviewService.setReviewMenu(true);
                        this.setFireImage();
                    } else {
                        this.noReviews = true;
                    }
                }
            })
            .catch((err) => {
                console.error("[levels] Error getting fire data. ", err);
            })
            .finally(() => {
                this.loader.setLoader(false);
            });
    }

    private setFireImage() {
        this.fireImage = this.learningPathService.getFireTypeFromStreak(this.fireData.FireData.fire_days);
    }

    getReviewImageUrl(fireImage: string, noReviews: boolean = false) {
        return this.learningPathService.getReviewImageUrl(fireImage, noReviews);
    }

    updateLevelImageUrl(event, levelId) {
        for (let i = 0; i < this.allLevels.length; ++i) {
            if (this.allLevels[i].id == levelId) {
                this.allLevels[i].image.ResizeImageUrl = this.defaultLevelImageUrl;
            }
        }
    }

    getUser() {
        this.cookieService
            .get("AuthUser")
            .then((value) => {
                if (value == "") {
                    throw value;
                }
                this.loader.setLoader(true);
                const loggedInUser = JSON.parse(value);
                this.settingsService
                    .getAllSettings(loggedInUser.id)
                    .then((res) => {
                        if (res.data.status) {
                            this.user = res.data.results[0];
                            this.getFireData();
                            if (this.user.learningpath_id) {
                                this.noPath = false;
                                this.getLearningPath();
                            } else {
                                this.noPath = true;
                            }
                        } else {
                            console.error("[levels] Error in user settings result. ", res);
                            this.alreadyDeleted();
                        }
                    })
                    .catch((err) => {
                        if (!err.ok) {
                            console.error("[level] Error getting user settings. ", err);
                            this.alreadyDeleted();
                        }
                    })
                    .finally(() => {
                        this.loader.setLoader(false);
                    });
            })
            .catch((err) => {
                console.info("[level] Error getting AuthUser cookie. ", err);
            });
    }

    getLearningPath() {
        const params = {
            user_id: this.user.id,
            path_id: this.user.learningpath_id,
        };

        this.lessonService
            .getLearningPathDetails(params)
            .then((response: any) => {
                this.loader.setLoader(false);
                if (response.data.status) {
                    this.path = response.data.results;
                    if (this.path.levels.length > 0) {
                        this.noLevel = false;
                        if (!this.localStorage.getItem("LevelID") || !this.setLevelFromLocalStorage()) {
                            //set the last unlocked level to selected
                            this.path.levels.forEach((level) => {
                                this.allLevels.push(level);
                                if (level.enable) {
                                    this.selectedLevel = level;
                                    this.localStorage.setItem("CurrentLevelID", level.id);
                                }
                            });
                            this.setActiveLevel(this.selectedLevel);
                        } else {
                            this.path.levels.forEach((level) => {
                                this.allLevels.push(level);
                            });
                        }
                    } else {
                        this.noLevel = true;
                    }
                }
            })
            .catch((err) => {
                this.loader.setLoader(false);
                console.error("[levels] Error getting learning path details. ", err);
            });
    }

    setActiveLevel(level) {
        /*** breadcrumb code star***/
        const breadcrumb = [
            {
                Name: this.user.learningpath.label,
                URL: "/dashboard",
            },
            {
                ID: level.id,
                Name: level.name,
                URL: "/start-learning",
            },
        ];
        // this.localStorage.setItem('breadcrumb', JSON.stringify(breadcrumb));
        this.reviewService.setBreadcrumb(breadcrumb);
        /*** breadcrumb code end***/

        this.localStorage.setItem("LevelID", level.id);
        this.localStorage.removeItem("unitID");
        this.currentLevel = level;
        this.lessonService.setLevel(level);
    }

    setLevelFromLocalStorage() {
        const levelID: number = parseInt(this.localStorage.getItem("LevelID"));
        for (let i = 0; i < this.path.levels.length; i++) {
            const level = this.path.levels[i];
            if (level.id == levelID) {
                this.setActiveLevel(level);
                return true;
            }
        }

        return false;
    }

    /**
     * Finds where the user left off.
     * First find the furthest enabled unit. If it has been started then go to it,
     * if it has be completed then go to its review session, if it hasn't been
     * started, search backwards to see if there are optional units immediately preceeding
     * this unit that the user started. If so, if the unit isn't complete, then go to it.
     * If the unit is complete, since we don't have review completion info yet, just go
     * to the next unit.
     */
    goToWhereverUserLeftOff() {
        // Active level is set already in this class
        // Unit can be determined from path JSON
        // Review completion can be determined by looking at unit_N and unit_N+1 enable flags
        const units = this.currentLevel.units; // convenience variable
        let lastEnabledUnitIndex = -1; // index of last enabled unit
        let done = false; // double for loop break helper variable

        // Find latest unlocked unit
        for (const unit of units) {
            if (!unit.enable) {
                lastEnabledUnitIndex = Math.max(lastEnabledUnitIndex, 0);
                break;
            }
            ++lastEnabledUnitIndex;
        }

        // Figure out if user is in the lessons, the review or
        // started an optional unit before this unit
        for (let i = lastEnabledUnitIndex; i >= 0; --i) {
            const unit = units[i];
            if (unit.unitPercentage >= 100) {
                // review
                this.setLastUnitOrReview("review", unit);
                break;
            } else if (unit.unitPercentage > 0) {
                // latest obligatory unit
                this.setLastUnitOrReview("unit", unit);
                break;
            } else {
                // unit.unitPercentage <= 0
                // check for progress in optional units if
                // there are any directly preceeding the last enabled unit
                for (let j = i - 1; j >= 0; --j) {
                    if (j < 0) {
                        done = this.setLastUnitOrReview("unit", unit);
                        break;
                    }
                    const pastUnit = units[j];
                    if (pastUnit.optional) {
                        if (pastUnit.unitPercentage > 0 && pastUnit.unitPercentage < 100) {
                            // user started an optional unit and hasn't started an obligatory unit after it,
                            // so put them back into the optional unit that they didn't finish
                            done = this.setLastUnitOrReview("unit", pastUnit);
                            break;
                        } else if (pastUnit.unitPercentage >= 100) {
                            // either user needs to do optional unit review, or it's finished and
                            // they can continue to next unit. But we currently don't supply (FIXME)
                            // review complete info, so I will assume it's completed and we will
                            // throw them into the last enabled obligatory unit
                            done = this.setLastUnitOrReview("unit", unit);
                            break;
                        } else {
                            // then, optional unit wasn't attempted, so check the previous unit to
                            // see if it's optional.
                        }
                    } else {
                        // user at least completed one unit past any optional ones so throw them
                        // into the latest obligatory unit
                        done = this.setLastUnitOrReview("unit", unit);
                        break;
                    }
                }
            }

            if (done) {
                break;
            }
        }
    }

    /**
     * Set next last unit or review so unit.component goes there
     */
    setLastUnitOrReview(type, unit) {
        if (type != "unit" && type != "review") {
            return true;
        }
        const params: any = {
            type: type,
            unit: unit,
        };
        this.lessonService.setLastActiveUnitOrReview(params);
        return true;
    }

    goToVillage() {
        this.learningPathService.goToVillage(this.user.learningpath_id, this.user.id, this.currentLevel.id);
    }

    goToReview() {
        const breadcrumb = [
            {
                Name: this.user.learningpath.label,
                URL: "/dashboard",
            },
            {
                ID: this.currentLevel.id,
                Name: this.currentLevel.name,
                URL: "/start-learning",
            },
            {
                Name: "Review",
                URL: "/review",
            },
        ];
        this.reviewService.setBreadcrumb(breadcrumb);
        void this.router.navigate(["/review"]);
    }
}
