import { Component, OnInit, OnDestroy, AfterViewInit, inject, signal } from "@angular/core";
import { form, FieldTree, FormField, required, submit, validate } from "@angular/forms/signals";
import { FormsModule } from "@angular/forms";
import { Router } from "@angular/router";
import { InfiniteScrollDirective } from "ngx-infinite-scroll";
import { Subscription } from "rxjs";

import { CookieService } from "@app/_services/cookie.service";
import { FriendSidebarComponent } from "@app/pages/forum/_partials/friend-sidebar/friend-sidebar.component";
import { PageTitleComponent } from "@app/_partials/page-title/page-title.component";
import { ReportPostModalComponent } from "@app/pages/forum/_partials/report-post-modal/report-post-modal.component";
import { DirectivesModule } from "@app/_directives/directives.module";
import { ForumService } from "@app/_services/forum.service";
import { Loader } from "@app/_services/loader.service";
import { LocalStorageService } from "@app/_services/local-storage.service";
import { ModalService } from "@app/_services/modal.service";
import { SettingsService } from "@app/_services/settings.service";
import { SnackbarService } from "@app/_services/snackbar.service";

interface PostFormValue {
    title: string;
    content: string;
    postStatus: string;
}

declare let bootbox: any;
@Component({
    selector: "app-forum-general-discussion",
    imports: [
        DirectivesModule,
        FormField,
        FormsModule,
        InfiniteScrollDirective,
        ReportPostModalComponent,
        FriendSidebarComponent,
        PageTitleComponent,
    ],
    templateUrl: "./forum-general-discussion.component.html",
    styleUrls: ["./forum-general-discussion.component.scss"],
    standalone: true,
})
export class ForumGeneralDiscussionComponent implements OnInit, OnDestroy, AfterViewInit {
    private forumService = inject(ForumService);
    private loader = inject(Loader);
    private cookieService = inject(CookieService);
    private router = inject(Router);
    private localStorage = inject(LocalStorageService);
    private modalService = inject(ModalService);
    private settingsService = inject(SettingsService);
    private snackbarService = inject(SnackbarService);

    postModel = signal<PostFormValue>({
        title: "",
        content: "",
        postStatus: "R",
    });

    public currentPage = 1;
    public totalPage = 1;
    public activeForum: number = null;
    public modalTitle = "New Topic";
    public searchTxt: string = "";
    public searchPost: boolean = false;
    public postId: number = null;
    public userId: number = null;
    public noPost: boolean = false;
    public paramsFetched: boolean = false;
    public postList: any = [];
    private postsRequestId = 0;
    public isLoadingPosts = false;
    public user: any = {};
    public postForm: FieldTree<PostFormValue>;
    public successFlag: boolean = false;
    public postDataSubscription: Subscription;
    public postReportDoneSubscription: Subscription;

    constructor() {
        this.postDataSubscription = this.forumService.currentUserId.subscribe((obj) => {
            if (obj && Object.keys(obj).length > 0) {
                this.userId = obj.user_id;
                this.getPosts(obj.user_id);
                this.paramsFetched = true;
            }
        });

        this.postReportDoneSubscription = this.forumService.postReportDone.subscribe((status) => {
            // Reload posts is data is available, otherwise reload page
            if (status == true) {
                const postId = this.localStorage.getItem("postId");
                if (!!postId) {
                    this.getPosts(JSON.parse(postId));
                } else {
                    void this.router.navigate(["/posts-by-user"]);
                }
            }
        });
    }

    ngOnInit() {
        this.cookieService
            .get("AuthUser")
            .then((value) => {
                if (value == "") {
                    throw new Error(value);
                }
                this.user = JSON.parse(value);
            })
            .catch((err) => {
                console.error(err);
            });
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
    }

    ngAfterViewInit() {
        if (!this.paramsFetched) {
            if (!this.localStorage.getItem("publicProfile")) return;

            this.userId = parseInt(this.localStorage.getItem("publicProfile"));
            this.getPosts(parseInt(this.localStorage.getItem("publicProfile")));
        }
    }

    ngOnDestroy(): void {
        this.postDataSubscription.unsubscribe();
        this.postReportDoneSubscription.unsubscribe();
    }

    getPosts(user_id: any, page: number = 1) {
        const requestId = ++this.postsRequestId;
        const timeZoneOffset = new Date().getTimezoneOffset() * 60;
        const params: any = {
            user_id: user_id,
            page: page,
            forum_id: this.activeForum,
            type: "postbyuser",
            timestamp_offset:
                timeZoneOffset > 0 ? -Math.abs(timeZoneOffset) : Math.abs(timeZoneOffset),
        };
        this.isLoadingPosts = true;
        this.loader.setLoader(true);
        if (this.searchPost && this.searchTxt.trim() != "" && this.searchTxt != undefined) {
            params.q = this.searchTxt;
        }
        this.forumService
            .getForumPosts(params)
            .then((res) => {
                if (requestId !== this.postsRequestId) return;

                const results = res.data.results;

                this.postList = page === 1 ? results.items : [...this.postList, ...results.items];

                this.currentPage = results.pageinfo.currentpage;
                this.totalPage = results.pageinfo.totalpage;
                this.noPost = this.postList.length === 0;
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

                this.isLoadingPosts = false;
                this.loader.setLoader(false);
            });
    }

    onScroll() {
        if (!this.isLoadingPosts && this.currentPage < this.totalPage) {
            this.getPosts(this.userId, this.currentPage + 1);
        }
    }

    goToProfile(user): void {
        if (!user?.id) return;

        this.localStorage.setItem("publicProfile", user.id);
        this.settingsService.setPublicProfile({ profile_id: user.id });
        void this.router.navigate(["profile"]);
    }

    goToDetails(post) {
        this.forumService.setForumId(post.id);
        void this.router.navigate(["/forum-post-details"]);
    }

    openReportPostModal(postObj) {
        const params = {
            postToReport: postObj,
            userId: this.user.id,
        };
        this.forumService.openReportPostModal(params);
    }

    openModal(name, type, postObj) {
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
                                status: res.data.status,
                                msg: res.data.message,
                            });
                            if (res.data.status) {
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
            },
        });
    }

    private reloadPosts(): void {
        this.currentPage = 1;
        this.totalPage = 1;
        this.noPost = false;
        this.postList = [];
        this.getPosts(this.userId, 1);
    }

    search(): void {
        this.searchPost = this.searchTxt.trim() !== "";
        this.reloadPosts();
    }

    clear(): void {
        this.searchPost = false;
        this.searchTxt = "";
        this.reloadPosts();
    }

    private createPostApiCall(params) {
        this.loader.setLoader(true);
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

    private updatePostApiCall(params) {
        this.loader.setLoader(true);
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

    createPost(event: Event) {
        event.preventDefault();

        void submit(this.postForm, {
            action: async () => {
                const { title, content, postStatus } = this.postModel();
                if (this.postId) {
                    const params: any = {
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
                    await this.updatePostApiCall(params);
                } else {
                    const params = {
                        forum_id: this.activeForum,
                        user_id: this.user.id,
                        title,
                        content,
                        type: "create",
                    };
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
    }
}
