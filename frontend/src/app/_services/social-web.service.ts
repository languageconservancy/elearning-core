import { Injectable } from "@angular/core";
// For Facebook/Google login on web
import {
    SocialAuthService,
    FacebookLoginProvider,
    GoogleLoginProvider,
} from "@abacritt/angularx-social-login";
import { SignInWithApple } from "@capacitor-community/apple-sign-in";
import { jwtDecode } from "jwt-decode";

import { SnackbarService } from "app/_services/snackbar.service";
import { environment } from "environments/environment";
import { SocialLoginError, SocialLoginErrorType } from "app/_exceptions/social-login.errors";

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

    handleFacebookLoginCallback(user: any, fbBtnClicked: boolean) {
        if (!user) {
            console.error(
                "setUpFacebookAuthSubscriber bad user - Facebook login returned null/undefined user. Check Facebook login settings and permissions.",
            );
            return;
        }

        const provider = user?.provider.toLowerCase();
        if (provider !== "facebook") {
            console.warn("Got unhandled sign-in provider: ", user.provider);
            return;
        }
        if (provider === "facebook" && !fbBtnClicked) {
            // facebook login invalid repeat
            console.info(
                "Facebook login detected but fbBtnClicked is false - ignoring (this prevents duplicate logins)",
            );
            return;
        }

        const fbUser = this.extractFacebookUserData(user);

        return fbUser;
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
    extractFacebookUserData(user: any): {
        type: string;
        social_id: number;
        name: string;
        email: string;
    } {
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

    async signInWithGoogle(): Promise<any> {
        try {
            const user = await this.socialAuthService.signIn(GoogleLoginProvider.PROVIDER_ID);
            return this.extractGoogleUserData(user);
        } catch (error) {
            console.error("Error signing in with Google: ", error);
            throw error;
        }
    }

    /**
     * Extracts necessary data from Google user object returned by @abacritt/angularx-social-login
     * to send to our login endpoint.
     *
     * @param user - SocialUser object from @abacritt/angularx-social-login
     */
    extractGoogleUserData(user: any): {
        type: string;
        social_id: string;
        name: string;
        email: string;
        profile_image: string;
    } {
        return {
            type: "google",
            social_id: user.id,
            name: user.name,
            email: user.email,
            profile_image: user.photoUrl,
        };
    }

    /**
     * Legacy method for extracting Google user from credential response (One-Tap JWT)
     * Used when Google One-Tap is enabled and returns JWT directly
     */
    extractGoogleUserFromCredentialResponse(response: any /*CredentialResponse*/): {
        type: string;
        social_id: string;
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

    /**
     * Handles Google login callback from authState observable
     */
    handleGoogleLoginCallback(user: any): any {
        if (!user) {
            console.error("Google login returned null/undefined user");
            return null;
        }

        return this.extractGoogleUserData(user);
    }

    /*------------------------------------------------------------------------*/
    /*                             Apple Login                                */
    /*------------------------------------------------------------------------*/
    appleConfigValid(): boolean {
        return environment.APP_ID.trim() !== "";
    }

    async signInWithApple(): Promise<any> {
        // Attempt authorization
        let result = null;
        try {
            result = await SignInWithApple.authorize({
                clientId: environment.APP_ID + ".web",
                redirectURI: environment.LOGIN_URI,
                scopes: "email name",
                state: "12345",
                nonce: "nonce",
            });
        } catch (error) {
            console.error("Error authorizing with Apple: ", error);
            // Check if user cancelled
            if (error?.error === "popup_closed_by_user" || error?.code === "1001") {
                throw new SocialLoginError(
                    SocialLoginErrorType.AUTH_CANCELLED,
                    "Apple",
                    "User cancelled Apple sign in",
                    error,
                );
            }
            // Network or other error
            throw new SocialLoginError(
                SocialLoginErrorType.NETWORK_ERROR,
                "Apple",
                "Failed to connect to Apple",
                error,
            );
        }

        // Validate response
        if (!result?.response) {
            console.error("Error signing in with Apple: No response");
            throw new SocialLoginError(
                SocialLoginErrorType.NO_TOKEN,
                "Apple",
                "No response from Apple sign in",
            );
        }

        if (!result.response.identityToken) {
            console.error("Identity token missing from Apple sign in response");
            throw new SocialLoginError(
                SocialLoginErrorType.NO_TOKEN,
                "Apple",
                "Identity token missing from Apple sign in response",
            );
        }

        // Decode and validate JWT
        const appleUser = result.response;
        let decoded = null;
        try {
            decoded = jwtDecode(appleUser.identityToken);
        } catch (err) {
            console.error("Bad Apple JWT:", err);
            throw new SocialLoginError(
                SocialLoginErrorType.DECODE_ERROR,
                "Apple",
                "Failed to decode Apple JWT",
                err,
            );
        }

        if (!decoded) {
            console.error("Failed to decode Apple JWT");
            throw new SocialLoginError(
                SocialLoginErrorType.INVALID_TOKEN,
                "Apple",
                "Invalid Apple token",
            );
        }

        // Build login data
        const loginData = {
            type: "apple",
            social_id: decoded.sub,
            name: "user",
            email: decoded.email || "default@email.com",
        };

        // Add name if provided
        if (appleUser.givenName) {
            if (appleUser.familyName) {
                loginData.name = `${appleUser.givenName} ${appleUser.familyName}`;
            } else {
                loginData.name = appleUser.givenName;
            }
        } else if (appleUser.familyName) {
            loginData.name = appleUser.familyName;
        }

        return loginData;
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
