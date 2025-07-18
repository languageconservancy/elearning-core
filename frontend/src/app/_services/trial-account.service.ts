import { Injectable } from "@angular/core";
import { RegistrationService } from "app/_services/registration.service";
import { LoginService } from "app/_services/login.service";
import { OwoksapeUtils } from "app/shared/utils/owoksape-utils";
import { RegistrationData, SiteLoginData } from "app/shared/utils/elearning-types";

@Injectable({
    providedIn: "root",
})
export class TrialAccountService {
    constructor(
        private registrationService: RegistrationService,
        private loginService: LoginService,
    ) {}

    generateTrialAccountRegistrationData(email: string): RegistrationData {
        return {
            name: "trial",
            dob: "01-01-" + new Date().getFullYear(),
            email: email,
            password: email,
            repassword: email,
        };
    }

    generateTrialAccountLoginData(email: string): SiteLoginData {
        return {
            email: email,
            password: email,
            type: "site",
        };
    }

    async generateTrialAccountEmail(): Promise<string> {
        return `trial-${await OwoksapeUtils.hashUserAgent()}`;
    }

    accountIsTrial(name: string, email: string): boolean {
        return name == "trial" && email.startsWith("trial-");
    }

    /**
     * Handles the trial account creation process or login if account already exists.
     * @returns User object if registration or login is successful.
     * @throws Error if registration or login fails.
     */
    async handleTrialAccount(): Promise<any> {
        const trialAccountEmail = await this.generateTrialAccountEmail();

        try {
            const isAlreadyRegistered: boolean = await this.registrationService.isEmailRegistered({
                email: trialAccountEmail,
            });
            if (isAlreadyRegistered) {
                const authData = this.generateTrialAccountLoginData(trialAccountEmail);
                const authUser: any = await this.loginService.login(authData);
                return { authUser, authData };
            } else {
                const authData = this.generateTrialAccountRegistrationData(trialAccountEmail);
                const authUser = await this.registerTrialAccount(authData);
                return { authUser, authData };
            }
        } catch (error) {
            throw new Error(error.message || "Failed to login with trial account.");
        }
    }

    /**
     * Attempts to register a trial account and returns the created User object.
     * @param authData Registration data for trial account.
     * @returns User object that was created during registration.
     * @throws Error if registration fails.
     */
    private async registerTrialAccount(authData: RegistrationData): Promise<any> {
        const authUser: any = await this.registrationService.register(authData);
        return authUser;
    }

    trialPromptIsRequired(registrationDate: string): boolean {
        const now = new Date();
        const trialStartDate = new Date(registrationDate);
        const diff = now.getTime() - trialStartDate.getTime();
        const days = diff / (1000 * 60 * 60 * 24);
        return days % 7 == 0;
    }
}
