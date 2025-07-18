import { Component, OnInit, OnDestroy, AfterViewInit, ViewChild } from "@angular/core";
import { Router } from "@angular/router";
import { UntypedFormGroup, UntypedFormControl, Validators } from "@angular/forms";
import { CookieService } from "app/_services/cookie.service";
import { Subscription } from "rxjs";

import { ForumService } from "app/_services/forum.service";
import { Loader } from "app/_services/loader.service";
import { LocalStorageService } from "app/_services/local-storage.service";
import { SettingsService } from "app/_services/settings.service";
import { BadgeService } from "app/_services/badge.service";
import { environment } from "environments/environment";
import { SnackbarService } from "app/_services/snackbar.service";
import { VirtualKeyboardComponent } from "app/_partials/virtual-keyboard/virtual-keyboard.component";
import { VirtualKeyboardService } from "app/_services/virtual-keyboard.service";
import { AudioService } from "app/_services/audio.service";
import { SiteSettingsService } from "app/_services/site-settings.service";
import { RegionPolicyService } from "app/_services/region-policy.service";

declare let jQuery: any;

declare let bootbox: any;

// declare var VirtualKeyboard: any;
@Component({
    selector: "app-forum-list",
    templateUrl: "./forum-list.component.html",
    styleUrls: ["./forum-list.component.scss"],
})
export class ForumListComponent implements OnInit, OnDestroy, AfterViewInit {
    public environment = environment;
    public user: any = {};
    public forums: any = [];
    public posts: any = [];
    public activeForum: number = null;
    public activeForumName: string = "";
    public noForums: boolean = false;
    public noPosts: boolean = false;
    public paramsFetched: boolean = false;
    public postForm: UntypedFormGroup;
    public pagenumber = 1;
    public currentPage = 1;
    public totalPage = 1;
    public searchPost = false;
    public searchTxt: any = "";
    public postId: any = "";
    public postStatus: any = "R";
    private forumDataSubscription: Subscription;
    private postReportDoneSubscription: Subscription;
    public valuetitle: boolean = false;
    public valuecontent: boolean = false;
    public hideNewPostBtn: boolean = false;

    public keyType: any = null;
    public keyboardFlag: boolean = false;
    public levelbadgeFlag: boolean = false;
    private keyboardVisibilitySubscription: Subscription;

    private settings: any = null;

    // For virtual keyboard
    public inputs: any = {
        search_id: "",
        input_id: "",
        textarea_id: "",
    };
    public inputAreaClicked: boolean = false;

    @ViewChild("virtualKeyboard") virtualKeyboard: VirtualKeyboardComponent;
    @ViewChild("scrollPost", { static: false }) myButton;

    constructor(
        private forumService: ForumService,
        private loader: Loader,
        private cookieService: CookieService,
        private router: Router,
        private localStorage: LocalStorageService,
        private badgeService: BadgeService,
        private settingsService: SettingsService,
        private snackbarService: SnackbarService,
        private virtualKeyboardService: VirtualKeyboardService,
        public audioService: AudioService,
        private siteSettingsService: SiteSettingsService,
        private regionPolicy: RegionPolicyService,
    ) {
        this.cookieService
            .get("AuthUser")
            .then((value) => {
                if (value == "") {
                    throw value;
                }
                this.user = JSON.parse(value);

                this.siteSettingsService
                    .getSettings()
                    .then((settings) => {
                        this.settings = settings;
                    })
                    .catch((err) => {
                        console.error(err);
                    });

                this.forumDataSubscription = forumService.forumObj.subscribe((obj) => {
                    if (obj && Object.keys(obj).length > 0) {
                        this.localStorage.setItem("forumParams", JSON.stringify(obj));
                        void this.getForums(obj);
                        this.paramsFetched = true;
                    }
                });

                this.postReportDoneSubscription = forumService.postReportDone.subscribe(
                    (status) => {
                        // Reload posts is data is available, otherwise reload page
                        if (status == true) {
                            const forumParams = this.localStorage.getItem("forumParams");
                            if (!!forumParams) {
                                void this.getForums(JSON.parse(forumParams));
                            } else {
                                void this.router.navigate(["/village"]);
                            }
                        }
                    },
                );

                this.keyboardVisibilitySubscription =
                    this.virtualKeyboardService.keyboardVisibility.subscribe((visible) => {
                        this.keyboardFlag = visible;
                    });
            })
            .catch((err) => {
                console.info("[forum-list] Error getting AuthToken cookie. ", err);
                void this.router.navigate([""]);
            });
    }

