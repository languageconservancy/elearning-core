import { Component, OnInit } from "@angular/core";
import { Router } from "@angular/router";
import { CookieService } from "app/_services/cookie.service";

import { Loader } from "app/_services/loader.service";
import { LocalStorageService } from "app/_services/local-storage.service";
import { ProgressService } from "app/_services/progress.service";
import { SiteSettingsService } from "app/_services/site-settings.service";
import { RegionPolicyService } from "app/_services/region-policy.service";

@Component({
    selector: "app-leader-board",
    templateUrl: "./leader-board.component.html",
    styleUrls: ["./leader-board.component.scss"],
})
export class LeaderBoardComponent implements OnInit {
    public top_users: any = [];
    public top_friends: any = [];
    private settings: any = null;

    public user: any = [];
    public leaderBoardData: any = [];
    public leaderboard_flag: string = "";
    constructor(
        private progressService: ProgressService,
        private loader: Loader,
        private cookieService: CookieService,
        private localStorage: LocalStorageService,
        private router: Router,
        private siteSettingsService: SiteSettingsService,
        private regionPolicyService: RegionPolicyService,
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
                    const params = { user_id: this.user.id };
                    this.getLeaderBoard(params);
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

    getLeaderBoard(params: { user_id: any }) {
        this.loader.setLoader(true);
        this.progressService
            .getLeaderBoardData(params)
            .then((res: any) => {
                if (!res.data.status) {
                    throw new Error(res.data.message);
                }
                this.leaderboard_flag = res.data.results.leaderboard_flag;
                this.leaderBoardData = res.data.results;
                this.top_users = this.leaderBoardData.top_users;
                this.top_friends = this.leaderBoardData.friends;
            })
            .catch((err: any) => {
                console.error(err);
            })
            .finally(() => {
                this.loader.setLoader(false);
            });
    }

    goToProfile(user) {
        this.localStorage.setItem("publicProfile", user.id);
        void this.router.navigate(["profile"]);
    }

    goToPage(slug) {
        void this.router.navigate([slug]);
    }
}
