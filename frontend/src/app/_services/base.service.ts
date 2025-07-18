import { Injectable } from "@angular/core";
import { BehaviorSubject } from "rxjs";
import { HttpStatusCode } from "@angular/common/http";
import { Router } from "@angular/router";
import { CapacitorHttp, HttpResponse } from "@capacitor/core";
import { SocialAuthService } from "@abacritt/angularx-social-login";
import { ForbidenResponseReasons } from "app/shared/utils/elearning-types";

import * as API from "app/_constants/api.constants";
import { CookieService } from "./cookie.service";
import { LocalStorageService } from "./local-storage.service";

@Injectable()
export class BaseService {
    public AuthType: string = "agent";
    public loginType: string = "";
    private logOutSubject = new BehaviorSubject<any>({});
    public loginStatus = this.logOutSubject.asObservable();

    constructor(
        public cookieService: CookieService,
        protected localStorage: LocalStorageService,
        protected router: Router,
        protected socialAuthService: SocialAuthService,
    ) {}

    public async callApi(
        functionUrl: string,
        requestType: string = "",
        requestParams = {},
        urlParams = {},
        authType: string,
        authRequired: boolean,
    ): Promise<any> {
        const params = {};
        if (typeof functionUrl === "undefined") {
            throw new TypeError("Sorry function url is not defined");
        }
        if (typeof authRequired === "undefined") {
            authRequired = true;
        }
        if (typeof urlParams === "undefined") {
            urlParams = {};
        }

        if (typeof requestParams === "undefined") {
            requestParams = {};
        }
        if (typeof requestType === "undefined") {
            requestType = "GET";
        }

        if (typeof authType === "undefined") {
            authType = "site";
        }

        params["type"] = authType;
        const headers = { "Content-Type": "application/json" };
        if (authRequired) {
            return this.getAuthTokens(params)
                .then(async (token) => {
                    if (!!token) {
                        headers["Authorization"] = "Bearer " + token;
                        return this.callApiwithAuth(
                            requestType,
                            functionUrl,
                            requestParams,
                            urlParams,
                            headers,
                        )
                            .then(async (res) => {
                                if (!res.data.data) {
                                    if (
                                        res.status == (HttpStatusCode.Unauthorized as number) ||
                                        this.tokenExpired(res)
                                    ) {
                                        await this.logOutOnError();
                                        throw new Error("Error getting auth token.");
                                    } else if (res.status == (HttpStatusCode.Forbidden as number)) {
                                        const reason = res.data.error.message;
                                        if (
                                            reason ==
                                            ForbidenResponseReasons.AGREEMENTS_NOT_ACCEPTED
                                        ) {
                                            await this.logout();
                                        }
                                        throw new Error("User has not accepted agreements.");
                                    }
                                }
                                return res.data;
                            })
                            .catch(async (err) => {
                                if (
                                    err.data?.data?.status ==
                                        (HttpStatusCode.Unauthorized as number) ||
                                    this.tokenExpired(err)
                                ) {
                                    await this.logOutOnError();
                                }
                                throw new Error(err);
                            });
                    } else {
                        throw new Error("Error getting auth token.");
                    }
                })
                .catch((err) => {
                    console.error(`[${this.constructor.name}] Error getting auth tokens. `, err);
                    throw new Error(err);
                });
        } else {
            return this.callApiwithAuth(requestType, functionUrl, requestParams, urlParams, headers)
                .then((res) => {
                    return res.data;
                })
                .catch(async (err) => {
                    if (!!err.data && !!err.data?.data) {
                        if (
                            err.data.data.status == (HttpStatusCode.Unauthorized as number) ||
                            this.tokenExpired(err)
                        ) {
                            console.error(`[${this.constructor.name}] token expired. `, err);
                            await this.logOutOnError();
                        }
                    }
                    throw new Error(err);
                });
        }
    }

    private tokenExpired(err: any): boolean {
        const error = err.data?.data?.error;
        if (!error) {
            return false;
        }
        return (
            error.code == (HttpStatusCode.InternalServerError as number) &&
            error.message == "Expired token"
        );
    }

