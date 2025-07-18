import { Component } from "@angular/core";
import { Router, ActivatedRoute } from "@angular/router";
import { CookieService } from "app/_services/cookie.service";

import { LocalStorageService } from "app/_services/local-storage.service";
import { ForumService } from "app/_services/forum.service";
import { ReviewService } from "app/_services/review.service";
import { environment } from "environments/environment";
import { SiteSettingsService } from "app/_services/site-settings.service";
import { RegionPolicyService } from "app/_services/region-policy.service";

@Component({
    selector: "app-footer",
    templateUrl: "./footer.component.html",
    styleUrls: ["./footer.component.scss"],
})
export class FooterComponent {
    public user: any;
    private settings: any = null;
    public features: any = null;
    public environment = environment;
    public copyrightDate = new Date().getFullYear();

    constructor(
        private router: Router,
        private localStorage: LocalStorageService,
        private thisRoute: ActivatedRoute,
        private forumService: ForumService,
        private cookieService: CookieService,
        private reviewService: ReviewService,
        private siteSettingsService: SiteSettingsService,
        private regionPolicyService: RegionPolicyService,
    ) {
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

    gotoUrl(url: any) {
        void this.router.navigate(["about/" + url]);
    }

    gotoUrlOther(url: any) {
        window.scroll(0, 0);
        if (url === "review") {
            localStorage.removeItem("unitID");
            this.reviewService.setReviewProgress({});
        }
        if (this.user) {
            void this.router.navigate([url]);
        } else {
            void this.router.navigate([""]);
        }
    }

    goToVillage() {
        window.scroll(0, 0);
        if (this.user) {
            this.localStorage.removeItem("forumId");
            const path: any = this.thisRoute.snapshot;
            const params: any = {
                path_id: this.user.learningpath_id,
                user_id: this.user.id,
            };
            if (path._routerState.url == "/start-learning") {
                params.level_id = parseInt(this.localStorage.getItem("LevelID"));
                params.labelType = "levelfetch";
            }

            if (path._routerState.url == "/lessons-and-exercises") {
                params.level_id = parseInt(this.localStorage.getItem("LevelID"));
                params.unit_id = parseInt(this.localStorage.getItem("unitID"));
            }

            this.forumService.setForumParams(params);
            void this.router.navigate(["village"]);
        } else {
            void this.router.navigate([""]);
        }
    }

    openNonAngularUrl(filePath) {
        const url = window.location.protocol + "//" + window.location.hostname + "/" + filePath;
        window.open(url, "_blank");
    }
}
