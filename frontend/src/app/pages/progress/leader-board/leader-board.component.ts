import { Component, OnInit } from "@angular/core";
import { Router } from "@angular/router";
import { CookieService } from "app/_services/cookie.service";

import { Loader } from "app/_services/loader.service";
import { LocalStorageService } from "app/_services/local-storage.service";
import { ProgressService } from "app/_services/progress.service";
import { SiteSettingsService } from "app/_services/site-settings.service";

@Component({
    selector: "app-leader-board",
    templateUrl: "./leader-board.component.html",
    styleUrls: ["./leader-board.component.scss"],
})
export class LeaderBoardComponent implements OnInit {
    public topUsers: any = [];
    public topFriends: any = [];
    public userCanAccessLeaderboard: boolean = false;
    public user: any = [];
    public leaderboardData: any = [];
    public leaderboardFlag: string = "";
    constructor(
        private progressService: ProgressService,
        private loader: Loader,
        private cookieService: CookieService,
        private localStorage: LocalStorageService,
        private router: Router,
        private siteSettingsService: SiteSettingsService,
    ) {}

    ngOnInit() {
        void this.initialize();
    }

    private async initialize() {
        this.cookieService
            .get("AuthUser")
            .then(async (value) => {
                if (value != "") {
                    this.user = JSON.parse(value);
                    const params = { user_id: this.user.id };
                    this.getLeaderBoard(params);
                    this.userCanAccessLeaderboard =
                        await this.siteSettingsService.canAccessLeaderboard(
                            this.user?.approximate_age as number,
                        );
                }
            })
            .catch((err) => {
                console.warn("No AuthUser cookie", err);
            });
    }

    getLeaderBoard(params: { user_id: any }) {
        this.loader.setLoader(true);
        this.progressService
            .getLeaderBoardData(params)
            .then((res: any) => {
                if (!res.data.status) {
                    throw new Error(res.data.message);
                }
                this.leaderboardFlag = res.data.results.leaderboard_flag;
                this.leaderboardData = res.data.results;
                this.topUsers = this.leaderboardData.top_users;
                this.topFriends = this.leaderboardData.friends;
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
