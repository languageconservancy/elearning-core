import { Component } from "@angular/core";
import { Router, ActivatedRoute } from "@angular/router";
import { CookieService } from "app/_services/cookie.service";

import { LocalStorageService } from "app/_services/local-storage.service";
import { ForumService } from "app/_services/forum.service";
import { ReviewService } from "app/_services/review.service";
import { environment } from "environments/environment";
import { SiteSettingsService } from "app/_services/site-settings.service";

@Component({
    selector: "app-footer",
    templateUrl: "./footer.component.html",
    styleUrls: ["./footer.component.scss"],
})
export class FooterComponent {
    public user: any;
    public features: any = null;
    public canAccessVillage: boolean = false;
    public canAccessLeaderboard: boolean = false;
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
    ) {}

    ngOnInit() {
        this.cookieService
            .get("AuthUser")
            .then(async (value) => {
                if (value == "") {
                    throw value;
                }
                this.user = JSON.parse(value);
                this.canAccessVillage = await this.siteSettingsService.canAccessVillage(
                    this.user?.approximate_age,
                );
                this.canAccessLeaderboard = await this.siteSettingsService.canAccessLeaderboard(
                    this.user?.approximate_age,
                );
            })
            .catch((err) => {
                console.warn("No AuthUser cookie", err);
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
