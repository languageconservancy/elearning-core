import { Component, OnInit, OnDestroy } from "@angular/core";
import { Router } from "@angular/router";
import { Subscription } from "rxjs";
import { CookieService } from "app/_services/cookie.service";

import { LocalStorageService } from "app/_services/local-storage.service";
import { RegistrationService } from "app/_services/registration.service";
import { LocalizeService } from "app/_services/localize.service";
import { environment } from "environments/environment";
import { SiteSettingsService } from "app/_services/site-settings.service";
import { RegionPolicyService } from "app/_services/region-policy.service";

@Component({
    selector: "app-spread-the-word",
    templateUrl: "./spread-the-word.component.html",
    styleUrls: ["./spread-the-word.component.scss"],
})
export class SpreadTheWordComponent implements OnInit, OnDestroy {
    public environment = environment;
    private userSubscription: Subscription;
    public user: any = {};
    public userId: string = "";
    public translations: any = {};
    private settings: any = null;

    constructor(
        private router: Router,
        private cookieService: CookieService,
        private registrationService: RegistrationService,
        private localStorage: LocalStorageService,
        private localizeService: LocalizeService,
        private siteSettingsService: SiteSettingsService,
        private regionPolicyService: RegionPolicyService,
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

        this.userSubscription = this.registrationService.currentUser.subscribe((userId) => (this.userId = userId.id));

        this.localizeService.getTranslations().subscribe((data) => {
            this.translations = data["components"]["spread-the-word"];
        });
    }

    ngOnInit() {
        this.cookieService
            .get("AuthUser")
            .then((value) => {
                if (value == "") {
                    throw value;
                }
                this.user = JSON.parse(value);
            })
            .catch((err) => {
                console.warn("No AuthUser cookie", err);
            });

        this.siteSettingsService
            .getSettings()
            .then((settings) => {
                this.settings = settings;
            })
            .catch((err) => {
                console.error("No settings", err);
            });
    }

    ngOnDestroy() {
        this.userSubscription.unsubscribe();
    }

    submit() {
        if (this.canAccessVillage()) {
            void this.router.navigate(["find-friends"]);
        } else {
            this.localStorage.removeItem("regProg");
            void this.router.navigate(["dashboard"]);
        }
    }

    private canAccessVillage(): boolean {
        return (
            this.regionPolicyService.isAdult(this.user?.approximate_age) ||
            (this.settings?.setting_minors_can_access_village === "1" &&
                !this.regionPolicyService.isBetweenChildAndAdult(this.user?.approximate_age))
        );
    }

    startLearning() {
        this.localStorage.removeItem("regProg");
        void this.router.navigate(["start-learning"]);
    }
}
