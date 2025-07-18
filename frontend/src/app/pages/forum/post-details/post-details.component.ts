import { Component, OnInit, ElementRef, Renderer2, ViewChild, OnDestroy } from "@angular/core";
import { Location } from "@angular/common";
import { Router } from "@angular/router";
import { UntypedFormGroup, UntypedFormControl } from "@angular/forms";
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
import { DeviceDetectorService } from "ngx-device-detector";

declare let jQuery: any;
declare let bootbox: any;

@Component({
    selector: "app-post-details",
    templateUrl: "./post-details.component.html",
    styleUrls: ["./post-details.component.scss"],
})
export class PostDetailsComponent implements OnInit, OnDestroy {
    public environment = environment;
    public keyType: any = null;
    public keyboardFlag: boolean = false;
    public newPostKeyboardFlag: boolean = false;

    private forumIdSubscription: Subscription;
    private postReportDoneSubscription: Subscription;
    public postDetails: any = {};
    public noPostFlag: any = false;
    public paramsFetched: any = false;
    public hideNewPostBtn: any = true;
    public user: any = {};
    public postForm: UntypedFormGroup;
    public newPostForm: UntypedFormGroup;
    public postId: number = null;
    public levelbadgeFlag: boolean = false;
    public modalTitle: string = "New Post";
    private keyboardVisibilitySubscription: Subscription;
    public isDesktop: boolean = false;

    // For virtual keyboard
    public inputs: any = {
        input_id: "",
        textarea_id: "",
        reptextarea_id: "",
    };
    public inputAreaClicked: boolean = false;
    @ViewChild("virtualKeyboard") virtualKeyboard: VirtualKeyboardComponent;

    constructor(
        private forumService: ForumService,
        private loader: Loader,
        private cookieService: CookieService,
        private router: Router,
        private renderer: Renderer2,
        private localStorage: LocalStorageService,
        private badgeService: BadgeService,
        private settingsService: SettingsService,
        private snackbarService: SnackbarService,
        private myElement: ElementRef,
        private _location: Location,
        private deviceDetector: DeviceDetectorService,
        private virtualKeyboardService: VirtualKeyboardService,
    ) {
        this.getDeviceInfo();

        this.cookieService
            .get("AuthUser")
            .then((value) => {
                if (value == "") {
                    throw value;
                }
                this.user = JSON.parse(value);
                this.forumIdSubscription = forumService.forumObjId.subscribe((obj) => {
                    if (obj && obj != "") {
                        this.localStorage.setItem("postId", obj);
                        this.getForumDetails(obj);
                        this.paramsFetched = true;
                    }
                });

                this.postReportDoneSubscription = forumService.postReportDone.subscribe(
                    (status) => {
                        // Reload posts is data is available, otherwise reload page
                        if (status == true) {
                            const postId = this.localStorage.getItem("postId");
                            if (!!postId) {
                                this.getForumDetails(JSON.parse(postId));
                            } else {
                                void this.router.navigate(["/forum-post-details"]);
                            }
                        }
                    },
                );

                this.keyboardVisibilitySubscription =
                    this.virtualKeyboardService.keyboardVisibility.subscribe((visible) => {
                        this.keyboardFlag = visible;
                    });
            })
            .catch(() => {
                void this.router.navigate([""]);
            });
    }

    ngOnInit() {
        this.postForm = new UntypedFormGroup({
            content: new UntypedFormControl("", [this.validateBlankValue.bind(this)]),
        });

        this.newPostForm = new UntypedFormGroup({
            title: new UntypedFormControl("", [this.validateBlankValue.bind(this)]),
            content: new UntypedFormControl("", [this.validateBlankValue.bind(this)]),
            postStatus: new UntypedFormControl("R"),
        });

        if (
            !this.paramsFetched &&
            this.localStorage.getItem("postId") &&
            this.localStorage.getItem("postId") != ""
        ) {
            this.getForumDetails(this.localStorage.getItem("postId"));
        }
    }

    getDeviceInfo() {
        this.isDesktop =
            this.deviceDetector.isDesktop() &&
            !this.deviceDetector.isMobile() &&
            !this.deviceDetector.isTablet();
    }

    private validateBlankValue(control: UntypedFormControl): any {
        if (this.postForm) {
            return control.value.trim() === "" ? { emptyValue: true } : null;
        }
    }