    ngOnInit() {
        this.postForm = new UntypedFormGroup({
            title: new UntypedFormControl("", [Validators.required]),
            content: new UntypedFormControl("", [Validators.required]),
            postStatus: new UntypedFormControl("R"),
        });
        this.localStorage.removeItem("postId");
        this.cookieService
            .get("AuthUser")
            .then((value) => {
                if (value == "") {
                    throw value;
                }
                this.user = JSON.parse(value);
            })
            .catch((err) => {
                console.info("[forum-list] Error getting AuthUser cookie. ", err);
                void this.router.navigate([""]);
            });
    }

    /**
     * Checks if the user is allowed to access the village.
     *
     * @returns {boolean} True if the user is allowed to access the village, false otherwise.
     */
    public canAccessVillage(): boolean {
        return (
            this.regionPolicy.isAdult(this.user?.approximate_age) ||
            (this.settings?.setting_minors_can_access_village === "1" &&
                !this.regionPolicy.isBetweenChildAndAdult(this.user?.approximate_age))
        );
    }

    private setForumParams() {
        this.cookieService
            .get("AuthUser")
            .then((value) => {
                if (value == "") {
                    throw value;
                }
                const user = JSON.parse(value);
                const params = {
                    path_id: user.learningpath_id,
                    user_id: user.id,
                };
                this.localStorage.setItem("forumParams", JSON.stringify(params));
            })
            .catch((err) => {
                console.info("[forum-list] Error getting AuthUser cookie. ", err);
                void this.router.navigate([""]);
            });
    }

    ngAfterViewInit() {
        const forumParams = JSON.parse(this.localStorage.getItem("forumParams"));
        if (!forumParams || !forumParams.path_id) {
            this.setForumParams();
        }

        if (!this.paramsFetched) {
            void this.getForums(JSON.parse(this.localStorage.getItem("forumParams")));
        }
    }

    ngOnDestroy(): void {
        this.forumDataSubscription.unsubscribe();
        this.postReportDoneSubscription.unsubscribe();
        this.keyboardVisibilitySubscription.unsubscribe();
        this.localStorage.removeItem("forumParams");
    }

    imgfunc(allbadge: any, type: string) {
        const imageUrl = this.badgeService.badgeImgSet(allbadge, type);
        return imageUrl;
    }

    getImage(image: any) {
        let x = 1;
        let img = "";
        for (let index = 0; index < image.length; index++) {
            if (x == 1 && !image.status) {
                x = x + 1;
                img = image.image;
            }
        }
        return img; //'./assets/images/user_img.png';
    }

    private getForums(params) {
        this.loader.setLoader(true);
        return this.forumService
            .getForums(params)
            .then((res) => {
                if (!res) {
                    return;
                }
                this.loader.setLoader(false);
                if (res.data.results.length > 0) {
                    this.forums = res.data.results;

                    const localForumId = this.localStorage.getItem("forumId");

                    let activeForumDetails = 0;

                    for (let i = 0; i < this.forums.length; i++) {
                        if (this.forums[i].id == localForumId) {
                            activeForumDetails = this.forums[i];
                        }
                    }

                    setTimeout(() => {
                        if (activeForumDetails != 0) {
                            this.setActiveForum(activeForumDetails);
                        } else {
                            this.setActiveForum(this.forums[0]);
                        }
                    }, 100);
                } else {
                    this.noForums = true;
                }
            })
            .catch((err) => {
                this.loader.setLoader(false);
                console.error("[forum-list] Error getting forums. ", err);
            });
    }

    search() {
        this.searchPost = true;
        this.posts = [];
        if ((this.user.role_id == 4 || this.user.role_id == 1) && this.activeForum == null) {
            this.getPostsForFlagForum();
        } else {
            this.getPostsForForum();
        }
    }

    clear() {
        this.searchPost = false;
        this.searchTxt = "";
        this.posts = [];
        if ((this.user.role_id == 4 || this.user.role_id == 1) && this.activeForum == null) {
            this.getPostsForFlagForum();
        } else {
            this.getPostsForForum();
        }
    }

    private getPostsForForum(page: number = 1) {
        const timeZoneOffset = new Date().getTimezoneOffset() * 60;
        const params: any = {
            user_id: this.user.id,
            page: page,
            forum_id: this.activeForum,
            type: "normal",
            timestamp_offset:
                timeZoneOffset > 0 ? -Math.abs(timeZoneOffset) : Math.abs(timeZoneOffset),
        };

        if (this.searchPost && this.searchTxt.trim() != "" && this.searchTxt != undefined) {
            params.q = this.searchTxt;
        }

        this.loader.setLoader(true);
        this.forumService
            .getForumPosts(params)
            .then((res) => {
                this.loader.setLoader(false);
                if (res.data.results.items.length > 0) {
                    this.noPosts = false;
                    res.data.results.items.forEach((post) => {
                        this.posts.push(post);
                    });
                    this.currentPage = res.data.results.pageinfo.currentpage;
                    this.totalPage = res.data.results.pageinfo.totalpage;
                } else {
                    if (page == 1) {
                        this.noPosts = true;
                    }
                }
            })
            .catch((err) => {
                this.loader.setLoader(false);
                console.error(err);
            });
    }

