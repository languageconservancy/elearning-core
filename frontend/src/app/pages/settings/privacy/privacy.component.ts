import { Component, OnInit, OnDestroy } from "@angular/core";
import { UntypedFormGroup, UntypedFormControl, Validators } from "@angular/forms";
import { Subscription } from "rxjs";
import { CookieService } from "app/_services/cookie.service";
import { Router } from "@angular/router";

import { SettingsService } from "app/_services/settings.service";
import { Loader } from "app/_services/loader.service";
import { LocalStorageService } from "app/_services/local-storage.service";
import { environment } from "environments/environment";
import { RegionPolicyService } from "app/_services/region-policy.service";
import { BaseService } from "app/_services/base.service";
import { SnackbarService } from "app/_services/snackbar.service";

declare let jQuery: any;

@Component({
    selector: "app-privacy",
    templateUrl: "./privacy.component.html",
    styleUrls: ["./privacy.component.scss"],
})
export class PrivacyComponent implements OnInit, OnDestroy {
    public environment = environment;
    private lockSubscription: Subscription;
    public user: any;
    public lockFlag: boolean = false;
    public lockEditFlag: boolean = false;
    public privacy: any = {};
    public parentalForm: UntypedFormGroup;
    public parentalEditForm: UntypedFormGroup;

    constructor(
        private settingsService: SettingsService,
        private loader: Loader,
        private router: Router,
        private cookieService: CookieService,
        private localStorage: LocalStorageService,
        private regionPolicyService: RegionPolicyService,
        private baseService: BaseService,
        private snackbarService: SnackbarService,
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
        this.settingsService.setTab("privacy");
    }

    ngOnInit() {
        this.parentalForm = new UntypedFormGroup({
            // eslint-disable-next-line @typescript-eslint/unbound-method
            parentalLock: new UntypedFormControl("", Validators.required),
        });

        this.setLoader(true);
        this.getSettings();
        this.parentalEditForm = new UntypedFormGroup({
            // eslint-disable-next-line @typescript-eslint/unbound-method
            parentalLockOld: new UntypedFormControl("", [Validators.required, this.validateParentalLock.bind(this)]),
            // eslint-disable-next-line @typescript-eslint/unbound-method
            parentalLockNew: new UntypedFormControl("", Validators.required),
        });
    }

    ngOnDestroy() {
        this.lockSubscription.unsubscribe();
    }

    private validateParentalLock(control: UntypedFormControl): any {
        if (this.user) {
            return control.value === this.user.usersetting.parental_lock ? null : { notSame: true };
        }
    }

    getSettings() {
        this.cookieService
            .get("AuthUser")
            .then((value) => {
                if (value == "") {
                    throw value;
                }
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
                                    ? parentalLock && parentalLock == this.user.usersetting.parental_lock
                                        ? false
                                        : true
                                    : false;
                            }

                            this.setUpModels();
                        } else {
                            console.error("[privacy] Error in user settings result. ", res);
                            this.alreadyDeleted();
                        }
                    })
                    .catch((err) => {
                        this.setLoader(false);
                        if (!err.ok) {
                            console.error("[privacy] Error getting user settings. ", err);
                            this.alreadyDeleted();
                        }
                    });
            })
            .catch((err) => {
                console.info("[privacy] Error getting AuthUser cookie. ", err);
            });
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

    setUpModels() {
        this.privacy = {
            adult: this.regionPolicyService.isAdult(this.user?.approximate_age) ? true : false,
            public: this.user.usersetting.public_profile == "0" ? false : true,
            leaderboard: this.user.usersetting.public_leaderboard == "0" ? false : true,
            audioArchive: this.user.usersetting.audio_archive == "0" ? false : true,
            parental_toggle: this.user.usersetting.parental_lock_on == "1" ? true : false,
        };
    }

    setPrivacy(type: string) {
        let data = {};
        switch (type) {
            case "public":
                data = {
                    id: this.user.id,
                    public_profile: this.privacy.public ? "1" : "0",
                };
                break;
            case "leaderboard":
                data = {
                    id: this.user.id,
                    public_leaderboard: this.privacy.leaderboard ? "1" : "0",
                };
                break;
            case "audioArchive":
                data = {
                    id: this.user.id,
                    audio_archive: this.privacy.audioArchive ? "1" : "0",
                };
                break;
            case "parentalLock":
                data = {
                    id: this.user.id,
                    parental_lock: this.privacy.parental_lock,
                };
                break;
            default:
                break;
        }
        this.setLoader(true);
        this.updateUser(data);
    }

    updateUser(data: any) {
        this.settingsService
            .updateUserSettings(data)
            .then(async (res) => {
                this.user = res.data.results[0];
                try {
                    await this.baseService.setAuthUserCookie(this.user);
                } catch (err) {
                    this.snackbarService.handleError(err, "Error setting user cookie.");
                }
            })
            .catch((err) => {
                console.error("[privacy] Error updating user settings. ", err);
            })
            .finally(() => {
                this.setLoader(false);
            });
    }

    openParentalLockModal() {
        if (this.privacy.parental_toggle && !this.user.usersetting.parental_lock) {
            jQuery("#parentalLockModal").modal("show");
        }
        this.setParentalToggle(this.privacy.parental_toggle);
    }

    setParentalToggle(type) {
        const data: any = {
            id: this.user.id,
            parental_lock_on: type ? "1" : "0",
        };

        if (!type) {
            this.lockEditFlag = false;
            this.localStorage.removeItem("parentalLockCode");
        }
        this.updateUser(data);
    }

    changeParentalLock(form) {
        if (form.valid) {
            this.privacy.parental_lock = this.lockEditFlag ? form.value.parentalLockNew : form.value.parentalLock;
            this.setPrivacy("parentalLock");
            jQuery("#parentalLockModal").modal("hide");
            form.reset();
        }
    }

    cancelParentalLock() {
        if (!this.lockEditFlag) {
            this.privacy.parental_toggle = false;
            this.setParentalToggle(false);
        }
    }

    goToUrl(url: string) {
        void this.router.navigate([url]);
    }

    isTrial() {
        return !this.userEmailValid() && this.user?.email.substring(0, 5) == "trial";
    }

    userEmailValid() {
        return this.user?.email.indexOf("@") >= 0;
    }
}
