import { Injectable } from "@angular/core";
import { CookieService as NgCookieService } from "ngx-cookie-service";
import { CapacitorCookies } from "@capacitor/core";
import { Preferences as CapacitorPreferences } from "@capacitor/preferences";
import { DeviceDetectorService } from "ngx-device-detector";

import { environment } from "environments/environment";

@Injectable({
    providedIn: "root",
})
export class CookieService {
    public isMobileOrTablet: boolean = false;
    private DEFAULT_EXPIRY_DAYS: number = 45;

    constructor(
        private deviceDetector: DeviceDetectorService,
        private ngCookieService: NgCookieService,
    ) {
        this.isMobileOrTablet = this.deviceDetector.isMobile() || this.deviceDetector.isTablet();
    }

    //-------------------
    // Generic functions
    //-------------------

    set(key: string, value: string, expiry: number | Date = undefined): Promise<any> {
        if (this.isMobileOrTablet) {
            return this.setMobileCookieAndPersistentStorage(key, value, expiry);
        } else {
            return this.setWebCookie(key, value, expiry);
        }
    }

    get(key: string): Promise<string> {
        if (this.isMobileOrTablet) {
            return this.getMobileCookie(key);
        } else {
            return new Promise((resolve) => {
                resolve(this.getWebCookie(key));
            });
        }
    }

    async delete(key: string): Promise<any> {
        if (this.isMobileOrTablet) {
            await this.deleteMobileCookie(key);
            return await this.removeMobilePersistentValue(key);
        } else {
            return this.deleteWebCookie(key);
        }
    }

    async deleteAll(): Promise<any> {
        if (this.isMobileOrTablet) {
            await this.deleteAllMobileCookies();
            return await this.clearMobilePersistentStorage();
        } else {
            return this.deleteAllWebCookies();
        }
    }

    //---------------------------
    // Mobile-specific functions
    //---------------------------

    setMobileCookie(key: string, value: string, expiry: number | Date = undefined): Promise<any> {
        const expire = !!expiry ? expiry.toString() : undefined;
        // Set cookie so mobile browser works
        return CapacitorCookies.setCookie({
            key: key,
            value: value,
            expires: expire,
            path: "/",
        });
    }

    async setMobileCookieAndPersistentStorage(
        key: string,
        value: string,
        expiry: number | Date = undefined,
    ): Promise<any> {
        if (!expiry) {
            expiry = new Date();
            expiry.setDate(expiry.getDate() + this.DEFAULT_EXPIRY_DAYS);
        }
        // Set cookie so mobile browser works
        return CapacitorCookies.setCookie({
            key: key,
            value: value,
            expires: expiry.toString(),
            path: "/",
        }).then(() => {
            // Set persistent storage so closing and reopening app keeps user logged in
            return this.setMobilePersistentValue(key, value);
        });
    }

    setMobilePersistentValue(key: string, value: string): Promise<any> {
        return CapacitorPreferences.set({ key: key, value: value });
    }

    /**
     * Get cookie on mobile device.
     * If cookie is empty, check for persistent storage value.
     * If persistent value exists, set cookie.
     * If cookie set fails, caller will hit catch.
     * If cookie set succeeds, return persistent value;
     * @param {string} key - Name used when cookie was saved
     * @return Promise resolving to cookie value
     */
    async getMobileCookie(key: string): Promise<string> {
        // Get cookie if it exists
        const value = this.getWebCookie(key);

        if (!!value) {
            return Promise.resolve(value);
        }

        // No cookie. Check persistent storage value
        return CapacitorPreferences.get({ key: key })
            .then(async (getResult) => {
                if (!getResult.value) {
                    console.warn("No persistent storage value found for key: " + key);
                    return null;
                }
                // Got value from persistent storage. Set cookie and return.
                return this.setMobileCookie(key, getResult.value).then(() => {
                    return getResult.value;
                });
            })
            .catch((error) => {
                console.error("Error getting mobile cookie for key ", key, error);
                throw error;
            });
    }

    deleteMobileCookie(key: string): Promise<any> {
        return CapacitorCookies.deleteCookie({ key: key });
    }

    removeMobilePersistentValue(key: string): Promise<any> {
        return CapacitorPreferences.remove({ key: key });
    }

    deleteAllMobileCookies(): Promise<any> {
        return CapacitorCookies.clearAllCookies();
    }

    clearMobilePersistentStorage(): Promise<any> {
        return CapacitorPreferences.clear();
    }

    //---------------------------
    // Web-specific functions
    //---------------------------

    setWebCookie(key: string, value: string, expiry: number | Date = undefined): Promise<any> {
        return new Promise((resolve) => {
            this.ngCookieService.set(
                key,
                value,
                expiry,
                "/",
                undefined,
                environment.production,
                "Strict",
            );
            resolve(true);
        });
    }

    getWebCookie(key: string): string {
        const value = this.ngCookieService.get(key);
        // To avoid returning 'null' string, check for null and return empty string
        return !!value ? value : "";
    }

    deleteWebCookie(key: string): Promise<any> {
        return new Promise((resolve) => {
            this.ngCookieService.delete(key, "/");
            resolve(true);
        });
    }

    deleteAllWebCookies(): Promise<any> {
        return new Promise((resolve) => {
            this.ngCookieService.deleteAll("/");
            resolve(true);
        });
    }

    getAuthUserExpiryDate(): Date {
        const expire = new Date();
        expire.setDate(expire.getDate() + this.DEFAULT_EXPIRY_DAYS);
        return expire;
    }
}
