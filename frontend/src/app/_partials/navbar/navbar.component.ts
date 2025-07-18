import { Component, OnInit, OnDestroy, ElementRef, ViewChild, AfterViewInit } from "@angular/core";
import { Router, ActivatedRoute, NavigationEnd } from "@angular/router";
import { Subscription } from "rxjs";

import { CookieService } from "app/_services/cookie.service";
import { RegistrationService } from "app/_services/registration.service";
import { SettingsService } from "app/_services/settings.service";
import { LocalStorageService } from "app/_services/local-storage.service";
import { ReviewService } from "app/_services/review.service";
import { BaseService } from "app/_services/base.service";
import { ForumService } from "app/_services/forum.service";
import { environment } from "environments/environment";
import { SiteSettingsService } from "app/_services/site-settings.service";
import { VirtualKeyboardService } from "app/_services/virtual-keyboard.service";
import { Routes } from "app/shared/utils/elearning-types";
import { RegionPolicyService } from "app/_services/region-policy.service";
import { PlatformRolesService } from "app/_services/platform-roles.service";

declare let jQuery: any;

@Component({
    selector: "app-navbar",
    templateUrl: "./navbar.component.html",
    styleUrls: ["./navbar.component.scss"],
})
export class NavbarComponent implements OnInit, OnDestroy, AfterViewInit {
    public environment = environment;
    public loggedIn: boolean = false;
    public showSignIn: boolean = false;
    public user: any;
    public currentRoute: string;
    public showReview: boolean = false;
    public showSignUpBtns: boolean = false;
    public expiredToken = false;
    private settings: any = null;
    public features: any = null;
    public Routes = Routes;

    public currentUserSub: Subscription;
    public userObjSub: Subscription;
    public showReviewSub: Subscription;
    public logOutSub: Subscription;

    @ViewChild("mainDiv") mainDiv: ElementRef;

    constructor(
        public router: Router,
        private registrationService: RegistrationService,
        private cookieService: CookieService,
        private settingsService: SettingsService,
        public baseService: BaseService,
        private localStorage: LocalStorageService,
        private reviewService: ReviewService,
        private forumService: ForumService,
        private thisRoute: ActivatedRoute,
        private siteSettingsService: SiteSettingsService,
        private regionPolicyService: RegionPolicyService,
        private virtualKeyboardService: VirtualKeyboardService,
        public platformRolesService: PlatformRolesService,
    ) {
        this.currentUserSub = this.registrationService.currentUser.subscribe((user) => {
            if (user && Object.keys(user).length > 0) {
                void this.setUser(user);
            }
        });

        this.userObjSub = this.settingsService.userObj.subscribe((user) => {
            if (user && Object.keys(user).length > 0) {
                void this.setUser(user);
            }
        });

        this.siteSettingsService
            .getSettings()
            .then((settings) => {
                this.settings = settings;
            })
            .catch((err) => {
                console.error(err);
            });

        this.siteSettingsService
            .getFeatures()
            .then((features) => {
                this.features = features;
            })
            .catch((err) => {
                console.error(err);
            });

        this.showReviewSub = this.reviewService.reviewMenu.subscribe((show) => {
            if (show) {
                this.showReview = true;
            }
        });

        this.logOutSub = this.baseService.loginStatus.subscribe((res) => {
            if (res && Object.keys(res).length > 0 && res.loggedOut) {
                this.loggedIn = false;
                this.setButtonVisibility(this.currentRoute);
                void this.router.navigate([Routes.Login]);
            }
        });

        this.cookieService
            .get("AuthUser")
            .then((value) => {
                if (!!value) {
                    this.getUserSettings();
                } else {
                    throw value;
                }
            })
            .catch(() => {
                this.setButtonVisibility(this.currentRoute);
            });
    }

    ngOnInit() {
        this.thisRoute.queryParamMap.subscribe((queryParams) => {
            if (queryParams.get("expiredToken")) {
                // User logged out due to error. Reset navbar buttons
                this.loggedIn = false;
                this.setButtonVisibility(this.currentRoute);
                this.expiredToken = true;
                setTimeout(() => {
                    this.expiredToken = false;
                }, 3000);
            }
        });
    }

    ngAfterViewInit() {
        this.virtualKeyboardService.navbarHeightChanged(this.mainDiv.nativeElement.clientHeight);

        this.router.events.subscribe((event) => {
            if (event instanceof NavigationEnd) {
                // Get the current route and omit the leading slash, so it aligns with our Routes constants
                this.currentRoute = event.urlAfterRedirects.startsWith("/")
                    ? event.urlAfterRedirects.substring(1)
                    : event.urlAfterRedirects;
                this.setButtonVisibility(this.currentRoute);
                if (event.url === Routes.Login && !this.loggedIn) {
                    // Don't check AuthUser, since the user is not logged in.
                    // This will avoid confusing warnings in the console.
                    return;
                }
                this.cookieService
                    .get("AuthUser")
                    .then((value: string) => {
                        if (!!value) {
                            this.getUserSettings(true);
                        }
                    })
                    .catch((err) => {
                        console.info("Error getting AuthUser cookie. ", err);
                    });
            }
        });
    }

    ngOnDestroy() {
        this.currentUserSub.unsubscribe();
        this.userObjSub.unsubscribe();
        this.showReviewSub.unsubscribe();
        this.logOutSub.unsubscribe();
    }

