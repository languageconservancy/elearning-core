import { Injectable } from "@angular/core";
// For Facebook/Google login on web
import { SocialAuthService, FacebookLoginProvider } from "@abacritt/angularx-social-login";

import { SnackbarService } from "app/_services/snackbar.service";
import { environment } from "environments/environment";

// Facebook SDK loaded in index.html
declare let FB: any;

@Injectable({
    providedIn: "root",
})
export class SocialWebService {
    constructor(
        private socialAuthService: SocialAuthService,
        private snackbarService: SnackbarService,
    ) {}

    /*------------------------------------------------------------------------*/
    /*                           Facebook Login                               */
    /*------------------------------------------------------------------------*/
    facebookConfigValid(): boolean {
        return environment.FACEBOOK_APP_ID.trim() != "";
    }

    initFacebook(): Promise<any> {
        // Initialize FacebookService
        const initParams = {
            appId: environment.FACEBOOK_APP_ID,
            xfbml: true,
            version: environment.FACEBOOK_APP_VERSION,
        };
        try {
            return Promise.resolve(FB.init(initParams));
        } catch (error) {
            console.error("Error initializing FacebookService: ", error);
            return Promise.reject(error);
        }
    }

    /**
     * Signs user into Facebook and initiates a callback
     * See LoginComponent::setUpFacebookAuthSubscriber()
     */
    signInWithFacebook(): Promise<any> {
        const fbLoginOptions = {
            scope: "public_profile,email",
            return_scopes: true,
            enable_profile_selector: true,
        };
        return this.socialAuthService.signIn(FacebookLoginProvider.PROVIDER_ID, fbLoginOptions);
    }

    api(path: string, method: string = "get", params: any = {}): Promise<any> {
        return new Promise((resolve, reject) => {
            try {
                FB.api(path, method, params, (response) => {
                    if (!response) {
                        reject(response);
                    } else if (response.error) {
                        reject(response.error);
                    } else {
                        resolve(response);
                    }
                });
            } catch (error) {
                reject(error);
            }
        });
    }

    ui(params: any): Promise<any> {
        return new Promise((resolve, reject) => {
            try {
                FB.ui(params, (response) => {
                    if (!response) {
                        reject(response);
                    } else if (response.error) {
                        reject(response.error);
                    } else {
                        resolve(response);
                    }
                });
            } catch (error) {
                reject(error);
            }
        });
    }

    /**
     * Extracts necessary data from Facebook user object to send to our
     * login endpoint.
     */
    extractFacebookUserData(user: any): { type: string; social_id: number; name: string; email: string } {
        return {
            type: "fb",
            social_id: user.id,
            name: user.name,
            email: user.email,
        };
    }

    /*------------------------------------------------------------------------*/
    /*                             Google Login                               */
    /*------------------------------------------------------------------------*/
    googleConfigValid(): boolean {
        return environment.GOOGLE_CLIENT_ID_WEB.trim() != "";
    }

    /**
     * Extracts necessary data from Google user object to send to our
     * login endpoint.
     */
    extractGoogleUserFromCredentialResponse(response: any /*CredentialResponse*/): {
        type: string;
        social_id: number;
        name: string;
        email: string;
        profile_image: string;
    } {
        // Decode JWT Response
        const user = JSON.parse(atob(response.credential.split(".")[1]));
        // Sign user in with info retreived from Google
        return {
            type: "google",
            social_id: user.sub,
            name: user.name,
            email: user.email,
            profile_image: user.picture,
        };
    }

    /*------------------------------------------------------------------------*/
    /*                             Apple Login                                */
    /*------------------------------------------------------------------------*/
    appleConfigValid(): boolean {
        return environment.GOOGLE_CLIENT_ID_IOS.trim() !== "";
    }

    /*------------------------------------------------------------------------*/
    /*                             Clever Login                               */
    /*------------------------------------------------------------------------*/
    /**
     * Returns whether or not Clever config values are valid.
     * @returns {boolean} - true if valid config, false otherwise.
     */
    cleverConfigValid(): boolean {
        return environment.LOGIN_URI.trim() != "" && environment.CLEVER_ID.trim() != "";
    }

    /**
     * Attempts to log the user in with Clever portal login
     * @param {object} queryParams - Contains params object
     * @param {object} queryParams.params - Contains URL query params
     * @param {string} queryParams.params.code - Clever code
     * @param {string} queryParams.params.scope - Clever access scope
     */
    extractCleverUserFromQueryParams(
        queryParams,
    ): { type: string; code: number | string; scope: string; redirect_uri: string } | null {
        if (!queryParams.code || !queryParams.scope) {
            return null;
        }

        // Redirected here from Clever
        const urlWithoutParams = window.location.href.split("?")[0];

        return {
            type: "clever",
            code: queryParams.code,
            scope: queryParams.scope,
            redirect_uri: urlWithoutParams,
        };
    }

    /**
     * Handles when a user clicks the "Sign in with Clever" button,
     * redirecting the user to the Clever redirect URI, so the user can
     * enter their Clever credentials, if needed, and then get redirected to
     * the elearning platform.
     */
    handleCleverLoginBtnClick() {
        if (this.cleverConfigValid()) {
            const cleverBtnLink =
                "https://clever.com/oauth/authorize?response_type=code&redirect_uri=" +
                environment.LOGIN_URI +
                "/&client_id=" +
                environment.CLEVER_ID;
            window.location.href = cleverBtnLink;
        } else {
            this.displayCleverDisabledMessage();
        }
    }

    /**
     * Displays message indicating that Clever login isn't currently enabled.
     */
    displayCleverDisabledMessage() {
        const errorMsg = "Clever isn't configured, so Clever login is disabled.";
        console.error(errorMsg);
        this.snackbarService.showSnackbar({
            status: false,
            msg: errorMsg,
        });
    }
}
