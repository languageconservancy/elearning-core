import { Component, OnInit } from "@angular/core";
import { Router } from "@angular/router";
import { CookieService } from "app/_services/cookie.service";

import { Loader } from "app/_services/loader.service";
import { ProgressService } from "app/_services/progress.service";
import { SiteSettingsService } from "app/_services/site-settings.service";
import { RegionPolicyService } from "app/_services/region-policy.service";

@Component({
    selector: "app-your-progress",
    templateUrl: "./your-progress.component.html",
    styleUrls: ["./your-progress.component.scss"],
})
export class YourProgressComponent implements OnInit {
    public user: any = [];
    public getprogress: any = [];
    public fireImage: string = "dead";
    private settings: any = null;

    constructor(
        private progressService: ProgressService,
        private loader: Loader,
        private cookieService: CookieService,
        private router: Router,
        private regionPolicyService: RegionPolicyService,
        private siteSettingsService: SiteSettingsService,
    ) {
        this.siteSettingsService
            .getSettings()
            .then((settings) => {
                this.settings = settings;
            })
            .catch((err) => {
                console.error(err);
            });
    }

    ngOnInit() {
        this.cookieService
            .get("AuthUser")
            .then((value) => {
                if (value != "") {
                    this.user = JSON.parse(value);
                    const params = {
                        user_id: this.user.id,
                        path_id: this.user.learningpath_id,
                    };
                    this.getProgressDetails(params);
                }
            })
            .catch((err) => {
                console.warn("No AuthUser cookie", err);
            });
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

    getProgressDetails(params) {
        this.loader.setLoader(true);
        this.progressService
            .getProgress(params)
            .then((res: any) => {
                if (res.data.status) {
                    this.getprogress = res.data.results;
                    this.setFireImage();
                    this.loader.setLoader(false);
                }
            })
            .catch((err) => {
                console.error(err);
                this.loader.setLoader(false);
            });
    }

    private setFireImage() {
        const fireDays = this.getprogress.FireData.fire_days;
        if (fireDays > 0 && fireDays < 3) {
            this.fireImage = "low";
        } else if (fireDays >= 3 && fireDays < 7) {
            this.fireImage = "medium";
        } else if (fireDays >= 7 && fireDays < 14) {
            this.fireImage = "high";
        } else if (fireDays >= 14) {
            this.fireImage = "ultra";
        }
    }

    goToPage(page) {
        void this.router.navigate([page]);
    }
}
