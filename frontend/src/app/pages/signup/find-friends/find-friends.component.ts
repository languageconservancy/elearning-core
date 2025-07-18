import { Component, OnInit, ChangeDetectorRef, OnDestroy } from "@angular/core";
import { Router } from "@angular/router";
import { Subscription } from "rxjs";
import { CookieService } from "app/_services/cookie.service";

import { FindFriendsService } from "app/_services/find-friends.service";
import { Loader } from "app/_services/loader.service";
import { LocalStorageService } from "app/_services/local-storage.service";
import { RegistrationService } from "app/_services/registration.service";
import { SocialWebService } from "app/_services/social-web.service";
import { environment } from "environments/environment";
import { SettingsService } from "app/_services/settings.service";
import { SnackbarService } from "app/_services/snackbar.service";

declare let gapi: any;

@Component({
    selector: "app-find-friends",
    templateUrl: "./find-friends.component.html",
    styleUrls: ["./find-friends.component.scss"],
})
export class FindFriendsComponent implements OnInit, OnDestroy {
    private userSubscription: Subscription;
    public userId: string;
    public socialModel: any = {};
    public user: any = {};
    public facebookFriends: any = [];
    public facebookFriendsFiltered: any = [];
    public googleFriends: any = [];
    public googleFriendsFiltered: any = [];
    public googleContacts: any = [];
    public googleContactsFiltered: any = [];
    public allUsers: any = [];
    public allUsersFiltered: any = [];
    public mailingList: any = [];
    public mailModel: any = { list: "", body: "" };
    public searchItem: any = {};
    public searchContact: any = {};
    public page: number = 1;
    public limit: number = 20;
    public facebookLoaded: boolean = false;

    constructor(
        private router: Router,
        public friendsService: FindFriendsService,
        private registrationService: RegistrationService,
        private ref: ChangeDetectorRef,
        private loader: Loader,
        private cookieService: CookieService,
        private localStorage: LocalStorageService,
        private settingsService: SettingsService,
        private socialWebService: SocialWebService,
        private snackbarService: SnackbarService,
    ) {
        this.cookieService
            .get("AuthToken")
            .then((value) => {
                if (value == "") {
                    throw value;
                }
            })
            .catch(() => {
                void this.router.navigate([""]);
            });

        this.socialWebService
            .initFacebook()
            .then(() => {
                this.facebookLoaded = true;
            })
            .catch((error) => {
                this.facebookLoaded = false;
                console.warn(error);
            });

        this.userSubscription = this.registrationService.currentUser.subscribe((userId) => (this.userId = userId.id));
    }

    ngOnInit() {
        this.getAllUsers();
        this.cookieService
            .get("AuthUser")
            .then((value) => {
                if (value == "") {
                    throw value;
                }
                this.user = JSON.parse(value);
                // setTimeout(() => {
                // 	if (this.user.google_status == '1') {
                // 		this.socialModel.google = true;
                // 		this.socialChange('google');
                // 	}
                // 	if (this.user.fb_status == '1') {
                // 		this.socialModel.facebook = true;
                // 		this.socialChange('facebook');
                // 	}
                // }, 200);
            })
            .catch((err) => {
                console.warn("No AuthUser cookie", err);
            });
    }

    ngOnDestroy() {
        this.userSubscription.unsubscribe();
    }

    private assignUserFilterCopy() {
        this.facebookFriendsFiltered = Object.assign([], this.facebookFriends);
        this.googleFriendsFiltered = Object.assign([], this.googleFriends);

        this.allUsersFiltered = [];
        for (let i = 0; i < this.page * this.limit; i++) {
            if (this.allUsers[i]) {
                this.allUsersFiltered.push(this.allUsers[i]);
            }
        }
    }

    private assignUserFilterContactCopy() {
        this.googleContactsFiltered = Object.assign([], this.mailingList);
    }

    private setLoader(val: boolean) {
        this.loader.setLoader(val);
    }