    /**
     * Checks if the user is allowed to access the leaderboard.
     *
     * @returns {boolean} True if the user is allowed to access the leaderboard, false otherwise.
     */
    canAccessLeaderboard(): boolean {
        return (
            this.regionPolicyService.isAdult(this.user?.approximate_age) ||
            (this.settings?.setting_minors_can_access_leaderboard === "1" &&
                !this.regionPolicyService.isBetweenChildAndAdult(this.user?.approximate_age))
        );
    }

    /**
     * Checks if the user is allowed to access the village.
     *
     * @returns {boolean} True if the user is allowed to access the village, false otherwise.
     */
    public canAccessVillage(): boolean {
        return (
            this.regionPolicyService.isAdult(this.user?.approximate_age) ||
            (this.settings?.setting_minors_can_access_village === "1" &&
                !this.regionPolicyService.isBetweenChildAndAdult(this.user?.approximate_age))
        );
    }

    private setButtonVisibility(currentRoute: string) {
        switch (currentRoute) {
            case Routes.Register:
                this.showSignIn = true;
                this.showSignUpBtns = false;
                break;
            case Routes.Login:
                this.showSignIn = false;
                this.showSignUpBtns = true;
                break;
            default:
                this.showSignIn = false;
                break;
        }
    }

    private async setUser(user: any) {
        this.loggedIn = true;
        this.user = user;
        let authToken = null;
        try {
            authToken = await this.cookieService.get("AuthToken");
            if (!authToken) {
                throw new Error("No AuthToken");
            }
        } catch (err) {
            console.error("Error getting AuthToken. ", err);
        }

        if (!!authToken) {
            this.getReviewData();
        }
    }

    private getUserSettings(type: boolean = false) {
        this.cookieService
            .get("AuthUser")
            .then((value) => {
                if (!value) {
                    return;
                }
                const loggedInUser = JSON.parse(value);

                this.settingsService
                    .getAllSettings(loggedInUser.id)
                    .then(async (res) => {
                        if (res.data.status) {
                            const authUser = Object.assign({}, res.data.results[0]);
                            delete authUser.userimages;
                            jQuery(".dropdown-toggle").dropdown();
                            try {
                                await this.baseService.setAuthUserCookie(authUser);
                            } catch (err) {
                                console.debug("Error setting AuthUser cookie. ", err);
                                return this.baseService.logout();
                            }
                            if (type) {
                                if (res.data.results[0].is_active == 0) {
                                    console.error("User is not active");
                                    return this.baseService.logout();
                                }
                            } else {
                                this.loggedIn = true;
                                this.user = res.data.results[0];
                                this.getReviewData();
                            }
                        } else {
                            console.error("Error getting user settings. ", res.data.message);
                            return this.baseService.logout();
                        }
                    })
                    .catch((err) => {
                        if (!err.ok) {
                            console.error("Error getting user settings. ", err);
                            return this.baseService.logout();
                        }
                    });
            })
            .catch((err) => {
                console.error(err);
                void this.baseService.logout();
            });
    }

    private getReviewData() {
        this.reviewService
            .getFire({ user_id: this.user.id })
            .then((res) => {
                if (!res.data) {
                    throw new Error(res);
                }
                if (res.data.status && res.data.results.haveReviewExercise) {
                    this.showReview = true;
                }
            })
            .catch((err) => {
                console.error(err);
            });
    }

    goToVillage() {
        this.localStorage.removeItem("forumId");
        const params: any = {
            path_id: this.user.learningpath_id,
            user_id: this.user.id,
        };
        if (this.currentRoute == Routes.StartLearning) {
            params.level_id = parseInt(this.localStorage.getItem("LevelID"));
            params.labelType = "levelfetch";
        }

        if (this.currentRoute == Routes.LessonsAndExercises) {
            params.level_id = parseInt(this.localStorage.getItem("LevelID"));
            params.unit_id = parseInt(this.localStorage.getItem("unitID"));
        }

        this.forumService.setForumParams(params);
        void this.router.navigate([Routes.Village]);
    }

    goToTeacher() {
        //do nothing
    }

    goToClassroom() {
        //do nothing
    }

    async gotoUrlOther(url: any) {
        window.scroll(0, 0);
        localStorage.removeItem("breadcrumb");
        if (url === Routes.Review) {
            this.reviewService.setReviewProgress({});
            localStorage.removeItem("unitID");
            localStorage.removeItem("reviewUnit");
            this.localStorage.setItem("isClassroom", 0);
            if (!this.user.learningpath?.label) {
                console.error("User learning path not found");
                await this.baseService.logout();
                void this.router.navigate([""]);
                return;
            }

            const breadcrumb = [
                {
                    Name: this.user.learningpath.label,
                    URL: Routes.Dashboard,
                },
                {
                    Name: "Review",
                    URL: Routes.Review,
                },
            ];
            // this.localStorage.setItem('breadcrumb', JSON.stringify(breadcrumb));
            this.reviewService.setBreadcrumb(breadcrumb);
        }
        if (this.user) {
            void this.router.navigate([url]);
        } else {
            void this.router.navigate([Routes.Login]);
        }
    }
    createFname(name: string) {
        const myname = name.split(" ");
        if (myname.length > 0) {
            return myname[0];
        } else {
            return "default";
        }
    }

    openDoorbellModal() {
        jQuery("#doorbell-button").click();
    }

    goToPrivacyPolicy() {
        void this.router.navigate([Routes.AboutPrivacy]);
    }

    goToTerms() {
        void this.router.navigate([Routes.AboutTerms]);
    }
}
