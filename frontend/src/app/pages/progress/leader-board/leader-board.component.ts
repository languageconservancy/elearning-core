import { Component, inject, OnInit } from "@angular/core";
import { Router } from "@angular/router";

import { PageTitleComponent } from "@app/_partials/page-title/page-title.component";
import { ProgressLeftPanelComponent } from "@app/pages/progress/_partials/progress-left-panel/progress-left-panel.component";
import { DecimalPipe } from "@angular/common";
import { CookieService } from "@app/_services/cookie.service";
import { Loader } from "@app/_services/loader.service";
import { LocalStorageService } from "@app/_services/local-storage.service";
import { ProgressService } from "@app/_services/progress.service";
import { SiteSettingsService } from "@app/_services/site-settings.service";

@Component({
    selector: "app-leader-board",
    imports: [PageTitleComponent, ProgressLeftPanelComponent, DecimalPipe],
    templateUrl: "./leader-board.component.html",
    styleUrls: ["./leader-board.component.scss"],
    standalone: true,
})
export class LeaderBoardComponent implements OnInit {
    private progressService = inject(ProgressService);
    private loader = inject(Loader);
    private cookieService = inject(CookieService);
    private localStorage = inject(LocalStorageService);
    private router = inject(Router);
    private siteSettingsService = inject(SiteSettingsService);

    public topUsers: any = [];
    public topFriends: any = [];
    public userCanAccessLeaderboard: boolean = false;
    public user: any = [];
    public leaderboardData: any = [];
    public leaderboardFlag: string = "";

    ngOnInit() {
        void this.initialize().catch((err: unknown) => {
            console.error("Unable to initialize leaderboard", err);
        });
    }

    private async initialize(): Promise<void> {
        const value = await this.cookieService.get("AuthUser");
        if (value === "") {
            return;
        }

        let user: unknown;
        try {
            user = JSON.parse(value);
        } catch (err) {
            console.error("Invalid AuthUser cookie", err);
            return;
        }

        if (typeof user !== "object" || user === null || !("id" in user)) {
            console.error("Invalid AuthUser cookie: missing user ID");
            return;
        }

        const userId = user.id;
        const approximateAge = "approximate_age" in user ? user.approximate_age : undefined;
        if (typeof userId !== "number" && typeof userId !== "string") {
            console.error("Invalid AuthUser cookie: invalid user ID");
            return;
        }

        this.user = user;
        if (approximateAge === null || approximateAge === undefined) {
            return;
        }
        if (typeof approximateAge !== "number" || !Number.isFinite(approximateAge)) {
            console.error("Invalid AuthUser cookie: invalid approximate age");
            return;
        }

        this.userCanAccessLeaderboard =
            await this.siteSettingsService.canAccessLeaderboard(approximateAge);
        if (this.userCanAccessLeaderboard) {
            this.getLeaderBoard({ user_id: userId });
        }
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