    private getPostsForFlagForum(page: number = 1) {
        const timeZoneOffset = new Date().getTimezoneOffset() * 60;
        const params: any = {
            page: page,
            timestamp_offset:
                timeZoneOffset > 0 ? -Math.abs(timeZoneOffset) : Math.abs(timeZoneOffset),
        };

        if (this.searchPost && this.searchTxt.trim() != "" && this.searchTxt != undefined) {
            params.q = this.searchTxt;
        }

        this.loader.setLoader(true);
        this.forumService
            .flagPostList(params)
            .then((res) => {
                this.loader.setLoader(false);
                if (res.data.results.items.length > 0) {
                    this.noPosts = false;
                    res.data.results.items.forEach((post) => {
                        this.posts.push(post);
                    });
                    this.currentPage = res.data.results.pageinfo.currentpage;
                    this.totalPage = res.data.results.pageinfo.totalpage;
                } else {
                    if (page == 1) {
                        this.noPosts = true;
                    }
                }
            })
            .catch((err) => {
                this.loader.setLoader(false);
                console.error("[forum-list] Error getting flag post list. ", err);
            });
    }

    setActiveForum(forum) {
        this.localStorage.setItem("forumId", forum.id);
        if (forum.title == "Lessons by Unit") {
            const params = {
                path_id: forum.path_id,
                user_id: this.user.id,
                level_id: forum.level_id,
                labelType: "unitfetch",
            };
            this.forumService.setForumParams(params);
        } else {
            if (forum.title == "What’s New" && (this.user.role_id != 4 || this.user.role_id != 1)) {
                this.hideNewPostBtn = true;
            } else {
                this.hideNewPostBtn = false;
            }
            this.posts = [];
            this.activeForum = forum.id;
            this.activeForumName = forum.title;
            if ((this.user.role_id == 4 || this.user.role_id == 1) && this.activeForum == null) {
                this.getPostsForFlagForum();
            } else {
                this.getPostsForForum();
            }
        }
    }

    openModal(name, type, postObj) {
        // VirtualKeyboard.hide();
        this.keyboardFlag = false;
        if (type == "edit") {
            this.postId = postObj.id;
            this.postForm.patchValue({
                title: postObj.title,
                content: postObj.content,
            });
            setTimeout(() => {
                jQuery("#" + name).modal("show");
                jQuery("#reset-progress-data").text("Edit Topic");
            }, 100);
        } else {
            this.postForm.patchValue({
                title: "",
                content: "",
            });
            jQuery("#" + name).modal("show");
            jQuery("#reset-progress-data").text("New Topic");
        }
    }

    createPost(form) {
        form.value.title = form.value.content.trim();
        form.value.content = form.value.content.trim();

        if (form.value.title && form.value.content) {
            this.loader.setLoader(true);
            if (this.postId) {
                const data: any = {
                    forum_id: this.activeForum,
                    post_id: this.postId,
                    user_id: this.user.id,
                    title: form.value.title.trim(),
                    content: form.value.content.trim(),
                };
                if (
                    (this.user.role_id == 4 || this.user.role_id == 1) &&
                    this.activeForum == null
                ) {
                    data.status = form.value.postStatus;
                }

                this.forumService
                    .updatePost(data)
                    .then((res: any) => {
                        this.loader.setLoader(false);
                        this.snackbarService.showSnackbar({
                            status: res.data.status,
                            msg: res.data.message,
                        });
                        if (res.data.status) {
                            jQuery("#newPost").modal("hide");
                            this.posts = [];
                            this.postId = "";
                            if (
                                (this.user.role_id == 4 || this.user.role_id == 1) &&
                                this.activeForum == null
                            ) {
                                this.getPostsForFlagForum();
                            } else {
                                this.getPostsForForum();
                            }
                        }
                    })
                    .catch((err) => {
                        console.error(err);
                        this.loader.setLoader(false);
                        this.snackbarService.showSnackbar({
                            status: false,
                            msg: "There has been an error. Please try again after some time while we fix it.",
                        });
                    });
            } else {
                const data = {
                    forum_id: this.activeForum,
                    user_id: this.user.id,
                    title: form.value.title,
                    content: form.value.content,
                    type: "create",
                };
                this.forumService
                    .createPost(data)
                    .then((res: any) => {
                        this.loader.setLoader(false);
                        this.snackbarService.showSnackbar({
                            status: res.data.status,
                            msg: res.data.message,
                        });
                        if (res.data.status) {
                            jQuery("#newPost").modal("hide");
                            this.posts = [];
                            if (
                                (this.user.role_id == 4 || this.user.role_id == 1) &&
                                this.activeForum == null
                            ) {
                                this.getPostsForFlagForum();
                            } else {
                                this.getPostsForForum();
                            }
                        }
                    })
                    .catch((err) => {
                        console.error(err);
                        this.loader.setLoader(false);
                        this.snackbarService.showSnackbar({
                            status: false,
                            msg: "There has been an error. Please try again after some time while we fix it.",
                        });
                    });
            }
        } else {
            this.snackbarService.showSnackbar({
                status: false,
                msg: "Please fill in all fields with valid data before moving forward.",
            });
        }

        this.virtualKeyboard.hide();
    }

