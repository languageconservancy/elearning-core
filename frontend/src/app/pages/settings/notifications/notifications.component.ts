import { Component, OnInit, OnDestroy } from "@angular/core";
import { Router } from "@angular/router";
import { Subscription } from "rxjs";
import { CookieService } from "app/_services/cookie.service";

import { Loader } from "app/_services/loader.service";
import { LocalStorageService } from "app/_services/local-storage.service";
import { SettingsService } from "app/_services/settings.service";
import { environment } from "environments/environment";
import { BaseService } from "app/_services/base.service";
import { SnackbarService } from "app/_services/snackbar.service";

@Component({
    selector: "app-notifications",
    templateUrl: "./notifications.component.html",
    styleUrls: ["./notifications.component.scss"],
})
export class NotificationsComponent implements OnInit, OnDestroy {
    public environment = environment;
    private lockSubscription: Subscription;
    public notif: any = {};
    public user: any;
    public lockFlag: boolean = false;

    constructor(
        private settingsService: SettingsService,
        private loader: Loader,
        private router: Router,
        private cookieService: CookieService,
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
        this.settingsService.setTab("notifications");
    }

    ngOnInit() {
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
                                    ? parentalLock == this.user.usersetting.parental_lock
                                        ? false
                                        : true
                                    : false;
                            }
                            this.setUpModels();
                        } else {
                            console.error("[notifications] Error in user settings result. ", res);
                            this.alreadyDeleted();
                        }
                    })
                    .catch((err) => {
                        this.setLoader(false);
                        if (!err.ok) {
                            console.error("[notifications] Error getting user settings. ", err);
                            this.alreadyDeleted();
                        }
                    });
            })
            .catch((err) => {
                console.info("[notifications] Error getting AuthUser cookie. ", err);
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

    setUpModels() {
        this.notif = {
            pushNotif: this.user.usersetting.push_notification == "0" ? false : true,
            email: this.user.usersetting.email_notification == "0" ? false : true,
            news: this.user.usersetting.news_event == "0" ? false : true,
            motivation: this.user.usersetting.motivation == "0" ? false : true,
            motivation_time: this.user.usersetting.motivation_time,
        };

        if (this.user.usersetting.motivation_time) {
            const notifTime = new Date(this.user.usersetting.motivation_time);
            const timeArray = this.user.usersetting.FormatedMotivationTime.split(":");
            let hour = timeArray[0];
            if (timeArray[2] == "PM") {
                hour = parseInt(hour) != 12 ? parseInt(hour) + 12 : 0;
                hour = parseInt(hour) < 10 ? "0" + hour : hour;
            }
            notifTime.setHours(hour);
            notifTime.setMinutes(timeArray[1]);
            notifTime.setSeconds(0);
            this.notif.motivation_time_obj = notifTime;
        }
    }

    setNotif(type: string) {
        let data = {};
        switch (type) {
            case "push":
                data = {
                    id: this.user.id,
                    push_notification: this.notif.pushNotif ? "1" : "0",
                };
                break;
            case "email":
                data = {
                    id: this.user.id,
                    email_notification: this.notif.email ? "1" : "0",
                };
                break;
            case "news":
                data = {
                    id: this.user.id,
                    news_event: this.notif.news ? "1" : "0",
                };
                break;
            case "motivation":
                data = {
                    id: this.user.id,
                    motivation: this.notif.motivation ? "1" : "0",
                };
                break;
            case "motivationTime":
                if (this.notif.motivation_time) {
                    data = {
                        id: this.user.id,
                        motivation_time: this.notif.motivation_time,
                    };
                }
                break;
            default:
                break;
        }

        this.setSetting(data);
    }

    setSetting(data) {
        this.setLoader(true);
        this.settingsService
            .updateUserSettings(data)
            .then(
                async (res) => {
                    this.user = res.data.results[0];
                    try {
                        await this.baseService.setAuthUserCookie(this.user);
                    } catch (err) {
                        this.snackbarService.handleError(err, "Error setting user cookie.");
                    }
                },
                (err) => {
                    console.error("[notifications] Error updating user settings. ", err);
                },
            )
            .catch((err) => {
                console.error("[notifications] Error updating user settings. ", err);
            })
            .finally(() => {
                this.setLoader(false);
            });
    }

    motifTime() {
        const hour = this.notif.motivation_time_obj.getHours();
        const min = this.notif.motivation_time_obj.getMinutes();
        this.notif.motivation_time = hour + ":" + min + ":00";

        setTimeout(() => {
            this.setNotif("motivationTime");
        }, 200);
    }
}
