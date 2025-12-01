import { Injectable } from "@angular/core";
import { Capacitor } from "@capacitor/core";
import {
    GoogleLoginResponse,
    GoogleLoginResponseOnline,
    SocialLogin,
    InitializeOptions,
} from "@capgo/capacitor-social-login";
import { BaseService } from "app/_services/base.service";

import { environment } from "environments/environment";
import { SocialLoginError, SocialLoginErrorType } from "app/_exceptions/social-login.errors";

interface LimitedLoginClaims {
    sub: number;
    name: string;
    email: string;
}

/**
 * Type guard to check if the Google login response is online (has profile data)
 */
function isGoogleLoginResponseOnline(
    response: GoogleLoginResponse,
): response is GoogleLoginResponseOnline {
    return response.responseType === "online";
}

@Injectable({
    providedIn: "root",
})
export class SocialMobileService {
    constructor(private baseService: BaseService) {}

    /*------------------------------------------------------------------------*/
    /*                           Google Login                               */
    /*------------------------------------------------------------------------*/
    async initGoogle() {
        try {
            await SocialLogin.initialize({
                google: {
                    webClientId: `${environment.GOOGLE_CLIENT_ID_WEB}`,
                    iOSClientId: `${environment.GOOGLE_CLIENT_ID_IOS}`,
                    mode: "online", // Changed to online mode to access profile data
                },
            });
        } catch (error) {
            console.error("Error initializing Google login: ", error);
            throw error;
        }
    }

    /**
     * Sign in with Google
     * @returns { type: string; social_id: string; name: string; email: string; }
     */
    async signInWithGoogle(): Promise<{
        type: string;
        social_id: string;
        name: string;
        email: string;
        profile_image: string;
    }> {
        try {
            const res = await SocialLogin.login({
                provider: "google",
                options: {
                    scopes: ["profile", "email"],
                    forceRefreshToken: true,
                    // forcePrompt: true,
                },
            });

            if (!res.result) {
                console.error("Error signing in with Google: ", res);
                throw res;
            }

            // Type guard to ensure we have an online response with profile data
            if (!isGoogleLoginResponseOnline(res.result)) {
                throw new Error(
                    "Google login returned offline response. Profile data is not available in offline mode.",
                );
            }

            const profile = res.result.profile;

            this.baseService.setLoginType("google");

            return {
                type: "google",
                social_id: profile?.id || null,
                name:
                    profile?.name ||
                    (profile?.givenName && profile?.familyName
                        ? `${profile.givenName} ${profile.familyName}`
                        : profile?.givenName || profile?.familyName || ""),
                email: profile?.email || null,
                profile_image: profile?.imageUrl || "",
            };
        } catch (error) {
            this.baseService.setLoginType("");
            throw error;
        }
    }

    /*------------------------------------------------------------------------*/
    /*                           Facebook Login                               */
    /*------------------------------------------------------------------------*/

    /**
     * Initialize Facebook login
     * @returns {void}
     */
    async initFacebook(): Promise<void> {
        try {
            await SocialLogin.initialize({
                facebook: {
                    appId: environment.FACEBOOK_APP_ID,
                    clientToken: environment.FACEBOOK_CLIENT_TOKEN,
                },
            });
        } catch (error) {
            console.error("Error initializing Facebook login: ", error);
            throw error;
        }
    }

    /**
     * Sign in with Facebook
     * @returns { type: string; social_id: string; name: string; email: string; }
     */
    async signInWithFacebook(): Promise<{
        type: string;
        social_id: string;
        name: string;
        email: string;
    }> {
        try {
            const res = await SocialLogin.login({
                provider: "facebook",
                options: {
                    permissions: ["public_profile", "email"],
                    limitedLogin: false,
                    nonce: "1234567890",
                },
            });

            if (!res.result) {
                console.error("Error signing in with Facebook: ", res);
                throw res;
            }

            const profile = res.result.profile;

            this.baseService.setLoginType("fb");

            return {
                type: "fb",
                social_id: profile?.userID || null,
                name: profile?.name || null,
                email: profile?.email || null,
            };
        } catch (error) {
            console.error("Error logging in with Facebook: ", error);
            this.baseService.setLoginType("");
            throw error;
        }
    }

