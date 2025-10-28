import { Injectable } from "@angular/core";
import { GoogleAuth } from "@codetrix-studio/capacitor-google-auth";
import { FacebookLogin, FacebookLoginResponse } from "@capacitor-community/facebook-login";

import { environment } from "environments/environment";

interface LimitedLoginClaims {
    sub: number;
    name: string;
    email: string;
}

@Injectable({
    providedIn: "root",
})
export class SocialMobileService {
    constructor() {}

    initGoogle() {
        GoogleAuth.initialize({
            clientId: `${environment.GOOGLE_CLIENT_ID_IOS}`,
            scopes: ["profile", "email"],
            grantOfflineAccess: true,
        });
    }

    signInWithGoogle() {
        return GoogleAuth.signIn().then((googleUser: any) => {
            if (!googleUser) {
                throw googleUser;
            }
            return {
                type: "google",
                social_id: googleUser.id,
                name: googleUser.displayName || googleUser.givenName + googleUser.familyName,
                email: googleUser.email,
                profile_image: googleUser.imageUrl,
            };
        });
    }

    initFacebook() {
        void FacebookLogin.initialize({ appId: environment.FACEBOOK_APP_ID });
    }

    async signInWithFacebook(): Promise<any> {
        // Check if user has a current access token
        // let result: FacebookLoginResponse = await FacebookLogin.getCurrentAccessToken();
        // if (!result?.accessToken?.token) {
        console.log("No current access token, logging in...");
        let result: FacebookLoginResponse = await FacebookLogin.login({
            permissions: ["public_profile", "email"],
        });

        const jwt = result?.accessToken?.token;
        if (!jwt) {
            throw new Error("Facebook login failed. No JWT found in login response.");
        }

        const claims = this.decodeLimitedLoginJwt(jwt);
        console.debug("Decoded claims", claims);

        // Facebook login successful
        // const userProfileJson = await fetch(
        // `https://graph.facebook.com/me?fields=id,name,email&access_token=${result?.accessToken?.token}`,
        // );
        // const userProfile = await userProfileJson.json();
        // if (!userProfile) {
        // throw new Error("Failed to get Facebook user profile");
        // }

        return this.extractFacebookUserData(claims);
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

    /**
     * Extracts necessary data from Facebook JWT to send to our
     * login endpoint.
     */
    extractFacebookUserData(claims: LimitedLoginClaims): {
        type: string;
        social_id: number;
        name: string;
        email: string;
    } {
        return {
            type: "fb",
            social_id: claims.sub,
            name: claims.name,
            email: claims.email,
        };
    }
}
