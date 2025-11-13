import { Injectable } from "@angular/core";
import { Capacitor } from "@capacitor/core";
import {
    GoogleLoginResponse,
    GoogleLoginResponseOnline,
    SocialLogin,
    InitializeOptions,
} from "@capgo/capacitor-social-login";

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
    constructor() {}

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
            console.debug("Google login initialized");
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
    async initFacebook() {
        try {
            await SocialLogin.initialize({
                facebook: {
                    appId: environment.FACEBOOK_APP_ID,
                    clientToken: environment.FACEBOOK_CLIENT_TOKEN,
                },
            });
            console.debug("Facebook login initialized");
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
                    limitedLogin: true,
                    nonce: "1234567890",
                },
            });

            if (!res.result) {
                console.error("Error signing in with Facebook: ", res);
                throw res;
            }

            const profile = res.result.profile;

            return {
                type: "fb",
                social_id: profile?.userID || null,
                name: profile?.name || null,
                email: profile?.email || null,
            };
        } catch (error) {
            console.error("Error logging in with Facebook: ", error);
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

            console.debug("Apple login response: ", res);

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
            console.debug("Decoded claims", claims);
            const profile = res.result.profile;

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

            // If it's already a SocialLoginError, re-throw it
            if (error instanceof SocialLoginError) {
                throw error;
            }

            // Check if user cancelled
            if (error?.code === "1001" || error?.message?.includes("cancel")) {
                throw new SocialLoginError(
                    SocialLoginErrorType.AUTH_CANCELLED,
                    "Apple",
                    "User cancelled Apple sign in",
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