    decodeLimitedLoginJwt(jwt: string): LimitedLoginClaims {
        const [, payload] = jwt.split(".");
        const base64 = payload.replace(/-/g, "+").replace(/_/g, "/");
        const json = decodeURIComponent(
            atob(base64)
                .split("")
                .map((c) => "%" + ("00" + c.charCodeAt(0).toString(16)).slice(-2))
                .join(""),
        );
        return JSON.parse(json) as LimitedLoginClaims;
    }

    /*------------------------------------------------------------------------*/
    /*                           Apple Login                               */
    /*------------------------------------------------------------------------*/

    /**
     * Initialize Apple login
     * @returns {void}
     */
    async initApple() {
        let appleInitOptions: InitializeOptions = { apple: {} };
        // If Android, set the clientId and redirectUrl to the app's ID and login URI
        if (Capacitor.getPlatform() === "android") {
            appleInitOptions.apple.clientId = environment.APP_ID;
            appleInitOptions.apple.redirectUrl = environment.LOGIN_URI;
        }
        try {
            await SocialLogin.initialize(appleInitOptions);
        } catch (error) {
            console.error("Error initializing Apple login: ", error);
            throw error;
        }
    }

    /**
     * Sign in with Apple
     * @returns { type: string; social_id: string; name: string; email: string; }
     */
    async signInWithApple(): Promise<{
        type: string;
        social_id: string;
        name: string;
        email: string;
    }> {
        try {
            const res = await SocialLogin.login({
                provider: "apple",
                options: {
                    scopes: ["email", "name"],
                    nonce: "nonce",
                },
            });

            if (!res.result) {
                console.error("Error signing in with Apple: No result");
                throw new SocialLoginError(
                    SocialLoginErrorType.NO_TOKEN,
                    "Apple",
                    "No result from Apple sign in",
                );
            }

            if (!res.result.idToken) {
                console.error("Identity token missing from Apple sign in response");
                throw new SocialLoginError(
                    SocialLoginErrorType.NO_TOKEN,
                    "Apple",
                    "Identity token missing from Apple sign in response",
                );
            }

            // Decode the idToken to get the user's Apple ID
            const idToken = res.result.idToken;
            let claims: LimitedLoginClaims;
            try {
                claims = this.decodeLimitedLoginJwt(idToken);
            } catch (err) {
                throw new SocialLoginError(
                    SocialLoginErrorType.DECODE_ERROR,
                    "Apple",
                    "Failed to decode Apple JWT",
                    err,
                );
            }

            const profile = res.result.profile;

            this.baseService.setLoginType("apple");

            // Return the user's data
            return {
                type: "apple",
                social_id: claims.sub.toString() || null,
                name:
                    profile?.givenName + " " + profile?.familyName ||
                    profile?.givenName ||
                    profile?.familyName ||
                    "user",
                email: claims.email || null,
            };
        } catch (error) {
            console.error("Error logging in with Apple: ", error);

            this.baseService.setLoginType("");

            // If it's already a SocialLoginError, re-throw it
            if (error instanceof SocialLoginError) {
                throw error;
            }

            // Check if user canceled
            if (error?.code === "1001" || error?.message?.includes("cancel")) {
                throw new SocialLoginError(
                    SocialLoginErrorType.AUTH_CANCELLED,
                    "Apple",
                    "User canceled Apple sign in",
                    error,
                );
            }

            // Unknown error
            throw new SocialLoginError(
                SocialLoginErrorType.UNKNOWN_ERROR,
                "Apple",
                "Unknown error during Apple sign in",
                error,
            );
        }
    }
}
