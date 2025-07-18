import { Component, OnInit, OnDestroy } from "@angular/core";
import { Router } from "@angular/router";
import { Subscription } from "rxjs";

import { CookieService } from "app/_services/cookie.service";
import { Loader } from "app/_services/loader.service";
import { LocalStorageService } from "app/_services/local-storage.service";
import { SettingsService } from "app/_services/settings.service";
import { BadgeService } from "app/_services/badge.service";
import { SnackbarService } from "app/_services/snackbar.service";
import { BaseService } from "app/_services/base.service";

@Component({
    selector: "app-profile",
    templateUrl: "./profile.component.html",
    styleUrls: ["./profile.component.scss"],
})
export class ProfileComponent implements OnInit, OnDestroy {
    private lockSubscription: Subscription;
    public lockFlag: boolean = false;
    public user: any;
    public profile: any = {};
    public levelbadgeFlag: boolean = false;

    constructor(
        private settingsService: SettingsService,
        private loader: Loader,
        private router: Router,
        private cookieService: CookieService,
        private localStorage: LocalStorageService,
        private badgeService: BadgeService,
        private snackbarService: SnackbarService,
        private baseService: BaseService,
    ) {
        this.cookieService
            .get("AuthToken")
            .then((value) => {
                if (!value) {
                    throw new Error("Empty AuthToken cookie");
                }
            })
            .catch((err) => {
                console.error(err);
                void this.router.navigate([""]);
            });

        this.lockSubscription = this.settingsService.parentalLockCode.subscribe(() => (this.lockFlag = false));
        this.settingsService.setTab("profile");
    }

    private setLoader(val: boolean) {
        this.loader.setLoader(val);
    }

    private async alreadyDeleted() {
        try {
            await this.cookieService.deleteAll();
        } catch (err) {
            console.error("[alreadyDeleted] ", err);
        }
        this.localStorage.clear();
        setTimeout(() => {
            void this.router.navigate([""]);
        }, 1000);
    }

    ngOnInit() {
        this.cookieService
            .get("AuthUser")
            .then((value) => {
                if (!value) {
                    throw new Error("Empty AuthUser cookie");
                }
                const loggedInUser = JSON.parse(value);
                this.setLoader(true);
                // let loggedInUser = JSON.parse(this.cookieService.get('AuthUser'));
                this.settingsService
                    .getAllSettings(loggedInUser.id)
                    .then((res) => {
                        this.setLoader(false);
                        if (res.data.status) {
                            this.user = res.data.results[0];

                            setTimeout(() => {
                                this.settingsService.setGalleryImages(this.user.userimages);
                            }, 200);

                            const parentalLock = this.localStorage.getItem("parentalLockCode");
                            if (this.user.usersetting.parental_lock_on == "1") {
                                this.lockFlag = this.user.usersetting.parental_lock
                                    ? parentalLock == this.user.usersetting.parental_lock
                                        ? false
                                        : true
                                    : false;
                            }

                            this.profile.aboutMe = this.user.usersetting.profile_desc;
                        } else {
                            void this.alreadyDeleted();
                        }
                    })
                    .catch((err) => {
                        console.error(err);
                    });
            })
            .catch((err) => {
                console.error(err);
            });
    }

    ngOnDestroy() {
        this.lockSubscription.unsubscribe();
    }
    imgfunc(allbadge: any, type: string) {
        const imageUrl = this.badgeService.badgeImgSet(allbadge, type);
        return imageUrl;
    }
    setProfileDetail(type: string) {
        let data = {};
        this.setLoader(true);
        switch (type) {
            case "about":
                data = {
                    id: this.user.id,
                    profile_desc: this.profile.aboutMe,
                };
                break;
            default:
                console.error("Unhandled setProfileDetail() type: ", type);
                this.snackbarService.showSnackbar({
                    status: false,
                    msg: "Unhandled type. Contact tech support",
                });
                return;
        }
        this.settingsService
            .updateUserSettings(data)
            .then(async (res) => {
                this.setLoader(false);
                this.snackbarService.showSnackbar({
                    status: res.data.status,
                    msg: res.data.message,
                });
                if (!res.data.status) {
                    return;
                }
                this.user = res.data.results[0];
                try {
                    await this.baseService.setAuthUserCookie(this.user);
                } catch (err) {
                    this.snackbarService.handleError(err, "Error setting user cookie.");
                }
            })
            .catch((err) => {
                console.error("Error with updateUserSettings: ", err);
                this.setLoader(false);
            });
    }

    profilePicUpload($event) {
        if ($event.target.files.length <= 0) {
            console.error("No file selected.");
            this.snackbarService.showSnackbar({
                status: false,
                msg: "No file selected.",
            });
            return;
        }
        this.setLoader(true);

        // Create form data from form elements
        const file = $event.target.files[0];
        const formData = new FormData();
        formData.append("profile_picture", file, file.name);
        formData.append("id", this.user.id);

        this.settingsService
            .updateUserImage(formData)
            .then(async (res: any) => {
                if (!res.data?.status || !res.data?.results) {
                    throw new Error(
                        res.data?.message
                            ? res.data.message
                            : "Profile update failed. Contact tech support",
                    );
                }
                this.setLoader(false);
                this.user = res.data.results[0];
                this.settingsService.setUser(this.user);
                try {
                    await this.baseService.setAuthUserCookie(this.user);
                } catch (err) {
                    this.snackbarService.handleError(err, "Error setting user cookie.");
                }
            })
            .catch((err) => {
                this.setLoader(false);
                console.error("Profile update failed: ", err);
                this.snackbarService.showSnackbar({
                    status: false,
                    msg: "Profile update failed",
                });
            });
    }
}
