import { Component, OnInit, OnDestroy } from "@angular/core";
import { Router } from "@angular/router";
import { Subscription } from "rxjs";

import { CookieService } from "app/_services/cookie.service";
import { Loader } from "app/_services/loader.service";
import { LocalStorageService } from "app/_services/local-storage.service";
import { RegistrationService } from "app/_services/registration.service";
import { SettingsService } from "app/_services/settings.service";
import { ReviewService } from "app/_services/review.service";
import { ForumService } from "app/_services/forum.service";
import { environment } from "environments/environment";
import { LocalizeService } from "app/_services/localize.service";

@Component({
    selector: "app-dashboard",
    templateUrl: "./dashboard.component.html",
    styleUrls: ["./dashboard.component.scss"],
})
export class DashboardComponent implements OnInit, OnDestroy {
    private userSubscription: Subscription;
    public user: any = {};
    public timeZoneOffset: number = 0;
    public fireData: any = {};
    public fireImage: string = "dead";
    public pathMessage: string =
        "Thanks for joining " +
        environment.SITE_NAME +
        ". Your " +
        environment.LANGUAGE_NATIVE +
        " learning journey begins now.";
    public showImages: boolean = false;
    public translations: any = null;

    constructor(
        private router: Router,
        private registrationService: RegistrationService,
        private cookieService: CookieService,
        private loader: Loader,
        private settingsService: SettingsService,
        private localStorage: LocalStorageService,
        private localizeService: LocalizeService,
        private reviewService: ReviewService,
        private forumService: ForumService,
    ) {
        this.userSubscription = this.registrationService.currentUser.subscribe((user) => (this.user = user));

        this.cookieService
            .get("AuthUser")
            .then((value: string) => {
                if (value == "") {
                    throw value;
                } else {
                    this.user = JSON.parse(value);
                }
            })
            .catch((err) => {
                console.info("[dashboard] Error getting AuthUser cookie. ", err);
                void this.router.navigate([""]);
            });
        this.localizeService.getTranslations().subscribe((data) => {
            this.translations = data["components"]["dashboard"];
        });
    }

    private setLoader(val: boolean) {
        this.loader.setLoader(val);
    }

    ngOnInit() {
        this.cookieService
            .get("AuthUser")
            .then((value) => {
                if (value == "") {
                    throw value;
                }
                this.settingsService
                    .getAllSettings(this.user.id)
                    .then((res) => {
                        this.setLoader(false);
                        if (res.data.status) {
                            this.user = res.data.results[0];
                            const timeZoneOffset = new Date().getTimezoneOffset() * 60;
                            this.timeZoneOffset =
                                timeZoneOffset > 0 ? -Math.abs(timeZoneOffset) : Math.abs(timeZoneOffset);
                            this.globalFire();
                        } else {
                            void this.alreadyDeleted();
                        }
                    })
                    .catch((err) => {
                        this.setLoader(false);
                        if (!err.ok) {
                            void this.alreadyDeleted();
                        }
                    });
            })
            .catch((err) => {
                console.error("[dashboard] No AuthUser cookie set. ", err);
            });
    }

    ngOnDestroy() {
        this.userSubscription.unsubscribe();
    }

    private globalFire() {
        const params = {
            user_id: this.user.id,
            type: "nextuse",
            timestamp_offset: this.timeZoneOffset,
        };
        this.reviewService
            .globalFire(params)
            .then((res) => {
                if (res.data.status) {
                    this.getFireData();
                }
            })
            .catch(() => {
                console.error("[dashboard] Error getting global fire data.");
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
                    this.showImages = true;
                    if (this.fireData.haveReviewExercise) {
                        this.reviewService.setReviewMenu(true);
                        this.setFireImage();
                        this.setPathMessage();
                    }
                }
            })
            .catch((err) => {
                console.error("[dashboard] Error getting fire data. ", err);
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
    private setPathMessage() {
        if (
            !!this.fireData.progressData &&
            this.fireData.progressData.pathTimeMinute > this.fireData.progressData.reviewTimeMinute
        ) {
            if (this.fireImage == "low") {
                this.pathMessage = "It is time to get serious. Maybe try some more REVIEW.";
            } else if (this.fireImage == "medium") {
                this.pathMessage = "Your streak is building! Keep going with some more REVIEW.";
            } else if (this.fireImage == "high") {
                this.pathMessage = "You have been working hard! Time for more REVIEW.";
            } else if (this.fireImage == "ultra") {
                this.pathMessage = this.translations ? this.translations.pathMessageReviewFireUltra : "";
                this.pathMessage += " You are amazing! Continue the great work with some REVIEW.";
            } else {
                //assume dead
                this.pathMessage = "Welcome Back! Take some time to REVIEW what you've learned before continuing on.";
            }
        } else {
            if (this.fireImage == "low") {
                this.pathMessage = "We are glad you are learning with us. It's time to do more LESSONS";
            } else if (this.fireImage == "medium") {
                this.pathMessage = "You're really picking things up. Time to learn from more LESSONS in the path.";
            } else if (this.fireImage == "high") {
                this.pathMessage = this.translations ? this.translations.pathMessageLessonsFireHigh : "";
                this.pathMessage += " You're really doing your part. Click LEARN to continue.";
            } else if (this.fireImage == "ultra") {
                this.pathMessage = this.translations ? this.translations.pathMessageLessonsFireUltra : "";
                this.pathMessage += " You are on fire! Keep on LEARNING!";
            } else {
                //assume dead
                this.pathMessage = "Welcome Back! Looks like you're ready to take on some new LESSONS.";
            }
        }
    }

    private async alreadyDeleted() {
        try {
            await this.cookieService.deleteAll();
            this.localStorage.clear();
            setTimeout(() => {
                void this.router.navigate([""]);
            }, 1000);
        } catch (err) {
            console.error("[dashboard] Error deleting all cookies. ", err);
            this.localStorage.clear();
            setTimeout(() => {
                void this.router.navigate([""]);
            }, 1000);
        }
    }

    goToPage(slug) {
        void this.router.navigate([slug]);
        if (slug === "review") {
            this.reviewService.setReviewProgress({});
            localStorage.removeItem("unitID");
            localStorage.removeItem("reviewUnit");
            const breadcrumb = [
                {
                    Name: this.user.learningpath.label,
                    URL: "/dashboard",
                },
                {
                    Name: "Review",
                    URL: "/review",
                },
            ];
            // this.localStorage.setItem('breadcrumb', JSON.stringify(breadcrumb));
            this.reviewService.setBreadcrumb(breadcrumb);
        }
    }

    goToVillage() {
        this.localStorage.removeItem("forumId");
        const params = {
            path_id: this.user.learningpath_id,
            user_id: this.user.id,
        };
        this.forumService.setForumParams(params);
        this.goToPage("village");
    }
}
