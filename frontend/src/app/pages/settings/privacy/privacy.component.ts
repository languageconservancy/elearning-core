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

interface Privacy {
    isAdult: boolean;
    profileIsPublic: boolean;
    onPublicLeaderboard: boolean;
    audioArchive: boolean;
    parentalLockOn: boolean;
    parentalLockCode: string;
}

interface UserSettings {
    parentalLockCode: string;
    profileIsPublic: boolean;
    onPublicLeaderboard: boolean;
    audioArchive: boolean;
    parentalLockOn: boolean;
}

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
    public privacy: Privacy = {
        isAdult: false,
        profileIsPublic: false,
        onPublicLeaderboard: false,
        audioArchive: false,
        parentalLockOn: false,
        parentalLockCode: "",
    };
    public initialUserSettings: UserSettings = {
        parentalLockCode: "",
        profileIsPublic: false,
        onPublicLeaderboard: false,
        audioArchive: false,
        parentalLockOn: false,
    };
    public createParentalLockCodeForm: UntypedFormGroup;
    public updateParentalLockCodeForm: UntypedFormGroup;

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

        this.lockSubscription = this.settingsService.parentalLockCode.subscribe(() => {
            console.log("parentalLockCode changed");
            this.lockFlag = false;
        });
        this.settingsService.setTab("privacy");
    }

    ngOnInit() {
        this.createParentalLockCodeForm = new UntypedFormGroup({
            // eslint-disable-next-line @typescript-eslint/unbound-method
            parentalLockCode: new UntypedFormControl("", Validators.required),
        });

        this.setLoader(true);
        this.getSettings();
        this.updateParentalLockCodeForm = new UntypedFormGroup({
            // eslint-disable-next-line @typescript-eslint/unbound-method
            parentalLockCodeCurrent: new UntypedFormControl("", [
                Validators.required,
                this.validateParentalLockCode.bind(this),
            ]),
            // eslint-disable-next-line @typescript-eslint/unbound-method
            parentalLockCodeNew: new UntypedFormControl("", Validators.required),
        });
    }

    ngOnDestroy() {
        this.lockSubscription.unsubscribe();
    }

    private validateParentalLockCode(control: UntypedFormControl): any {
        if (this.user) {
            return control.value === this.initialUserSettings.parentalLockCode
                ? null
                : { notSame: true };
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
                            this.storeUserSettings(this.user);
                            const parentalLock = this.localStorage.getItem("parentalLockCode");
                            if (this.user.usersetting.parental_lock_on == "1") {
                                this.lockFlag = this.user.usersetting.parental_lock
                                    ? parentalLock &&
                                      parentalLock == this.user.usersetting.parental_lock
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

    private storeUserSettings(user: any) {
        this.initialUserSettings = {
            parentalLockCode: user.usersetting.parental_lock,
            profileIsPublic: user.usersetting.public_profile == "0" ? false : true,
            onPublicLeaderboard: user.usersetting.public_leaderboard == "0" ? false : true,
            audioArchive: user.usersetting.audio_archive == "0" ? false : true,
            parentalLockOn: user.usersetting.parental_lock_on == "1" ? true : false,
        };
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
            isAdult: this.regionPolicyService.isAdult(this.user?.approximate_age) ? true : false,
            profileIsPublic: this.user.usersetting.public_profile == "0" ? false : true,
            onPublicLeaderboard: this.user.usersetting.public_leaderboard == "0" ? false : true,
            audioArchive: this.user.usersetting.audio_archive == "0" ? false : true,
            parentalLockOn: this.user.usersetting.parental_lock_on == "1" ? true : false,
            parentalLockCode: this.user.usersetting.parental_lock,
        };
    }

    updatePrivacySetting(settingType: string) {
        let data = {};
        switch (settingType) {
            case "public":
                data = {
                    id: this.user.id,
                    public_profile: this.privacy.profileIsPublic ? "1" : "0",
                };
                break;
            case "leaderboard":
                data = {
                    id: this.user.id,
                    public_leaderboard: this.privacy.onPublicLeaderboard ? "1" : "0",
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
                    parental_lock: this.initialUserSettings.parentalLockCode,
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
                this.storeUserSettings(this.user);
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
        if (this.privacy.parentalLockOn && !this.user.usersetting.parental_lock) {
            // parental lock is turned on but no code is set, open modal to set code.
            jQuery("#parentalLockModal").modal("show");
        }
        this.setParentalLockState(this.privacy.parentalLockOn);
    }

    setParentalLockState(enabled: boolean) {
        const data: any = {
            id: this.user.id,
            parental_lock_on: enabled ? "1" : "0",
        };

        if (!enabled) {
            this.lockEditFlag = false;
            this.localStorage.removeItem("parentalLockCode");
        }
        this.updateUser(data);
    }

    onParentalLockCodeChange(form) {
        if (form.valid) {
            this.privacy.parentalLockOn = this.lockEditFlag
                ? form.value.parentalLockCodeNew
                : form.value.parentalLockCode;
            this.updatePrivacySetting("parentalLock");
            jQuery("#parentalLockModal").modal("hide");
            form.reset();
        }
    }

    cancelUpdatingParentalLockCode() {
        if (!this.lockEditFlag) {
            this.privacy.parentalLockOn = false;
            this.setParentalLockState(false);
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
