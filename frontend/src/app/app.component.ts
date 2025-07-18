import { Component, ApplicationRef, createComponent } from "@angular/core";
import { SettingsService } from "./_services/settings.service";
import { DeviceDetectorService } from "ngx-device-detector";
import { environment } from "../environments/environment";
import { App } from "@capacitor/app";
import { Location } from "@angular/common";
import { VersionCheckService } from "./_services/version-check.service";
import { UpdateModalComponent } from "./_partials/update-modal/update-modal.component";

@Component({
    selector: "app-root",
    templateUrl: "./app.component.html",
    styleUrls: ["./app.component.scss"],
})
export class AppComponent {
    public environment = environment;
    public debug = !environment.production;
    public maintenanceFlag: boolean = false;
    public deviceInfo: any;
    public isMobileOrTablet: boolean = false;
    public isDesktop: boolean = false;

    constructor(
        private settingsService: SettingsService,
        private deviceDetector: DeviceDetectorService,
        private location: Location,
        private versionCheckService: VersionCheckService,
        private appRef: ApplicationRef,
    ) {
        this.getDeviceInfo();

        this.settingsService.maintenanceMode.subscribe((res) => {
            this.maintenanceFlag = res;
        });

        // Handle back button on mobile devices
        void App.addListener("backButton", () => {
            this.location.back();
        });

        // Check if the mobile app needs to be updated
        // This is only relevant for mobile devices, so we check the device type first
        if (this.isMobileOrTablet) {
            void this.checkAppVersion();
        }
    }

    /**
     * Get device information using the DeviceDetectorService.
     * This method sets the deviceInfo, isMobileOrTablet, and isDesktop properties.
     * It uses the DeviceDetectorService to determine the type of device.
     */
    private getDeviceInfo() {
        this.deviceInfo = this.deviceDetector.getDeviceInfo();
        this.isMobileOrTablet = this.deviceDetector.isMobile() || this.deviceDetector.isTablet();
        this.isDesktop = this.deviceDetector.isDesktop();
    }

    /**
     * Check the app version for mobile devices.
     * This method uses the VersionCheckService to check if the app version is compatible.
     * If the app version is less than the minimum supported version, it will prompt for a force update.
     * If the app version is less than the latest version, it will suggest an update.
     */
    private async checkAppVersion() {
        // Perform version check for mobile devices
        console.log("Checking app version for mobile");
        try {
            const result = await this.versionCheckService.checkCompatibility();
            console.log("Version check result:", result);

            if (result === "force-update" || result === "suggest-update") {
                // Create and display the update modal
                const componentRef = createComponent(UpdateModalComponent, {
                    environmentInjector: this.appRef.injector,
                });

                componentRef.instance.forceUpdate = result === "force-update";
                this.appRef.attachView(componentRef.hostView);

                const domElem = (componentRef.hostView as any).rootNodes[0] as HTMLElement;
                document.body.appendChild(domElem);

                if (result === "force-update") {
                    document.body.classList.add("update-block");
                }
            } else {
                console.log("App version is compatible");
            }
        } catch (error) {
            console.error("Error checking app version:", error);
        }
    }
}
