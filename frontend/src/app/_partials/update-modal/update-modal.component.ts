import { Component, Input } from "@angular/core";
import { Device } from "@capacitor/device";
import { environment } from "environments/environment";

@Component({
    selector: "app-update-modal",
    templateUrl: "./update-modal.component.html",
    styleUrls: ["./update-modal.component.css"],
})
/**
 * UpdateModalComponent is responsible for displaying a modal that prompts the user to update the
 * mobile app.
 * It provides methods to open the app in the App Store or Play Store based on the platform.
 */
export class UpdateModalComponent {
    // When true, the modal will not be closed when the user clicks the "Update" button,
    // since updating is mandatory, since the backend is not compatible with the current version.
    @Input() forceUpdate: boolean = false;
    private debug: boolean = !environment.production;

    /**
     * Opens the app in the App Store or Play Store based on the platform.
     * This method is called when the user clicks the "Update" button.
     */
    async goToAppStore() {
        const info = await Device.getInfo();

        if (info.platform === "android") {
            this.openAppInPlayStore(environment.APP_ID);
        } else if (info.platform === "ios") {
            this.openAppInAppStore(environment.IOS_APP_ID_NUMBER);
        } else {
            console.info("Unsupported platform: ", info.platform);
        }
    }

    /**
     * Opens the app in the Play Store.
     * @param {string} reverseDomainName - The reverse domain name of the app to open in the Play Store.
     */
    openAppInPlayStore(reverseDomainName: string) {
        if (!reverseDomainName) {
            console.error("reverseDomainName is not defined");
            return;
        }
        // Use the APP_ID from the environment to construct the Play Store URL
        const androidUrl = `market://details?id=${reverseDomainName}`;
        if (this.debug) console.debug("Opening Android Play Store URL: ", androidUrl);
        window.open(androidUrl, "_system");
    }

    /**
     * Opens the app in the App Store.
     * @param {string} iosAppId - The iOS app ID to open in the App Store.
     */
    openAppInAppStore(iosAppId: string) {
        if (!iosAppId) {
            console.error("iosAppId is not defined");
            return;
        }
        // Use the iTunes URL scheme to open the app in the App Store
        const iosUrl = `itms-apps://itunes.apple.com/app/id${iosAppId}`;
        if (this.debug) console.debug("Opening iOS App Store URL: ", iosUrl);
        window.open(iosUrl, "_system");
    }

    /**
     * Closes the modal.
     * This method is called when the user clicks the "Close" button.
     * If forceUpdate is true, the modal will not be closed.
     * Otherwise, it will remove the modal from the DOM and remove the "update-block" class from the body.
     */
    dismiss() {
        if (!this.forceUpdate) {
            document.body.classList.remove("update-block");
            const modal = document.getElementById("update-modal");
            modal?.remove();
        }
    }
}
