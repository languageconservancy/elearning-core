import { Injectable } from "@angular/core";
import { Router } from "@angular/router";

import * as API from "app/_constants/api.constants";
import { BaseService } from "./base.service";
import { SettingsService } from "./settings.service";
import { CookieService } from "./cookie.service";
import { LocalStorageService } from "./local-storage.service";
import { SocialAuthService } from "@abacritt/angularx-social-login";

@Injectable({
    providedIn: "root",
})
export class SiteSettingsService extends BaseService {
    private settings: any;
    private settingsLoaded: Promise<any>;

    private features: any;
    private featuresLoaded: Promise<any>;

    constructor(
        protected settingsService: SettingsService,
        public cookieService: CookieService,
        protected localStorage: LocalStorageService,
        protected router: Router,
        protected socialAuthService: SocialAuthService,
    ) {
        super(cookieService, localStorage, router, socialAuthService);
        this.settingsLoaded = this.fetchSettings();
        this.featuresLoaded = this.fetchFeatures();
    }

    /**
     * Fetches the site settings from the server that are prefixed 'setting_'
     *
     * @returns A promise that resolves when the settings have been fetched.
     */
    async fetchSettings(): Promise<void> {
        return this.callApi(API.Settings.GET_SETTINGS, "POST", {}, {}, "site", false)
            .then((res) => {
                if (!res.data.status) {
                    throw Error(res.data.message);
                }
                this.settings = res.data.results;
            })
            .catch((error) => {
                console.error("Error fetching settings", error);
            });
    }

    async fetchFeatures(): Promise<void> {
        return this.callApi(API.Settings.GET_FEATURES, "POST", {}, {}, "site", false)
            .then((res) => {
                if (!res.data.status) {
                    throw Error(res.data.message);
                }
                this.features = res.data.results;
            })
            .catch((error) => {
                console.error("Error fetching features", error);
            });
    }

    /**
     * Gets the settings.
     *
     * @returns A promise that resolves with the settings.
     */
    async getSettings(): Promise<any> {
        return this.settingsLoaded.then(() => this.settings);
    }

    /**
     * Gets the features.
     *
     * @returns A promise that resolves with the features.
     */
    async getFeatures(feature: string = ""): Promise<any> {
        if (!feature) {
            return this.featuresLoaded.then(() => this.features);
        } else {
            return this.featuresLoaded.then(() => this.features[feature]);
        }
    }

    async getContentByKeyword(keyword: string): Promise<any> {
        return this.callApi(API.Settings.GET_CONTENT_BY_KEYWORD, "POST", { keyword }, {}, "site", false)
            .then((res) => {
                return res;
            })
            .catch((error) => {
                console.error("Error fetching content by keyword", error);
            });
    }
}