    deletePost(postobj) {
        bootbox.confirm({
            message: "Are you sure? You want to delete this post?",
            buttons: {
                confirm: {
                    label: "Yes",
                    className: "btn-success",
                },
                cancel: {
                    label: "No",
                    className: "btn-danger",
                },
            },
            callback: (result) => {
                if (result) {
                    // deletePost
                    const data = {
                        post_id: postobj.id,
                    };
                    this.forumService
                        .deletePost(data)
                        .then((res: any) => {
                            this.loader.setLoader(false);
                            this.snackbarService.showSnackbar({
                                status: true,
                                msg: res.data.message,
                            });
                            if (res.data.status) {
                                this.posts = [];
                                if (
                                    (this.user.role_id == 4 || this.user.role_id == 1) &&
                                    this.activeForum == null
                                ) {
                                    this.getPostsForFlagForum();
                                } else {
                                    this.getPostsForForum();
                                }
                            }
                        })
                        .catch((err) => {
                            console.error(err);
                            this.loader.setLoader(false);
                            this.snackbarService.showSnackbar({
                                status: false,
                                msg: "There has been an error. Please try again after some time while we fix it.",
                            });
                            this.posts = [];
                            if (
                                (this.user.role_id == 4 || this.user.role_id == 1) &&
                                this.activeForum == null
                            ) {
                                this.getPostsForFlagForum();
                            } else {
                                this.getPostsForForum();
                            }
                        });
                }
            },
        });
    }

    openReportPostModal(postObj) {
        const params = {
            postToReport: postObj,
            userId: this.user.id,
        };
        this.forumService.openReportPostModal(params);
    }

    goToDetails(post) {
        this.forumService.setForumId(post.id);
        void this.router.navigate(["/forum-post-details"]);
    }

    onScroll() {
        if (this.currentPage != this.totalPage) {
            this.pagenumber = this.pagenumber + 1;
            this.getPostsForForum(this.pagenumber);
        }
    }

    approvePost(postObj) {
        const data = {
            user_id: this.user.id,
            post_id: postObj.id,
            status: "A",
            title: postObj.title,
            content: postObj.content,
        };
        this.forumService
            .updatePost(data)
            .then((res: any) => {
                this.loader.setLoader(false);
                this.snackbarService.showSnackbar({
                    status: true,
                    msg: res.data.message,
                });
                if (res.data.status) {
                    this.posts = [];
                    if (
                        (this.user.role_id == 4 || this.user.role_id == 1) &&
                        this.activeForum == null
                    ) {
                        this.getPostsForFlagForum();
                    } else {
                        this.getPostsForForum();
                    }
                }
            })
            .catch((err) => {
                console.error(err);
                this.loader.setLoader(false);
                this.snackbarService.showSnackbar({
                    status: false,
                    msg: "There has been an error. Please try again after some time while we fix it.",
                });
            });
    }

    goToProfile(user) {
        if (this.user.id == user.id) {
            void this.router.navigate(["profile-settings"]);
        } else {
            this.localStorage.setItem("publicProfile", user.id);
            this.settingsService.setPublicProfile({ profile_id: user.id });
            void this.router.navigate(["profile"]);
        }
    }

    toggleVirtualKeyboard(): void {
        if (this.keyboardFlag) {
            this.virtualKeyboard.show();
        } else {
            this.virtualKeyboard.hide();
        }
    }

    setActiveInput(event: Event): void {
        this.virtualKeyboard.onInputFocus(event);
    }
}
