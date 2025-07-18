/* eslint-disable @typescript-eslint/unbound-method */
import { Component, OnInit, OnDestroy, AfterViewInit, Renderer2 } from "@angular/core";
import { Router } from "@angular/router";
import { UntypedFormGroup, UntypedFormControl, Validators } from "@angular/forms";
import { CookieService } from "app/_services/cookie.service";
import { Subscription } from "rxjs";

import { ForumService } from "app/_services/forum.service";
import { Loader } from "app/_services/loader.service";
import { LocalStorageService } from "app/_services/local-storage.service";
import { SettingsService } from "app/_services/settings.service";
import { SnackbarService } from "app/_services/snackbar.service";

declare let jQuery: any;

declare let bootbox: any;
@Component({
    selector: "app-forum-general-discussion",
    templateUrl: "./forum-general-discussion.component.html",
    styleUrls: ["./forum-general-discussion.component.scss"],
})
export class ForumGeneralDiscussionComponent implements OnInit, OnDestroy, AfterViewInit {
    public pagenumber = 1;
    public currentPage = 1;
    public totalPage = 1;
    public activeForum: number = null;
    public searchTxt: string = "";
    public searchPost: boolean = false;
    public successMsg: string = "";
    public postCreated: boolean = false;
    public postId: number = null;
    public userId: number = null;
    public noPost: boolean = false;
    public paramsFetched: boolean = false;
    public postList: any = [];
    public user: any = {};
    public postForm: UntypedFormGroup;
    public successFlag: boolean = false;
    public levelbadgeFlag: boolean = false;

    public postDataSubscription: Subscription;
    public postReportDoneSubscription: Subscription;

