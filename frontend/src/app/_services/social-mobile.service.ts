import { Injectable } from "@angular/core";
import { GoogleAuth } from "@codetrix-studio/capacitor-google-auth";
import { FacebookLogin } from "@capacitor-community/facebook-login";

import { environment } from "environments/environment";

@Injectable({
    providedIn: "root",
})
export class SocialMobileService {
    constructor() {}

    initGoogle() {
        GoogleAuth.initialize({
            clientId: `${environment.GOOGLE_CLIENT_ID_WEB}`,
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
        const result = await FacebookLogin.login({ permissions: ["public_profile", "email"] });

        if (!result || !result.accessToken) {
            console.error("Facebook login failed.");
            throw new Error("Facebook login failed.");
        }
        // Facebook login successful
        const fbUser = await this.getFacebookUserProfile();
        if (!fbUser) {
            throw new Error("Failed to get Facebook user profile");
        }

        return this.extractFacebookUserData(fbUser);
    }

    async getFacebookUserProfile() {
        const result = await FacebookLogin.getProfile<{
            id: number;
            name: string;
            email: string;
        }>({ fields: ["id", "name", "email"] });
        return result;
    }

    /**
     */
    extractFacebookUserData(user: any): any {
        return {
            type: "fb",
            social_id: user.id,
            name: user.name,
            email: user.email,
        };
    }
}
