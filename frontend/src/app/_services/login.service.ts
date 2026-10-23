import { inject, Injectable } from "@angular/core";

import { BaseService } from "./base.service";
import { RegistrationService } from "./registration.service";
import * as API from "@app/_constants/api.constants";

@Injectable()
export class LoginService extends BaseService {
    public registrationService = inject(RegistrationService);

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