    constructor(
        private forumService: ForumService,
        private loader: Loader,
        private cookieService: CookieService,
        private router: Router,
        private renderer: Renderer2,
        private localStorage: LocalStorageService,
        private settingsService: SettingsService,
        private snackbarService: SnackbarService,
    ) {
        this.postDataSubscription = forumService.currentUserId.subscribe((obj) => {
            if (obj && Object.keys(obj).length > 0) {
                this.userId = obj.user_id;
                this.getPosts(obj.user_id);
                this.paramsFetched = true;
            }
        });

        this.postReportDoneSubscription = forumService.postReportDone.subscribe((status) => {
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
                    throw value;
                }
                this.user = JSON.parse(value);
            })
            .catch(() => {});
        this.postForm = new UntypedFormGroup({
            title: new UntypedFormControl("", [Validators.required, this.validateBlankValue.bind(this)]),
            content: new UntypedFormControl("", [Validators.required, this.validateBlankValue.bind(this)]),
            postStatus: new UntypedFormControl("R"),
        });
    }

    ngAfterViewInit() {
        if (!this.paramsFetched) {
            this.userId = parseInt(this.localStorage.getItem("publicProfile"));
            this.getPosts(parseInt(this.localStorage.getItem("publicProfile")));
        }
    }

    ngOnDestroy(): void {
        this.postDataSubscription.unsubscribe();
    }

    private validateBlankValue(control: UntypedFormControl): any {
        if (this.postForm) {
            return control.value.trim() === "" ? { emptyValue: true } : null;
        }
    }

    getPosts(user_id: any, page: number = 1) {
        const timeZoneOffset = new Date().getTimezoneOffset() * 60;
        const params: any = {
            user_id: user_id,
            page: page,
            forum_id: this.activeForum,
            type: "postbyuser",
            timestamp_offset: timeZoneOffset > 0 ? -Math.abs(timeZoneOffset) : Math.abs(timeZoneOffset),
        };
        this.loader.setLoader(true);
        if (this.searchPost && this.searchTxt.trim() != "" && this.searchTxt != undefined) {
            params.q = this.searchTxt;
        }
        this.forumService
            .getForumPosts(params)
            .then((res) => {
                this.loader.setLoader(false);
                if (res.data.results.items.length > 0) {
                    res.data.results.items.forEach((post) => {
                        this.postList.push(post);
                    });
                    this.currentPage = res.data.results.pageinfo.currentpage;
                    this.totalPage = res.data.results.pageinfo.totalpage;
                    this.noPost = false;
                } else {
                    this.noPost = true;
                }
            })
            .catch((err) => {
                this.loader.setLoader(false);
                console.error(err);
            });
    }

    onScroll() {
        if (this.currentPage != this.totalPage) {
            this.pagenumber = this.pagenumber + 1;
            this.getPosts(this.userId, this.pagenumber);
        }
    }

    goToProfile(user) {
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
            this.postForm.patchValue({
                title: postObj.title,
                content: postObj.content,
            });
            setTimeout(() => {
                jQuery("#" + name).modal("show");
            }, 100);
        } else {
            this.postForm.patchValue({
                title: "",
                content: "",
            });
            jQuery("#" + name).modal("show");
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
                                this.postList = [];
                                //changes on 7th may
                                this.getPosts(this.userId);
                                //changes on 7th may
                            }
                        })
                        .catch((err) => {
                            console.error(err);
                            this.loader.setLoader(false);
                            this.snackbarService.showSnackbar({
                                status: false,
                                msg: "There has been an error. Please try again after some time while we fix it.",
                            });
                            this.postList = [];
                            this.getPosts(this.userId);
                        });
                }
            },
        });
    }

    search() {
        this.searchPost = true;
        this.postList = [];
        this.getPosts(this.userId);
    }

    clear() {
        this.searchPost = false;
        this.searchTxt = "";
        this.postList = [];
        this.getPosts(this.userId);
    }
    imgfunc(allbadge: any, type: string) {
        if (type == "firebadges") {
            const fire_days = allbadge.fire_days;
            let imagsrc = "./assets/images/fire_dead.png";
            if (fire_days > 0 && fire_days < 3) {
                imagsrc = "./assets/images/fire_low.png";
            } else if (fire_days >= 3 && fire_days < 7) {
                imagsrc = "./assets/images/fire_med.png";
            } else if (fire_days >= 7 && fire_days < 14) {
                imagsrc = "./assets/images/fire_high.png";
            } else if (fire_days >= 14) {
                imagsrc = "./assets/images/fire_ultra.png";
            }
            return imagsrc;
        } else if (type == "levelbadge") {
            const levelbadge = allbadge;
            levelbadge.reverse();
            levelbadge.forEach(function (element) {
                if (element.status) {
                    this.levelbadgeFlag = true;
                    return element.image;
                }
            });
        } else if (type == "socialpoint") {
            let imagsrc = "";
            if (allbadge > 4 && allbadge < 100) {
                imagsrc = "./assets/images/fire_low.png";
            } else if (allbadge >= 100 && allbadge < 250) {
                imagsrc = "./assets/images/fire_med.png";
            } else if (allbadge >= 250 && allbadge < 1000) {
                imagsrc = "./assets/images/fire_high.png";
            } else if (allbadge >= 1000 && allbadge < 5000) {
                imagsrc = "./assets/images/fire_high.png";
            } else if (allbadge >= 5000) {
                imagsrc = "./assets/images/fire_ultra.png";
            }
            return imagsrc;
        }
    }
    private createPostApiCall(params) {
        this.forumService
            .createPost(params)
            .then((res: any) => {
                this.loader.setLoader(false);
                this.snackbarService.showSnackbar({
                    status: res.data.status,
                    msg: res.data.message,
                });
                if (res.data.status) {
                    jQuery("#newPost").modal("hide");
                    this.postList = [];
                    this.getPosts(this.userId);
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
        this.forumService
            .updatePost(params)
            .then((res: any) => {
                this.loader.setLoader(false);
                this.snackbarService.showSnackbar({
                    status: res.data.status,
                    msg: res.data.message,
                });
                if (res.data.status) {
                    jQuery("#newPost").modal("hide");
                    this.postList = [];
                    this.getPosts(this.userId);
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

    createPost(form) {
        if (form.valid) {
            this.loader.setLoader(true);
            if (this.postId) {
                const params: any = {
                    post_id: this.postId,
                    user_id: this.user.id,
                    title: form.value.title,
                    content: form.value.content,
                };
                if ((this.user.role_id == 4 || this.user.role_id == 1) && this.activeForum == null) {
                    params.status = form.value.postStatus;
                }
                this.updatePostApiCall(params);
            } else {
                const params = {
                    forum_id: this.activeForum,
                    user_id: this.user.id,
                    title: form.value.title,
                    content: form.value.content,
                    type: "create",
                };
                this.createPostApiCall(params);
            }
        } else {
            this.snackbarService.showSnackbar({
                status: false,
                msg: "Please fill in all fields with valid data before moving forward.",
            });
        }
    }
}
