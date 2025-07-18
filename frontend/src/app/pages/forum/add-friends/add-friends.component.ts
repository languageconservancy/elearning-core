import { Component, AfterViewInit, Renderer2 } from "@angular/core";
import { Router } from "@angular/router";
import { CookieService } from "app/_services/cookie.service";

import { ForumService } from "app/_services/forum.service";
import { Loader } from "app/_services/loader.service";
import { LocalStorageService } from "app/_services/local-storage.service";
import { FindFriendsService } from "app/_services/find-friends.service";
import { SettingsService } from "app/_services/settings.service";

@Component({
    selector: "app-add-friends",
    templateUrl: "./add-friends.component.html",
    styleUrls: ["./add-friends.component.scss"],
})
export class AddFriendsComponent implements AfterViewInit {
    public user: any = {};
    public friends: any = [];
    public pagenumber = 1;
    public currentPage = 1;
    public totalPage = 1;
    public noFriends: boolean = true;
    public allUsers: any = [];
    public page: number = 1;
    public limit: number = 20;
    public searchItem: any = {
        search: "",
    };
    constructor(
        private forumService: ForumService,
        private loader: Loader,
        private cookieService: CookieService,
        private router: Router,
        private renderer: Renderer2,
        private localStorage: LocalStorageService,
        private findFriendsService: FindFriendsService,
        private settingsService: SettingsService,
    ) {
        this.cookieService
            .get("AuthUser")
            .then((value) => {
                if (value != "") {
                    this.user = JSON.parse(value);
                }
            })
            .catch((err) => {
                console.warn("No AuthUser cookie", err);
            });
    }

    ngAfterViewInit() {
        this.friendlist();
    }

    friendlist(page = 1) {
        const params: any = {
            user_id: this.user.id,
        };
        this.loader.setLoader(true);
        this.findFriendsService
            .getAllUsers(params)
            .then((res) => {
                this.loader.setLoader(false);
                if (res.data.results.resultSet.length > 0) {
                    this.noFriends = false;
                    res.data.results.resultSet.forEach((item) => {
                        this.allUsers.push(item);
                    });
                } else {
                    if (page == 1) {
                        this.noFriends = true;
                    }
                }
                setTimeout(() => {
                    for (let i = 0; i < this.page * this.limit; i++) {
                        if (this.allUsers[i]) {
                            this.friends.push(this.allUsers[i]);
                        }
                    }
                }, 20);
            })
            .catch((err) => {
                this.loader.setLoader(false);
                console.error(err);
            });
    }

    search() {
        if (!this.searchItem.search || this.searchItem.search == "") {
            this.assignUserFilterCopy();
            return;
        }

        this.friends = Object.assign([], this.allUsers).filter((item) => {
            return item.name && item.name.toLowerCase().indexOf(this.searchItem.search.toLowerCase()) > -1;
        });
    }
    clear() {
        this.searchItem.search = "";
        this.friends = [];
        this.friendlist();
    }
    assignUserFilterCopy() {
        this.friends = [];
        for (let i = 0; i < this.page * this.limit; i++) {
            if (this.allUsers[i]) {
                this.friends.push(this.allUsers[i]);
            }
        }
    }

    loadMore() {
        const start = this.page * this.limit;
        this.page++;
        for (let i = start; i < this.page * this.limit; i++) {
            if (this.allUsers[i]) {
                this.friends.push(this.allUsers[i]);
            }
        }
    }
    friendAddRemove(status, friend) {
        this.loader.setLoader(true);
        const params: any = {
            userId: this.user.id,
            status: status,
            friendId: friend.id,
        };
        this.findFriendsService
            .addRemoveFriend(params)
            .then((res) => {
                this.loader.setLoader(false);
                if (res.data.status) {
                    if (status == 1) {
                        friend.friendstatus = true;
                    } else if (status == 0) {
                        friend.friendstatus = false;
                    }
                }
            })
            .catch((err) => {
                this.loader.setLoader(false);
                console.error(err);
            });
    }
    goToProfile(friend) {
        this.localStorage.setItem("publicProfile", friend.id);
        this.settingsService.setPublicProfile({ profile_id: friend.id });
        void this.router.navigate(["profile"]);
    }
}
