import { Component, OnDestroy, AfterViewInit } from "@angular/core";
import { Router } from "@angular/router";
import { trigger, transition, query, style, animate, group } from "@angular/animations";
import { Subscription } from "rxjs";

import { LocalStorageService } from "app/_services/local-storage.service";
import { SettingsService } from "app/_services/settings.service";
import { Loader } from "app/_services/loader.service";
import { ForumService } from "app/_services/forum.service";
import { BadgeService } from "app/_services/badge.service";

@Component({
    selector: "app-public-profile",
    templateUrl: "./public-profile.component.html",
    styleUrls: ["./public-profile.component.scss"],
    animations: [
        trigger("slider", [
            transition(
                ":increment",
                group([
                    query(
                        ":enter",
                        [
                            style({
                                transform: "translateX(100%)",
                            }),
                            animate("0.5s ease-out", style("*")),
                        ],
                        { optional: true },
                    ),
                    query(
                        ":leave",
                        [
                            animate(
                                "0.5s ease-out",
                                style({
                                    transform: "translateX(-100%)",
                                }),
                            ),
                        ],
                        { optional: true },
                    ),
                ]),
            ),
            transition(
                ":decrement",
                group([
                    query(
                        ":enter",
                        [
                            style({
                                transform: "translateX(-100%)",
                            }),
                            animate("0.5s ease-out", style("*")),
                        ],
                        { optional: true },
                    ),
                    query(
                        ":leave",
                        [
                            animate(
                                "0.5s ease-out",
                                style({
                                    transform: "translateX(100%)",
                                }),
                            ),
                        ],
                        { optional: true },
                    ),
                ]),
            ),
        ]),
    ],
})
export class PublicProfileComponent implements OnDestroy, AfterViewInit {
    public publicUser: any = {};
    public profile: any = {};
    public userId: number = null;
    public paramFetched: boolean = false;
    public noProfile: boolean = true;
    private _images: string[] = [];
    public selectedIndex: number = 0;
    public imageLength: number = 0;
    public pageTitle: string = "";

    private profileSub: Subscription;

    constructor(
        private loader: Loader,
        private router: Router,
        private localStorage: LocalStorageService,
        private badgeService: BadgeService,
        private settingsService: SettingsService,
        private forumService: ForumService,
    ) {
        this.profileSub = this.settingsService.currentProfile.subscribe((res) => {
            if (res && Object.keys(res).length > 0) {
                this.userId = res.profile_id;
                this.getProfile(res.profile_id);
                this.paramFetched = true;
            }
        });
    }

    ngAfterViewInit() {
        if (!this.paramFetched) {
            this.userId = parseInt(this.localStorage.getItem("publicProfile"));
            this.getProfile(parseInt(this.localStorage.getItem("publicProfile")));
        }
    }

    ngOnDestroy(): void {
        this.profileSub.unsubscribe();
    }

    private getProfile(profileId: number) {
        if (!profileId) {
            this.noProfile = true;
            return;
        }
        this.loader.setLoader(true);
        this.settingsService
            .getPublicUser(profileId)
            .then((res) => {
                this.loader.setLoader(false);
                if (res.data.status) {
                    this.publicUser = res.data.results.user;
                    this.setGalleryImages(this.publicUser.userimages);
                    this.pageTitle = this.publicUser.name + "'s Profile";
                }
                this.noProfile = false;
            })
            .catch((err) => {
                this.loader.setLoader(false);
                console.error(err);
            });
    }

    setGalleryImages(gallery) {
        this._images = [];
        this.imageLength = gallery.length;
        if (gallery.length > 0) {
            gallery.forEach((element) => {
                this._images.push(element.FullImageUrl);
            });
        }
    }
    imgfunc(allbadge: any, type: string) {
        const imageUrl = this.badgeService.badgeImgSet(allbadge, type);
        return imageUrl;
    }
    get images() {
        return [this._images[this.selectedIndex]];
    }

    previous() {
        if (this.selectedIndex == 0) {
            this.selectedIndex = this._images.length;
        }
        this.selectedIndex = Math.max(this.selectedIndex - 1, 0);
    }

    next() {
        if (this.selectedIndex + 1 == this._images.length) {
            this.selectedIndex = -1;
        }
        this.selectedIndex = Math.min(this.selectedIndex + 1, this._images.length - 1);
    }

    goBack() {
        void this.router.navigate(["/village"]);
    }

    goToPosts() {
        this.forumService.setUserId({ user_id: this.publicUser.id });
        this.localStorage.setItem("publicProfile", this.publicUser.id);
        void this.router.navigate(["posts-by-user"]);
    }
}
