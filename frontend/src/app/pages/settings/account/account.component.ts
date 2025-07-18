import { Component, OnInit, ChangeDetectorRef, OnDestroy } from "@angular/core";
import { UntypedFormGroup, UntypedFormControl, Validators } from "@angular/forms";
import { Router } from "@angular/router";
import { Subscription } from "rxjs";
import { CookieService } from "app/_services/cookie.service";

import { FindFriendsService } from "app/_services/find-friends.service";
import { Loader } from "app/_services/loader.service";
import { LocalStorageService } from "app/_services/local-storage.service";
import { SettingsService } from "app/_services/settings.service";
import { SocialWebService } from "app/_services/social-web.service";
import { SnackbarService } from "app/_services/snackbar.service";
import { environment } from "environments/environment";
import { BaseService } from "app/_services/base.service";

declare let gapi: any;
declare let jQuery: any;

@Component({
    selector: "app-account",
    templateUrl: "./account.component.html",
    styleUrls: ["./account.component.scss"],
})
export class AccountComponent implements OnInit, OnDestroy {
    private lockSubscription: Subscription;
    public socialModel: any = {};
    public googleContacts: any = [];
    public googleContactsFiltered: any = [];
    public mailingList: any = [];
    public mailModel: any = { list: "", body: "" };
    public contactsFetchFlag: boolean = false;
    public user: any;
    public searchContact: any = {};
    public changeForm: UntypedFormGroup;
    public lockFlag: boolean = false;
    public userType: string = "";
    public password: string = "";
    public resetDataPasswordError: boolean = false;
    public environment = environment;
    public facebookLoaded = false;
    public facebookConfigValid: boolean = false;
    public googleConfigValid: boolean = false;
    public accountDeletion: any = {
        error: false,
        errorMsg: "",
        inputValue: "",
    };

    constructor(
        private settingsService: SettingsService,
        public friendsService: FindFriendsService,
        private ref: ChangeDetectorRef,
        private loader: Loader,
        private cookieService: CookieService,
        private localStorage: LocalStorageService,
        private router: Router,
        private socialWebService: SocialWebService,
        private snackbarService: SnackbarService,
        private baseService: BaseService,
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

        this.checkSocialLoginConfigs();

        if (this.facebookConfigValid) {
            this.socialWebService
                .initFacebook()
                .then(() => {
                    this.facebookLoaded = true;
                    this.facebookConfigValid = true;
                })
                .catch((error) => {
                    console.warn("[account] Error initting facebook. ", error);
                });
        }

        this.lockSubscription = this.settingsService.parentalLockCode.subscribe(() => (this.lockFlag = false));
        this.settingsService.setTab("account");
    }

    ngOnInit() {
        this.changeForm = new UntypedFormGroup({
            // eslint-disable-next-line @typescript-eslint/unbound-method
            oldpassword: new UntypedFormControl("", Validators.required),
            // eslint-disable-next-line @typescript-eslint/unbound-method
            password: new UntypedFormControl("", Validators.required),
            confirmpassword: new UntypedFormControl("", [
                // eslint-disable-next-line @typescript-eslint/unbound-method
                Validators.required,
                this.validatePasswordConfirmation.bind(this),
            ]),
        });

        this.cookieService
            .get("AuthUser")
            .then((value) => {
                if (value == "") {
                    throw value;
                }
                this.setLoader(true);
                const loggedInUser = JSON.parse(value);
                this.settingsService
                    .getAllSettings(loggedInUser.id)
                    .then((res) => {
                        this.setLoader(false);
                        if (res.data.status) {
                            this.user = res.data.results[0];
                            this.userType = res.data.results.registration_type;

                            const parentalLock = this.localStorage.getItem("parentalLockCode");
                            if (this.user.usersetting.parental_lock_on == "1") {
                                this.lockFlag = this.user.usersetting.parental_lock
                                    ? parentalLock == this.user.usersetting.parental_lock
                                        ? false
                                        : true
                                    : false;
                            }
                            this.setUpModels();
                        } else {
                            this.alreadyDeleted();
                        }
                    })
                    .catch((err) => {
                        this.setLoader(false);
                        if (!err.ok) {
                            console.error("[account] Error getting user settings. ", err);
                            this.alreadyDeleted();
                        }
                    });
            })
            .catch((err) => {
                console.error(err);
            });
    }

    checkSocialLoginConfigs() {
        this.facebookConfigValid = environment.FACEBOOK_APP_ID.trim() != "";
        this.googleConfigValid = environment.GOOGLE_CLIENT_ID_WEB.trim() != "";
    }