    getForumDetails(id) {
        const timeZoneOffset = new Date().getTimezoneOffset() * 60;
        const params: any = {
            post_id: id,
            timestamp_offset:
                timeZoneOffset > 0 ? -Math.abs(timeZoneOffset) : Math.abs(timeZoneOffset),
            user_id: this.user.id,
        };
        this.loader.setLoader(true);
        this.forumService
            .getSinglePost(params)
            .then((res) => {
                this.loader.setLoader(false);
                if (res && res.data.status) {
                    this.postDetails = res.data.results;
                    this.noPostFlag = false;
                    if (
                        this.postDetails.forum.title == "What’s New" &&
                        (this.user.role_id != 4 || this.user.role_id != 1)
                    ) {
                        this.hideNewPostBtn = true;
                    } else {
                        this.hideNewPostBtn = false;
                    }
                } else {
                    this.noPostFlag = true;
                }
            })
            .catch((err) => {
                this.loader.setLoader(false);
                this._location.back();
                console.error(err);
            });
    }

    ngOnDestroy(): void {
        this.forumIdSubscription.unsubscribe();
        this.postReportDoneSubscription.unsubscribe();
        this.keyboardVisibilitySubscription.unsubscribe();
    }
    imgfunc(allbadge: any, type: string) {
        const imageUrl = this.badgeService.badgeImgSet(allbadge, type);
        return imageUrl;
    }

    openModal(id, type) {
        this.keyboardFlag = false;
        if (type == "reply") {
            jQuery("#replyPost").modal("show");
        } else {
            this.modalTitle = "New Post";
            this.newPostForm.patchValue({
                title: "",
                content: "",
                postStatus: "R",
            });
            jQuery("#newPost").modal("show");
        }
    }

    editPostModal(name, type, postObj) {
        this.keyboardFlag = false;
        if (type == "edit") {
            this.modalTitle = "Edit Post";
            this.postId = postObj.id;
            this.newPostForm.patchValue({
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
                postStatus: "R",
            });
            jQuery("#" + name).modal("show");
        }
    }

    replyPost(form) {
        form.value.content = this.myElement.nativeElement
            .querySelector("#reptextarea_id")
            .value.trim();
        if (form.value.content) {
            this.loader.setLoader(true);
            const data = {
                forum_id: this.postDetails.forum_id,
                user_id: this.user.id,
                //title: form.value.title,
                content: form.value.content,
                type: "reply",
                parent_id: this.postDetails.id,
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
                        jQuery("#replyPost").modal("hide");
                        this.getForumDetails(this.postDetails.id);
                        this.postForm.patchValue({
                            title: "",
                            content: "",
                        });
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
            this.snackbarService.showSnackbar({
                status: false,
                msg: "Please fill in all fields with valid data before moving forward.",
            });
        }
    }

    createPost(form) {
        form.value.title = this.myElement.nativeElement.querySelector("#input_id").value.trim();
        form.value.content = this.myElement.nativeElement
            .querySelector("#textarea_id")
            .value.trim();
        if (form.value.title && form.value.content) {
            this.loader.setLoader(true);
            if (!this.postId) {
                const data = {
                    forum_id: this.postDetails.forum_id,
                    user_id: this.user.id,
                    title: form.value.title,
                    content: form.value.content,
                    type: "create",
                    //parent_id: this.postDetails.id
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
                            void this.router.navigate(["/village"]);
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
                const data: any = {
                    post_id: this.postId,
                    user_id: this.user.id,
                    title: form.value.title,
                    content: form.value.content,
                };

                if (
                    (this.user.role_id == 4 || this.user.role_id == 1) &&
                    this.postDetails.forum_id == null
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
                            this.getForumDetails(this.postId);
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
    }

    goToDetails(post) {
        this.getForumDetails(post.id);
    }

    goBack() {
        if (this.postDetails) {
            if (this.postDetails.parent_id != null) {
                this.getForumDetails(this.postDetails.parent_id);
            } else {
                void this.router.navigate(["/village"]);
            }
        } else {
            void this.router.navigate(["/village"]);
        }
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
                                window.history.back();
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

    toggleReplyPostKeyboard(flag: boolean): void {
        this.toggleVirtualKeyboard(flag);
    }

    toggleNewPostKeyboard(flag: boolean): void {
        this.toggleVirtualKeyboard(flag);
    }

    toggleVirtualKeyboard(flag: boolean): void {
        if (!this.virtualKeyboard) {
            return;
        }

        if (flag) {
            this.virtualKeyboard.show();
        } else {
            this.virtualKeyboard.hide();
        }
    }

    closeVirtualKeyboard(): void {
        if (this.virtualKeyboard) {
            this.virtualKeyboard.hide();
        }
    }

    setActiveInput(event: Event): void {
        if (this.virtualKeyboard) {
            this.virtualKeyboard.onInputFocus(event);
        }
    }

    openReportPostModal(postObj) {
        const params = {
            postToReport: postObj,
            userId: this.user.id,
        };
        this.forumService.openReportPostModal(params);
    }
}