    getAllUsers() {
        this.setLoader(true);
        this.friendsService
            .getAllUsers({ user_id: this.userId })
            .then((res) => {
                res.data.results.resultSet.forEach((user) => {
                    if (user.userType == "site") {
                        user.show = true;
                    } else {
                        switch (user.userType) {
                            case "fb":
                                if (this.socialModel.facebook) {
                                    user.show = true;
                                } else {
                                    user.show = false;
                                }
                                break;
                            case "google":
                                if (this.socialModel.google) {
                                    user.show = true;
                                } else {
                                    user.show = false;
                                }
                                break;
                            default:
                                break;
                        }
                    }
                    this.allUsers.push(user);
                });

                setTimeout(() => {
                    for (let i = 0; i < this.page * this.limit; i++) {
                        if (this.allUsers[i]) {
                            this.allUsersFiltered.push(this.allUsers[i]);
                        }
                    }
                    this.setLoader(false);
                }, 20);
            })
            .catch((err) => {
                console.error(err);
                this.setLoader(false);
            });
    }

    loadMore() {
        const start = this.page * this.limit;
        this.page++;
        for (let i = start; i < this.page * this.limit; i++) {
            if (this.allUsers[i]) {
                this.allUsersFiltered.push(this.allUsers[i]);
            }
        }
    }

    search() {
        if (!this.searchItem.search || this.searchItem.search == "") {
            this.assignUserFilterCopy();
            return;
        }
        this.facebookFriendsFiltered = Object.assign([], this.facebookFriends).filter((item) => {
            return item.name && item.name.toLowerCase().indexOf(this.searchItem.search.toLowerCase()) > -1;
        });
        this.googleFriendsFiltered = Object.assign([], this.googleFriends).filter((item) => {
            return item.name && item.name.toLowerCase().indexOf(this.searchItem.search.toLowerCase()) > -1;
        });
        this.allUsersFiltered = Object.assign([], this.allUsers).filter((item) => {
            const googleFriendIdList = [];
            const fbFriendIdList = [];
            this.googleFriendsFiltered.forEach((element) => {
                googleFriendIdList.push(element.email);
            });

            this.facebookFriends.forEach((element) => {
                fbFriendIdList.push(element.fbId);
            });
            if (item.name && item.name.toLowerCase().indexOf(this.searchItem.search.toLowerCase()) > -1) {
                if (this.socialModel.google) {
                    if (this.socialModel.facebook) {
                        return (
                            item.email &&
                            googleFriendIdList.indexOf(item.email) == -1 &&
                            typeof item.fbId != "undefined" &&
                            fbFriendIdList.indexOf(item.fbId) == -1
                        );
                    } else {
                        return item.email && googleFriendIdList.indexOf(item.email) == -1;
                    }
                } else {
                    if (this.socialModel.facebook) {
                        return typeof item.fbId != "undefined" && fbFriendIdList.indexOf(item.fbId) == -1;
                    }
                }
                return true;
            } else {
                return false;
            }
        });
    }

    contactSearch() {
        if (!this.searchContact.search || this.searchContact.search == "") {
            this.assignUserFilterContactCopy();
            return;
        }

        this.googleContactsFiltered = Object.assign([], this.mailingList).filter((item) => {
            return item.name && item.name.toLowerCase().indexOf(this.searchContact.search.toLowerCase()) > -1;
        });
    }

    socialChange(type) {
        switch (type) {
            case "facebook":
                if (this.socialModel.facebook) {
                    this.getFbContacts();
                } else {
                    this.resetFbContacts();
                    this.resetLink(type);
                }
                break;
            case "google":
                if (this.socialModel.google) {
                    this.getGoogleContacts();
                } else {
                    this.resetGoogleContacts();
                    this.resetLink(type);
                }
                break;
            default:
                break;
        }
    }

