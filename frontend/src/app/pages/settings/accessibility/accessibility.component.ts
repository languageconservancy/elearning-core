import { Component, OnInit, OnDestroy } from "@angular/core";
import { Router } from "@angular/router";
import { Subscription } from "rxjs";
import { CookieService } from "app/_services/cookie.service";

import { SettingsService } from "app/_services/settings.service";
import { LocalStorageService } from "app/_services/local-storage.service";
import { Loader } from "app/_services/loader.service";
import { environment } from "environments/environment";
import { BaseService } from "app/_services/base.service";
import { SnackbarService } from "app/_services/snackbar.service";

@Component({
    selector: "app-accessibility",
    templateUrl: "./accessibility.component.html",
    styleUrls: ["./accessibility.component.scss"],
})
export class AccessibilityComponent implements OnInit, OnDestroy {
    private lockSubscription: Subscription;
    public tabName: string = "";
    public hearing: any = {};
    public user: any;
    public lockFlag: boolean = false;
    public environment = environment;

    constructor(
        private settingsService: SettingsService,
        private loader: Loader,
        private cookieService: CookieService,
        private localStorage: LocalStorageService,
        private baseService: BaseService,
        private snackbarService: SnackbarService,
        private router: Router,
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
        this.settingsService.setTab("accessibility");
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
                    .then(async (res) => {
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
                            this.hearing.checked = this.user.usersetting.hearing == "0" ? false : true;
                        } else {
                            console.error("[accessibility] Error with user settings result. ", res);
                            await this.alreadyDeleted();
                        }
                    })
                    .catch(async (err) => {
                        this.setLoader(false);
                        if (!err.ok) {
                            console.error("[accessibility] Error getting user settings. ", err);
                            await this.alreadyDeleted();
                        }
                    });
            })
            .catch((err) => {
                console.info("[accessibility] Error getting AuthUser cookie. ", err);
            });
    }

    ngOnDestroy() {
        this.lockSubscription.unsubscribe();
    }

    private setLoader(val: boolean) {
        this.loader.setLoader(val);
    }

    private async alreadyDeleted() {
        try {
            await this.cookieService.deleteAll();
        } catch (err) {
            console.error("[accessibility] Error deleting all cookies. ", err);
        }
        this.localStorage.clear();
        setTimeout(() => {
            void this.router.navigate([""]);
        }, 1000);
    }

    setHearingImpaired() {
        this.setLoader(true);
        const data = {
            id: this.user.id,
            hearing: this.hearing.checked ? "1" : "0",
        };
        this.settingsService
            .updateUserSettings(data)
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
                console.error("[accessibility] Error updating user settings. ", err);
            })
            .finally(() => {
                this.setLoader(false);
            });
    }
}
