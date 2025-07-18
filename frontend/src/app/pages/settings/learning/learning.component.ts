import { Component, OnInit, ChangeDetectorRef, OnDestroy } from "@angular/core";
import { Router } from "@angular/router";
import { Subscription } from "rxjs";
import { CookieService } from "app/_services/cookie.service";

import { LearningPathService } from "app/_services/learning-path.service";
import { LearningSpeedService } from "app/_services/learning-speed.service";
import { Loader } from "app/_services/loader.service";
import { LocalStorageService } from "app/_services/local-storage.service";
import { SettingsService } from "app/_services/settings.service";
import { environment } from "environments/environment";
import { BaseService } from "app/_services/base.service";
import { SnackbarService } from "app/_services/snackbar.service";

@Component({
    selector: "app-learning",
    templateUrl: "./learning.component.html",
    styleUrls: ["./learning.component.scss"],
})
export class LearningComponent implements OnInit, OnDestroy {
    private lockSubscription: Subscription;
    public learningSpeeds: any = [];
    public learningPaths: any = [];
    public user: any;
    public currentPath: any;
    public pathModel: any = {};
    public lockFlag: boolean = false;
    public environment = environment;

    constructor(
        private settingsService: SettingsService,
        private router: Router,
        private loader: Loader,
        private ref: ChangeDetectorRef,
        private cookieService: CookieService,
        public learningSpeedService: LearningSpeedService,
        private learningPathService: LearningPathService,
        private baseService: BaseService,
        private snackbarService: SnackbarService,
        private localStorage: LocalStorageService,
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

        this.lockSubscription = this.settingsService.parentalLockCode.subscribe(() => (this.lockFlag = false));
        this.settingsService.setTab("learning");
    }

    ngOnInit() {
        this.cookieService
            .get("AuthUser")
            .then((value) => {
                if (value == "") {
                    throw value;
                }
                this.setLoader(true);
                const loggedInUser = JSON.parse(value);
                this.settingsService
                    .getAllSettings(loggedInUser.id)
                    .then((res) => {
                        this.setLoader(false);
                        if (res.data.status) {
                            this.user = res.data.results[0];

                            const parentalLock = this.localStorage.getItem("parentalLockCode");
                            if (this.user.usersetting.parental_lock_on == "1") {
                                this.lockFlag = this.user.usersetting.parental_lock
                                    ? parentalLock == this.user.usersetting.parental_lock
                                        ? false
                                        : true
                                    : false;
                            }
                            this.getLearningPaths();
                            this.getLearningSpeeds();
                        } else {
                            console.error("[learning] Error in user settings result. ", res);
                            this.alreadyDeleted();
                        }
                    })
                    .catch((err) => {
                        this.setLoader(false);
                        if (!err.ok) {
                            console.error("[learning] Error getting user settings. ", err);
                            this.alreadyDeleted();
                        }
                    });
            })
            .catch((err) => {
                console.info("[learning] Error getting AuthUser cookie. ", err);
            });
    }

    ngOnDestroy() {
        this.lockSubscription.unsubscribe();
    }

    private setLoader(val: boolean) {
        this.loader.setLoader(val);
    }

    private alreadyDeleted() {
        void this.cookieService.deleteAll();
        this.localStorage.clear();
        setTimeout(() => {
            void this.router.navigate([""]);
        }, 1000);
    }

    getLearningPaths() {
        this.setLoader(true);

        this.learningPathService
            .getLearningPaths({ user_id: this.user.id })
            .then((userLearningPaths) => {
                this.learningPaths = userLearningPaths;
                this.learningPaths.forEach((element) => {
                    if (this.user.learningpath_id == element.id) {
                        this.currentPath = element;
                    }
                });
            })
            .catch((err) => {
                console.error("[learning] Error getting learning paths. ", err);
            })
            .finally(() => {
                this.setLoader(false);
            });
    }

    getLearningSpeeds() {
        this.setLoader(true);
        this.learningSpeedService
            .getLearningSpeed()
            .then((res) => {
                this.learningSpeeds = res.data.results;
            })
            .catch((err) => {
                console.error("[learning] Error getting learning speeds. ", err);
            })
            .finally(() => {
                this.setLoader(false);
            });
    }

    pathChanged(path) {
        this.setLoader(true);
        this.currentPath = path;
        const postData = {
            id: this.user.id,
            learningpath_id: path.id,
        };
        this.learningPathService
            .setLearningPath(postData)
            .then(async (res) => {
                this.setLoader(false);
                this.user = res.data.results[0];
                try {
                    await this.baseService.setAuthUserCookie(this.user);
                } catch (err) {
                    this.snackbarService.handleError(err, "Error setting user cookie.");
                }
                this.localStorage.removeItem("LevelID");
                this.settingsService.setUser(res.data.results[0]);
            })
            .catch((err) => {
                console.error("[learning] Error setting learning path. ", err);
                this.setLoader(false);
            });
    }

    speedChanged(speed) {
        this.setLoader(true);
        const postData = {
            id: this.user.id,
            learningspeed_id: speed.id,
        };
        this.learningSpeedService
            .setLearningSpeed(postData)
            .then(async (res) => {
                this.setLoader(false);
                this.user = res.data.results[0];
                try {
                    await this.baseService.setAuthUserCookie(this.user);
                } catch (err) {
                    this.snackbarService.handleError(err, "Error setting user cookie.");
                }
            })
            .catch((err) => {
                console.error("[learning] Error setting learning speed. ", err);
                this.setLoader(false);
            });
    }
}
