import { Injectable } from "@angular/core";
import { BehaviorSubject } from "rxjs";

import { BaseService } from "./base.service";
import { CookieService } from "./cookie.service";
import * as API from "app/_constants/api.constants";
import { LocalStorageService } from "./local-storage.service";
import { Router } from "@angular/router";
import { SocialAuthService } from "@abacritt/angularx-social-login";
import { ApiResponse } from "app/shared/utils/elearning-types";

@Injectable()
export class RegistrationService extends BaseService {
    private userSubject = new BehaviorSubject<any>({});
    public currentUser = this.userSubject.asObservable();

    constructor(
        public cookieService: CookieService,
        protected localStorageService: LocalStorageService,
        protected router: Router,
        protected socialAuthService: SocialAuthService,
    ) {
        super(cookieService, localStorageService, router, socialAuthService);
    }

    register(data: any) {
        const promise = new Promise((resolve, reject) => {
            this.callApi(API.User.SIGNUP, "POST", data, {}, data.type, false)
                .then((res: ApiResponse) => {
                    if (!res.data.status) {
                        throw new Error(res.data.message);
                    } else if (!res.data.results || res.data.results.length === 0) {
                        throw new Error("No user found.");
                    }
                    const authUser = Object.assign({}, res.data.results[0]);
                    delete authUser.userimages;
                    resolve(authUser);
                })
                .catch((err) => {
                    console.error("[register] ", err);
                    reject(err);
                });
        });
        return promise;
    }

    teacherRegister(data: any) {
        const promise = new Promise((resolve, reject) => {
            this.callApi(API.User.SIGNUP, "POST", data, {}, data.type, false).then(
                (res) => {
                    resolve(res);
                },
                (msg) => {
                    reject(msg);
                },
            );
        });
        return promise;
    }

    isEmailRegistered(data: any): Promise<boolean> {
        return this.callApi(API.User.CHECK_EMAIL, "POST", data, {}, "site", false)
            .then((res) => res.data.status)
            .catch((error) => {
                console.error("Error checking email registration: ", error);
                throw new Error("Error checking email registration.");
            });
    }

    sendContactUs(data) {
        return this.callApi(API.User.POST_CONTACTUS, "POST", data, {}, "site", false);
    }

    getCaptchaResponse(data: any) {
        return this.callApi(API.User.CAPTCHA, "POST", data, {}, "site", false);
    }

    setUser(user: any) {
        this.userSubject.next(user);
    }
}