    getFbContacts() {
        if (!this.facebookLoaded) {
            console.warn("Can't get Facebook contacts. Facebook not loaded.");
            return;
        }

        this.setLoader(true);

        this.socialWebService
            .signInWithFacebook()
            .then((response) => {
                this.socialWebService
                    .api("/" + response.authResponse.userID + "?fields=friends.fields(name,picture)")
                    .then(async (res) => {
                        await this.checkIfFriends(res, "facebook");
                    })
                    .catch((error: any) => {
                        console.error(error);
                        this.setLoader(false);
                    });
            })
            .catch((error: any) => {
                console.error(error);
                this.setLoader(false);
            });
    }

    getGoogleContacts() {
        gapi.client.setApiKey(environment.GOOGLE_API_KEY);
        window.setTimeout(() => {
            this.authorize();
        });
    }

    authorize() {
        this.setLoader(true);
        gapi.auth.authorize(
            { client_id: environment.GOOGLE_CLIENT_ID_WEB, scope: environment.GOOGLE_CONTACT_SCOPE, immediate: false },
            (authorizationResult) => {
                this.handleAuthorization(authorizationResult);
            },
        );
    }

    handleAuthorization(authorizationResult) {
        if (authorizationResult && !authorizationResult.error) {
            this.friendsService
                .getContacts(authorizationResult)
                .then(async (res: any) => {
                    this.googleContacts = res.data.feed.entry;
                    await this.checkIfFriends(res.data.feed.entry, "google");
                })
                .catch((err) => {
                    console.error(err);
                });
        } else {
            this.socialModel.google = false;
        }
        this.setLoader(false);
    }

    async checkIfFriends(obj, type) {
        switch (type) {
            case "facebook":
                this.facebookFriends = [];
                const fbData = {
                    id: this.userId,
                    fb_status: 1,
                    fb_data: JSON.stringify(obj),
                };
                await this.friendsService.checkIfFriends(fbData).then((res) => {
                    this.facebookFriends = res.data.results;
                    this.facebookFriendsFiltered = Object.assign([], this.facebookFriends);
                    this.filterFbUsers();
                });
                break;
            case "google":
                this.googleFriends = [];
                const googleData = {
                    id: this.userId,
                    google_status: 1,
                    google_data: JSON.stringify({ entry: obj }),
                };
                this.friendsService
                    .checkIfFriends(googleData)
                    .then((res: any) => {
                        this.setLoader(false);
                        this.googleFriends = res.data.results;
                        this.googleFriendsFiltered = Object.assign([], this.googleFriends);
                        this.filterGoogleUsers();
                        this.ref.detectChanges();
                    })
                    .catch(() => {
                        this.setLoader(false);
                    });
                break;
            default:
                break;
        }
        this.setLoader(false);
    }

    filterGoogleUsers() {
        this.allUsersFiltered = [];
        this.allUsers.forEach((user) => {
            if (user.userType == "google") {
                user.show = true;
            }
        });

        const googleFriendIdList = [];
        this.googleFriends.forEach((element) => {
            googleFriendIdList.push(element.email);
        });

        setTimeout(() => {
            for (let i = 0; i < this.page * this.limit; i++) {
                if (
                    this.allUsers[i] &&
                    this.allUsers[i].email != "undefined" &&
                    googleFriendIdList.indexOf(this.allUsers[i].email) == -1
                ) {
                    this.allUsersFiltered.push(this.allUsers[i]);
                }
            }
            this.ref.detectChanges();
        }, 20);
    }

    resetGoogleContacts() {
        this.googleFriendsFiltered = this.googleFriends = [];
        this.allUsers.forEach((user) => {
            if (user.userType == "google") {
                user.show = false;
            }
        });
        this.allUsersFiltered = [];
        for (let i = 0; i < this.page * this.limit; i++) {
            if (this.allUsers[i]) {
                this.allUsersFiltered.push(this.allUsers[i]);
            }
        }
    }

