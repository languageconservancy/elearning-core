import { Component, OnInit, Renderer2 } from "@angular/core";
import { trigger, state, style, animate, transition } from "@angular/animations";
import { Router } from "@angular/router";
import { CookieService } from "app/_services/cookie.service";
import { Subscription } from "rxjs";

import { ForumService } from "app/_services/forum.service";
import { Loader } from "app/_services/loader.service";
import { LocalStorageService } from "app/_services/local-storage.service";
import { SettingsService } from "app/_services/settings.service";

@Component({
    selector: "app-friend-sidebar",
    templateUrl: "./friend-sidebar.component.html",
    styleUrls: ["./friend-sidebar.component.scss"],
    animations: [
        trigger("navigation", [
            state("true", style({ right: "-15rem" })),
            state("false", style({ right: "0%" })),
            transition("0 => 1", animate(".2s")),
            transition("1 => 0", animate(".2s")),
        ]),
        trigger("showOverlay", [
            state("true", style({ opacity: 0.3, display: "block" })),
            state("false", style({ opacity: 0, display: "none" })),
            transition("0 => 1", animate(".2s")),
            transition("1 => 0", animate(".5s")),
        ]),
    ],
})
export class FriendSidebarComponent implements OnInit {
    public friendsListArray: any = [];
    public noFriends: boolean = false;
    public friendsList: any = [];
    public paramsFetched: boolean = false;
    private forumDataSubscription: Subscription;
    public navigation: boolean = true;
    public showOverlay: boolean = false;
    public searchItem: any = {};
    public toggleArrow: boolean = true;
    public user: any = {};

    constructor(
        private forumService: ForumService,
        private loader: Loader,
        private cookieService: CookieService,
        private router: Router,
        private renderer: Renderer2,
        private localStorage: LocalStorageService,
        private settingsService: SettingsService,
    ) {}

    ngOnInit() {
        this.cookieService
            .get("AuthUser")
            .then((value) => {
                if (value != "") {
                    this.user = JSON.parse(value);
                    this.getFriendlist();
                }
            })
            .catch((err) => {
                console.warn("No AuthUser cookie", err);
            });
    }

    navigationDrawer() {
        if (this.toggleArrow) {
            this.toggleArrow = false;
        } else {
            this.toggleArrow = true;
        }
        this.navigation = !this.navigation;
        this.showOverlay = !this.showOverlay;
    }

    goToProfile(user) {
        this.localStorage.setItem("publicProfile", user.id);
        this.settingsService.setPublicProfile({ profile_id: user.id });
        void this.router.navigate(["profile"]);
    }

    getFriendlist() {
        this.loader.setLoader(true);
        this.forumService
            .getUsersFriends({ user_id: this.user.id })
            .then((res) => {
                this.loader.setLoader(false);
                if (res.data.results.resultSet.length > 0) {
                    this.friendsListArray = res.data.results.resultSet;
                    this.assignUserFilterCopy();
                } else {
                    this.noFriends = true;
                }
            })
            .catch((err) => {
                this.loader.setLoader(false);
                console.error(err);
            });
    }
    goToReplyCounter(friend) {
        console.log("friend", friend);
    }
    goToAddFriendsPage() {
        void this.router.navigate(["add-friends"]);
    }

    goToPost(param: any) {
        this.forumService.setUserId({ user_id: param });
        this.localStorage.setItem("publicProfile", param);
        void this.router.navigate(["posts-by-user"]);
    }

    private assignUserFilterCopy() {
        this.friendsList = Object.assign([], this.friendsListArray);
    }

    search() {
        if (!this.searchItem.search || this.searchItem.search == "") {
            this.assignUserFilterCopy();
            return;
        }
        // if (this.friendsList.length < 1) {
        //     this.noFriends = true;
        // } else {
        //     this.noFriends = false;
        // }
        this.friendsList = Object.assign([], this.friendsListArray).filter((item) => {
            return item.name && item.name.toLowerCase().indexOf(this.searchItem.search.toLowerCase()) > -1;
        });
    }
}
