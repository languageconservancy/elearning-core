import {
    Component,
    OnInit,
    OnDestroy,
    AfterViewInit,
    inject,
    signal,
    ViewChild,
} from "@angular/core";
import { form, FieldTree, FormField, required, submit, validate } from "@angular/forms/signals";
import { FormsModule } from "@angular/forms";
import { Router } from "@angular/router";
import { InfiniteScrollDirective } from "ngx-infinite-scroll";
import { Subscription } from "rxjs";

import { PageTitleComponent } from "@app/_partials/page-title/page-title.component";
import { FriendSidebarComponent } from "../_partials/friend-sidebar/friend-sidebar.component";
import { ReportPostModalComponent } from "../_partials/report-post-modal/report-post-modal.component";
import { SocialShareComponent } from "@app/_partials/social-share/social-share.component";
import { VirtualKeyboardComponent } from "@app/_partials/virtual-keyboard/virtual-keyboard.component";
import { DirectivesModule } from "@app/_directives/directives.module";
import { BadgeImagePipe } from "@app/_pipes/badge-image.pipe";
import { AudioService } from "@app/_services/audio.service";
import { BadgeService } from "@app/_services/badge.service";
import { CookieService } from "@app/_services/cookie.service";
import { ForumService } from "@app/_services/forum.service";
import { Loader } from "@app/_services/loader.service";
import { LocalStorageService } from "@app/_services/local-storage.service";
import { ModalService } from "@app/_services/modal.service";
import { RegionPolicyService } from "@app/_services/region-policy.service";
import { SettingsService } from "@app/_services/settings.service";
import { SiteSettingsService } from "@app/_services/site-settings.service";
import { SnackbarService } from "@app/_services/snackbar.service";
import { VirtualKeyboardService } from "@app/_services/virtual-keyboard.service";
import { environment } from "@environments/environment";

declare let bootbox: any;

interface PostFormValue {
    title: string;
    content: string;
    postStatus: string;
}

// declare var VirtualKeyboard: any;
@Component({
    selector: "app-forum-list",
    imports: [
        BadgeImagePipe,
        DirectivesModule,
        FormField,
        FormsModule,
        FriendSidebarComponent,
        PageTitleComponent,
        ReportPostModalComponent,
        SocialShareComponent,
        VirtualKeyboardComponent,
        InfiniteScrollDirective,
    ],
    templateUrl: "./forum-list.component.html",
    styleUrls: ["./forum-list.component.scss"],
    standalone: true,
})
export class ForumListComponent implements OnInit, OnDestroy, AfterViewInit {
    public readonly audioService = inject(AudioService);
    private badgeService = inject(BadgeService);
    private cookieService = inject(CookieService);
    private forumService = inject(ForumService);
    private loader = inject(Loader);
    private localStorage = inject(LocalStorageService);
    private modalService = inject(ModalService);
    private regionPolicy = inject(RegionPolicyService);
    private router = inject(Router);
    private settingsService = inject(SettingsService);
    private siteSettingsService = inject(SiteSettingsService);
    private snackbarService = inject(SnackbarService);
    private virtualKeyboardService = inject(VirtualKeyboardService);

    postModel = signal<PostFormValue>({
        title: "",
        content: "",
        postStatus: "R",
    });

    public environment = environment;
    public user: any = {};
    public forums: any = [];
    public posts: any = [];
    public activeForum: number = null;
    public activeForumName: string = "";
    public isLoadingPosts: boolean = false;
    public modalTitle = "New Topic";
    public noForums: boolean = false;
    public noPosts: boolean = false;
    public paramsFetched: boolean = false;
    public postForm: FieldTree<PostFormValue>;
    public postList: any = [];
    private postsRequestId = 0;
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