    ngOnDestroy() {
        this.lockSubscription.unsubscribe();
    }

    private validatePasswordConfirmation(control: UntypedFormControl): any {
        if (this.changeForm) {
            return control.value === this.changeForm.get("password").value ? null : { notSame: true };
        }
    }

    private setLoader(val: boolean) {
        this.loader.setLoader(val);
    }

    private assignUserFilterContactCopy() {
        this.googleContactsFiltered = Object.assign([], this.mailingList);
    }

    setUpModels() {
        this.socialModel.facebook = parseInt(this.user.fb_status) == 1;
        this.socialModel.google = parseInt(this.user.google_status) == 1;
    }

    isTrial() {
        return !this.userEmailValid() && this.user?.email.substring(0, 5) == "trial";
    }

    userEmailValid() {
        return this.user?.email.indexOf("@") >= 0;
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

    async socialChange(type) {
        switch (type) {
            case "facebook":
                this.setLoader(true);
                const fbData = this.socialModel.facebook ? "1" : "0";
                try {
                    const res = await this.settingsService.updateUserData({ id: this.user.id, fb_status: fbData });
                    this.setLoader(false);
                    this.user = res.data.results[0];
                    try {
                        await this.baseService.setAuthUserCookie(this.user);
                    } catch (err) {
                        this.snackbarService.handleError(err, "Error setting user cookie.");
                    }
                } catch (err) {
                    this.setLoader(false);
                    console.log("[account] Error updating user data. ", err);
                    this.snackbarService.showSnackbar({
                        status: false,
                        msg: "Could not be saved. Please try again.",
                    });
                }
                break;
            case "google":
                this.setLoader(true);
                const googleData = this.socialModel.google ? "1" : "0";
                console.log("googleData", googleData);
                try {
                    const res = await this.settingsService.updateUserData({
                        id: this.user.id,
                        google_status: googleData,
                    });
                    this.user = res.data.results[0];
                    try {
                        await this.baseService.setAuthUserCookie(this.user);
                    } catch (err) {
                        this.snackbarService.handleError(err, "Error setting user cookie.");
                    }
                    this.setLoader(false);
                    if (this.socialModel.google) {
                        // this.getGoogleContacts();
                    }
                } catch (err) {
                    console.error("[account] Error updating user data. ", err);
                    this.setLoader(false);
                    this.snackbarService.showSnackbar({
                        status: false,
                        msg: "Could not be saved. Please try again.",
                    });
                }
                break;
            default:
                break;
        }
    }

    getGoogleContacts() {
        gapi.client.setApiKey(environment.GOOGLE_API_KEY);
        window.setTimeout(() => {
            this.authorize();
        });
    }

    authorize() {
        this.setLoader(true);
        const authData = {
            client_id: environment.GOOGLE_CLIENT_ID_WEB,
            scope: environment.GOOGLE_CONTACT_SCOPE,
            immediate: false,
        };
        gapi.auth.authorize(authData, (authorizationResult) => {
            this.handleAuthorization(authorizationResult);
        });
    }

    handleAuthorization(authorizationResult) {
        this.setLoader(false);
        if (authorizationResult && !authorizationResult.error) {
            this.friendsService
                .getContacts(authorizationResult)
                .then((res: any) => {
                    this.googleContacts = res.feed.entry;
                    if (this.contactsFetchFlag) {
                        this.googleInvite();
                    }
                })
                .catch((err) => {
                    console.error("[account] Error getting contacts. ", err);
                });
        } else {
            this.socialModel.google = false;
        }
    }

    fbInvite() {
        if (!this.facebookLoaded) {
            console.warn("Can't invite on Facebook. Facebook not loaded.");
            return;
        }

        this.socialWebService
            .ui({
                method: "send",
                link: environment.ROOT,
            })
            .then((res) => {
                console.log("[account] Facebook invite sent. ", res);
            })
            .catch((err) => {
                console.error("[account] Error sending facebook invite. ", err);
            });
    }

    googleInvite() {
        if (this.googleContacts.length > 0) {
            this.setLoader(true);
            const data = { google_data: JSON.stringify({ entry: this.googleContacts }), id: this.user.id };
            this.friendsService
                .getGoogleInvitees(data)
                .then((res) => {
                    this.mailingList = res.data.results;
                    this.googleContactsFiltered = Object.assign([], this.mailingList);
                    this.setLoader(false);
                    this.contactsFetchFlag = false;
                    this.ref.detectChanges();
                })
                .catch((err) => {
                    console.error("[account] Error inviting google friends. ", err);
                    this.setLoader(false);
                });
        } else {
            this.contactsFetchFlag = true;
            this.getGoogleContacts();
            this.setLoader(false);
        }
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
            const data = { email: this.mailModel.list, user_id: this.user.id, message: this.mailModel.body };
            this.friendsService
                .sendInvites(data)
                .then((res) => {
                    this.snackbarService.showSnackbar({
                        status: res.data.status,
                        msg: res.data.message,
                    });
                })
                .catch((err) => {
                    console.error("[account] Error sending google invite. ", err);
                })
                .finally(() => {
                    this.setLoader(false);
                });
        }, 200);
    }

    changePassword(form: { valid: any; value: { oldpassword: any; password: any } }) {
        if (form.valid) {
            this.setLoader(true);
            const data = {
                id: this.user.id,
                current_password: form.value.oldpassword,
                new_password: form.value.password,
            };
            this.settingsService
                .resetPassword(data)
                .then((res: any) => {
                    this.setLoader(false);
                    this.snackbarService.showSnackbar({
                        status: res.data.status,
                        msg: res.data.message,
                    });
                    if (res.data.status) {
                        jQuery("#resetPass").modal("hide");
                    }
                })
                .catch((err) => {
                    console.error("[account] Error reseting password. ", err);
                    this.setLoader(false);
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

    keyPressed(event) {
        if (event.keyCode == 13) {
            this.resetProgressData();
        }
    }

    clearPasswordData() {
        this.resetDataPasswordError = false;
        this.password = "";
    }

    resetProgressData() {
        if (this.userType == "site") {
            if (this.password == "") {
                this.resetDataPasswordError = true;
                return;
            } else {
                this.resetDataPasswordError = false;
            }
        }

        const params = {
            user_id: this.user.id,
            email: this.user.email,
            password: this.password,
            type: this.userType,
        };
        this.setLoader(true);
        this.settingsService
            .resetProgressData(params)
            .then((res) => {
                this.setLoader(false);
                if (res.data?.status) {
                    jQuery("#resetProgressData").modal("hide");
                    this.snackbarService.showSnackbar({
                        status: true,
                        msg: "You progress data has been successfully reset.",
                    });
                } else {
                    throw res.data?.message;
                }
            })
            .catch((err) => {
                this.setLoader(false);
                this.resetDataPasswordError = true;
                console.error("[account] Error resetting progress data. ", err);
                this.snackbarService.showSnackbar({
                    status: false,
                    msg: err,
                });
            });
    }

    deactivateProfile() {
        this.settingsService
            .deactivateProfile({ id: this.user.id })
            .then(() => {
                this.setLoader(false);
                this.alreadyDeleted();
            })
            .catch((err) => {
                this.setLoader(false);
                console.error("[account] Error deactivating profile. ", err);
                this.alreadyDeleted();
            });
    }

    openModal(name) {
        jQuery("#" + name).modal("show");
    }

    hideModal(name) {
        jQuery("#" + name).modal("hide");
    }

    alreadyDeleted() {
        void this.cookieService.deleteAll();
        this.localStorage.clear();
        setTimeout(() => {
            void this.router.navigate([""]);
        }, 1000);
    }

    onAccountDeletionInputChange(newValue) {
        this.accountDeletion.error = false;
        this.accountDeletion.errorMsg = "";
        this.accountDeletion.inputValue = newValue;
    }

    modalDeleteAccountPressed() {
        if (this.accountDeletion.inputValue !== "DELETE") {
            this.accountDeletion.error = true;
            this.accountDeletion.errorMsg = 'Please type "DELETE" to confirm account deletion';
            return;
        }
        this.accountDeletion.error = false;
        this.accountDeletion.errorMsg = "";
        this.accountDeletion.inputValue = "";
        this.hideModal("deleteAccount");
        this.openModal("deleteAccountConfirmation");
    }

    deleteUsersAccount() {
        this.setLoader(true);
        this.settingsService
            .deleteAccount({ userId: this.user.id })
            .then((res) => {
                this.setLoader(false);
                this.snackbarService.showSnackbar({
                    status: true,
                    msg: res.data.message,
                });
                setTimeout(() => {
                    void this.baseService.logout();
                }, 1500);
            })
            .catch((err) => {
                this.setLoader(false);
                console.error(err);
                this.snackbarService.showSnackbar({
                    status: false,
                    msg: err.message,
                });
            });
    }
}