    filterFbUsers() {
        this.allUsersFiltered = [];
        this.allUsers.forEach((user) => {
            if (user.userType == "fb") {
                user.show = true;
            }
        });

        const fbFriendIdList = [];
        this.facebookFriends.forEach((element) => {
            fbFriendIdList.push(element.fbId);
        });

        setTimeout(() => {
            for (let i = 0; i < this.page * this.limit; i++) {
                if (
                    this.allUsers[i] &&
                    this.allUsers[i].fbId != "undefined" &&
                    fbFriendIdList.indexOf(this.allUsers[i].fbId)
                ) {
                    this.allUsersFiltered.push(this.allUsers[i]);
                }
            }
        }, 20);
    }

    resetFbContacts() {
        this.facebookFriendsFiltered = this.facebookFriends = [];
        this.allUsers.forEach((user) => {
            if (user.userType == "fb") {
                user.show = false;
            }
        });

        this.allUsersFiltered = [];
        for (let i = 0; i < this.page * this.limit; i++) {
            if (this.allUsers[i]) {
                this.allUsersFiltered.push(this.allUsers[i]);
            }
        }
    }

    async fbInvite() {
        if (!this.facebookLoaded) {
            console.warn("Can't send Facebook invitation. Facebook not loaded");
            return;
        }
        await this.socialWebService.ui({
            method: "send",
            link: environment.ROOT,
        });
    }

    async googleInvite() {
        this.setLoader(true);
        await this.friendsService
            .getGoogleInvitees({ google_data: JSON.stringify({ entry: this.googleContacts }), id: this.userId })
            .then((res) => {
                this.mailingList = res.data.results;
                this.googleContactsFiltered = Object.assign([], this.mailingList);
                this.setLoader(false);
            });
    }

    sendGoogleInvite() {
        this.setLoader(true);
        this.mailModel.list = "";
        this.mailingList.forEach((item) => {
            if (item.checked) {
                this.mailModel.list += item.email + ",";
            }
        });

        setTimeout(() => {
            void this.friendsService
                .sendInvites({ email: this.mailModel.list, userid: this.userId, message: this.mailModel.body })
                .then((res: any) => {
                    this.setLoader(false);
                    this.snackbarService.showSnackbar({ status: true, msg: res.data.message });
                });
        }, 200);
    }

    async makeFriends(status, type, user) {
        switch (type) {
            case "facebook":
                const fbData = {
                    userId: this.userId,
                    friendsFbId: user.fbId,
                    status: status,
                };
                await this.friendsService.addRemoveFriend(fbData).then(() => {
                    user.friendstatus = !user.friendstatus;
                });
                break;
            case "google":
                const googleData = {
                    userId: this.userId,
                    friendsgoogleId: user.email,
                    status: status,
                };
                await this.friendsService.addRemoveFriend(googleData).then(() => {
                    user.friendstatus = !user.friendstatus;
                });
                break;
            case "site":
                const siteData = {
                    userId: this.userId,
                    friendId: user.id,
                    status: status,
                };
                await this.friendsService.addRemoveFriend(siteData).then(() => {
                    user.friendstatus = !user.friendstatus;
                });
                break;
            default:
                break;
        }
    }

    startLearning() {
        this.localStorage.removeItem("regProg");
        void this.router.navigate(["start-learning"]);
    }

    finishReg() {
        this.localStorage.removeItem("regProg");
        void this.router.navigate(["dashboard"]);
    }

    resetLink(type) {
        this.setLoader(true);
        const params: any = { id: this.user.id };
        switch (type) {
            case "google":
                params.google_status = this.socialModel.google ? "1" : "0";
                break;
            case "facebook":
                params.fb_status = this.socialModel.facebook ? "1" : "0";
                break;
            default:
                break;
        }
        this.settingsService
            .updateUserData(params)
            .then(() => {
                this.setLoader(false);
            })
            .catch(() => {
                this.setLoader(false);
            });
    }
}