    private callApiwithAuth(
        requestType: string,
        functionUrl: string,
        requestData: any,
        urlParams: any,
        headers: any,
    ) {
        switch (requestType) {
            case "GET":
                return CapacitorHttp.get({
                    url: functionUrl,
                    params: urlParams,
                    headers: headers,
                });
            case "POST":
                return CapacitorHttp.post({
                    url: functionUrl,
                    headers: headers,
                    data: requestData,
                    params: urlParams,
                });
            case "PUT":
                return CapacitorHttp.put({
                    url: functionUrl,
                    headers: headers,
                    data: requestData,
                    params: urlParams,
                });
            default:
                throw new Error("Got unhandled HTTP method: " + requestType);
        }
    }

    public async getAuthTokens(params: any): Promise<string | void> {
        if (!params.hasOwnProperty("type")) {
            throw new Error("Invalid login credentials. No type.");
        }
        return this.cookieService
            .get("AuthToken")
            .then((value) => {
                if (!value) {
                    return this.postToTokenEndpoint(params);
                } else {
                    return value;
                }
            })
            .catch((err) => {
                console.error(`getAuthTokens catch. Error: `, err);
            });
    }

    private async postToTokenEndpoint(params: any): Promise<string | void> {
        if (!params.hasOwnProperty("email") && params.type != "fb") {
            throw new Error("Invalid login credentials. No email and no fb.");
        }

        return CapacitorHttp.post({
            url: API.User.AUTH_TOKEN,
            headers: { "Content-type": "application/json" },
            data: params,
        })
            .then(async (auth: HttpResponse) => {
                if (!auth.data.data?.token) {
                    throw new Error("No token found in response.");
                }
                const expire = new Date();
                const daysUntilExpiry = 45;
                expire.setDate(expire.getDate() + daysUntilExpiry);
                try {
                    await this.cookieService.set("AuthToken", auth.data.data.token, expire);
                    return auth.data.data.token;
                } catch (err) {
                    console.error(`[${this.constructor.name}] Error setting AuthToken. `, err);
                    throw new Error("Error setting auth token.");
                }
            })
            .catch((err) => {
                console.info(`[${this.constructor.name}] Error getting Auth token. `, err);
                throw new Error("Error getting auth token.");
            });
    }

    public setLoginType(type: string) {
        this.loginType = type;
    }

    private async logOutOnError() {
        // Print caller stack
        console.info("[logOutOnError] ", new Error().stack);
        try {
            await this.cookieService.deleteAll();
        } catch (err) {
            console.error(`[${this.constructor.name}] Error deleting all cookies. `, err);
        }
        this.localStorage.clear();
        await this.signOutOfSocialAccount();
        void this.router.navigate(["/"], { queryParams: { expiredToken: true } });
    }

    public async logout() {
        try {
            await this.cookieService.deleteAll();
        } catch (err) {
            console.error(`[${this.constructor.name}] Error deleting all cookies. `, err);
        }
        this.localStorage.clear();
        this.logOutSubject.next({ loggedOut: true });
        await this.signOutOfSocialAccount();
    }

    private async signOutOfSocialAccount() {
        if (["fb", "google"].indexOf(this.loginType) >= 0) {
            try {
                await this.socialAuthService.signOut(true);
            } catch (err) {
                console.warn(
                    `[${this.constructor.name}] Error signing out of social account. `,
                    err,
                );
            }
        }
    }

    async setAuthUserCookie(user: any): Promise<void> {
        if (!user || !user.id) {
            throw new Error(
                "[setAutherUserCookie] User object is invalid. " + JSON.stringify(user),
            );
        }
        if (user.userimages) {
            delete user.userimages;
        }
        const expire = new Date();
        const daysUntilExpiry = 45;
        expire.setDate(expire.getDate() + daysUntilExpiry);
        try {
            await this.cookieService.set("AuthUser", JSON.stringify(user), expire);
        } catch (error) {
            console.error(`[${this.constructor.name}] Error setting AuthUser cookie. `, error);
            throw new Error(
                `[${this.constructor.name}] Error setting AuthUser cookie: ${error?.message || "Unknown error"}`,
            );
        }
    }
}
