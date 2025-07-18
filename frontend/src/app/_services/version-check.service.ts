// version-check.service.ts
import { Injectable } from "@angular/core";
import { App } from "@capacitor/app";
import { compareVersions } from "compare-versions";
import { Settings as API } from "app/_constants/api.constants";
import { BaseService } from "./base.service";

interface VersionResponse {
    minSupportedAppVersion: string;
    latestAppVersion?: string;
}

@Injectable({ providedIn: "root" })
export class VersionCheckService {
    constructor(private baseService: BaseService) {}

    async checkCompatibility(): Promise<"ok" | "force-update" | "suggest-update"> {
        const appInfo = await App.getInfo(); // { version: "1.3.0", ... }
        const appVersion = appInfo.version;

        const response = await this.baseService.callApi(
            API.GET_VERSION_INFO,
            "GET",
            {},
            {},
            "site",
            false,
        );

        const versionData = response?.data?.results;
        console.log("Version data:", versionData);

        if (!versionData || !versionData.min_supported_app_version) {
            console.error("Version check failed: No version data or min supported version");
            return "ok";
        }

        // Convert the response to the expected format.
        const versionInfo: VersionResponse = {
            minSupportedAppVersion: response.data.results.min_supported_app_version,
            latestAppVersion: response.data.results.latest_app_version,
        };

        // Check if the app version is less than the minimum supported version.
        // If the app version is less than the minimum supported version, force update.
        if (compareVersions(appVersion, versionInfo.minSupportedAppVersion) < 0) {
            return "force-update";
        }

        // Check if the app version is less than the latest version.
        // If the app version is less than the latest version, suggest update.
        if (
            versionInfo.latestAppVersion &&
            compareVersions(appVersion, versionInfo.latestAppVersion) < 0
        ) {
            return "suggest-update";
        }

        if (
            versionInfo.latestAppVersion &&
            compareVersions(appVersion, versionInfo.latestAppVersion) > 0
        ) {
            console.warn("App version is greater than the latest version");
        }

        return "ok";
    }
}
