import { Injectable } from "@angular/core";
import { Router } from "@angular/router";
import { Observable } from "rxjs";

import { CookieService } from "app/_services/cookie.service";
import { SettingsService } from "app/_services/settings.service";

@Injectable()
export class MaintenanceModeCheckGuard {
    constructor(
        private settingsService: SettingsService,
        private router: Router,
    ) {}

    canActivate(): Observable<boolean> | Promise<boolean> | boolean {
        return this.settingsService
            .getMaintenanceMode()
            .then((res) => {
                if (!res && !res.data) {
                    // bad result for maintenance mode. don't allow route.
                    return false;
                }
                if (res.data.status && res.data.results.is_under_construction == "Y") {
                    // site under construction. don't allow route. redirect to construction page.
                    this.settingsService.setMaintenanceMode(true);
                    void this.router.navigate(["under-construction"]);
                    return false;
                } else {
                    // site not under construction. allow route.
                    this.settingsService.setMaintenanceMode(false);
                    return true;
                }
            })
            .catch(() => {
                // error getting determining if site is under construction. just allow route.
                this.settingsService.setMaintenanceMode(false);
                return true;
            });
    }
}

@Injectable()
export class MaintenanceModeGuard {
    constructor(
        private settingsService: SettingsService,
        private router: Router,
        private cookieService: CookieService,
    ) {}

    canActivate(): Observable<boolean> | Promise<boolean> | boolean {
        return this.settingsService
            .getMaintenanceMode()
            .then((res) => {
                if (res.data.status && res.data.results.is_under_construction == "N") {
                    // site not under construction. don't allow under construction page.
                    this.settingsService.setMaintenanceMode(false);
                    this.cookieService
                        .get("AuthUser")
                        .then((value) => {
                            if (value == "") {
                                throw value;
                            }
                            // user already signed in. route to dashboard.
                            void this.router.navigate(["dashboard"]);
                        })
                        .catch(() => {
                            // user not signed in. route to homepage.
                            void this.router.navigate([""]);
                            return false;
                        });
                } else {
                    // site is under construction. allow under construction page.
                    return true;
                }
            })
            .catch(() => {
                this.settingsService.setMaintenanceMode(false);
                return false;
            });
    }
}