    constructor() {
        this.cookieService
            .get("AuthUser")
            .then((value) => {
                if (value == "") {
                    throw new Error(value);
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

                this.forumDataSubscription = this.forumService.forumObj.subscribe((obj) => {
                    if (obj && Object.keys(obj).length > 0) {
                        this.localStorage.setItem("forumParams", JSON.stringify(obj));
                        void this.getForums(obj);
                        this.paramsFetched = true;
                    }
                });

                this.postReportDoneSubscription = this.forumService.postReportDone.subscribe(
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
        this.postForm = form(this.postModel, (path) => {
            required(path.title);
            required(path.content);

            validate(path.title, ({ value }) =>
                value().trim() === ""
                    ? { kind: "blank", message: "A value is required" }
                    : undefined,
            );
            validate(path.content, ({ value }) =>
                value().trim() === ""
                    ? { kind: "blank", message: "A value is required" }
                    : undefined,
            );
        });
        this.localStorage.removeItem("postId");
        this.cookieService
            .get("AuthUser")
            .then((value) => {
                if (value == "") {
                    throw new Error(value);
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
                    throw new Error(value);
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
            if (!this.localStorage.getItem("forumParams")) return;

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
        const requestId = ++this.postsRequestId;
        this.loader.setLoader(true);
        this.isLoadingPosts = true;
        return this.forumService
            .getForums(params)
            .then((res) => {
                if (!res) {
                    return;
                }
                if (requestId !== this.postsRequestId) return;

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
                        if (requestId !== this.postsRequestId) return;

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
                if (requestId !== this.postsRequestId) return;

                console.error("[forum-list] Error getting forums. ", err);

                this.snackbarService.showSnackbar({
                    status: false,
                    msg: "There has been an error. Please try again after some time while we fix it.",
                });
            })
            .finally(() => {
                if (requestId !== this.postsRequestId) return;

                this.isLoadingPosts = false;
                this.loader.setLoader(false);
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
        const requestId = ++this.postsRequestId;

        if (this.searchPost && this.searchTxt.trim() != "" && this.searchTxt != undefined) {
            params.q = this.searchTxt;
        }

        this.loader.setLoader(true);
        this.isLoadingPosts = true;

        this.forumService
            .getForumPosts(params)
            .then((res) => {
                if (requestId !== this.postsRequestId) return;

                const results = res.data.results;

                this.posts = page === 1 ? results.items : [...this.posts, ...results.items];
                this.currentPage = res.data.results.pageinfo.currentpage;
                this.totalPage = res.data.results.pageinfo.totalpage;
                this.noPosts = this.posts.length === 0;
            })
            .catch((err) => {
                if (requestId !== this.postsRequestId) return;

                console.error(err);

                this.snackbarService.showSnackbar({
                    status: false,
                    msg: "There has been an error. Please try again after some time while we fix it.",
                });
            })
            .finally(() => {
                if (requestId !== this.postsRequestId) return;

                this.loader.setLoader(false);
                this.isLoadingPosts = false;
            });
    }

    private getPostsForFlagForum(page: number = 1) {
        const timeZoneOffset = new Date().getTimezoneOffset() * 60;
        const params: any = {
            page: page,
            timestamp_offset:
                timeZoneOffset > 0 ? -Math.abs(timeZoneOffset) : Math.abs(timeZoneOffset),
        };
        const requestId = ++this.postsRequestId;

        if (this.searchPost && this.searchTxt.trim() != "" && this.searchTxt != undefined) {
            params.q = this.searchTxt;
        }

        this.loader.setLoader(true);
        this.isLoadingPosts = true;

        this.forumService
            .flagPostList(params)
            .then((res) => {
                if (requestId !== this.postsRequestId) return;

                const results = res.data.results;

                this.noPosts = false;

                this.posts = page === 1 ? results.items : [...this.posts, ...results.items];

                this.currentPage = res.data.results.pageinfo.currentpage;
                this.totalPage = res.data.results.pageinfo.totalpage;
                this.noPosts = this.posts.length === 0;
            })
            .catch((err) => {
                if (requestId !== this.postsRequestId) return;

                console.error("[forum-list] Error getting flag post list. ", err);

                this.snackbarService.showSnackbar({
                    status: false,
                    msg: "There has been an error. Please try again after some time while we fix it.",
                });
            })
            .finally(() => {
                if (requestId !== this.postsRequestId) return;

                this.loader.setLoader(false);
                this.isLoadingPosts = false;
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
            this.postModel.update((value) => ({
                ...value,
                title: postObj.title,
                content: postObj.content,
                postStatus: postObj.status ?? "R",
            }));
            setTimeout(() => {
                this.modalService.openModal("#" + name);
                this.modalTitle = "Edit Topic";
            }, 100);
        } else {
            this.postId = null;
            this.postModel.update((value) => ({
                ...value,
                title: "",
                content: "",
                postStatus: "R",
            }));
            this.modalService.openModal("#" + name);
            this.modalTitle = "New Topic";
        }
    }

    createPost(event: Event) {
        event.preventDefault();

        void submit(this.postForm, {
            action: async () => {
                const { title, content, postStatus } = this.postModel();
                if (this.postId) {
                    const params: any = {
                        forum_id: this.activeForum,
                        post_id: this.postId,
                        user_id: this.user.id,
                        title,
                        content,
                    };
                    if (
                        (this.user.role_id == 4 || this.user.role_id == 1) &&
                        this.activeForum == null
                    ) {
                        params.status = postStatus;
                    }
                    this.loader.setLoader(true);
                    await this.updatePosts(params);
                } else {
                    const params = {
                        forum_id: this.activeForum,
                        post_id: this.postId,
                        user_id: this.user.id,
                        title,
                        content,
                        type: "create",
                    };
                    this.loader.setLoader(true);
                    await this.createPostApiCall(params);
                }
            },
            onInvalid: () => {
                this.snackbarService.showSnackbar({
                    status: false,
                    msg: "Please fill in all fields with valid data before moving forward.",
                });
            },
        });
        this.virtualKeyboard.hide();
    }

    private createPostApiCall(params) {
        return this.forumService
            .createPost(params)
            .then((res: any) => {
                this.loader.setLoader(false);
                this.snackbarService.showSnackbar({
                    status: res.data.status,
                    msg: res.data.message,
                });
                if (res.data.status) {
                    this.modalService.closeModal("#newPost");
                    this.reloadPosts();
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

    onInputValueChange(event: { inputName: string; value: string }): void {
        this.inputs[event.inputName] = event.value;

        if (event.inputName === "input_id") {
            this.postModel.update((model) => ({ ...model, title: event.value }));
        } else if (event.inputName === "textarea_id") {
            this.postModel.update((model) => ({ ...model, content: event.value }));
        }
    }

    openReportPostModal(postObj) {
        const params = {
            postToReport: postObj,
            userId: this.user.id,
        };
        this.forumService.openReportPostModal(params);
    }

    private updatePosts(params) {
        return this.forumService
            .updatePost(params)
            .then((res: any) => {
                this.loader.setLoader(false);
                this.snackbarService.showSnackbar({
                    status: res.data.status,
                    msg: res.data.message,
                });
                if (res.data.status) {
                    this.modalService.closeModal("#newPost");
                    this.reloadPosts();
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

    private reloadPosts(): void {
        this.currentPage = 1;
        this.posts = [];
        this.noPosts = false;
        this.postList = [];
        if ((this.user.role_id == 4 || this.user.role_id == 1) && this.activeForum == null) {
            this.getPostsForFlagForum();
        } else {
            this.getPostsForForum();
        }
    }

    goToDetails(post) {
        this.forumService.setForumId(post.id);
        void this.router.navigate(["/forum-post-details"]);
    }

    onScroll() {
        if (!this.isLoadingPosts && this.currentPage < this.totalPage) {
            if ((this.user.role_id == 4 || this.user.role_id == 1) && this.activeForum == null) {
                this.getPostsForFlagForum(this.currentPage + 1);
            } else {
                this.getPostsForForum(this.currentPage + 1);
            }
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
