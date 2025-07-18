import { Injectable } from "@angular/core";

import { BaseService } from "./base.service";
import { RegistrationService } from "./registration.service";
import * as API from "app/_constants/api.constants";
import { CookieService } from "./cookie.service";
import { LocalStorageService } from "./local-storage.service";
import { Router } from "@angular/router";
import { SocialAuthService } from "@abacritt/angularx-social-login";

@Injectable()
export class LoginService extends BaseService {
    constructor(
        public registrationService: RegistrationService,
        public cookieService: CookieService,
        protected localStorageService: LocalStorageService,
        protected router: Router,
        protected socialAuthService: SocialAuthService,
    ) {
        super(cookieService, localStorageService, router, socialAuthService);
    }

    getAuthToken(data: any) {
        return this.getAuthTokens(data);
    }

    /**
     * Logs in a user.
     * @param data - The user data to login.
     * @returns User object if login is successful.
     */
    async login(data: any): Promise<any> {
        try {
            const res: any = await this.callApi(API.User.LOGIN, "POST", data, {}, data.type, false);

            if (!res.data?.results) {
                console.error(`[${this.constructor.name}] Invalid login response format:`, res);
                throw new Error("Invalid login response format.");
            }
            if (!res.data.status || !res.data.results[0]) {
                console.error(
                    `[${this.constructor.name}] Invalid login response format:`,
                    res.data.message,
                );
                throw new Error(res.data.message || "Invalid login response format.");
            }

            const authUser = JSON.parse(JSON.stringify(res.data.results[0]));

            // Remove userimages from authUser, since it's not needed.
            delete authUser.userimages;

            return authUser;
        } catch (error) {
            console.error(
                `[${this.constructor.name}] Error logging in user: ${error.message}`,
                error,
            );
            throw new Error(`${error.message}`);
        }
    }
}
