/**
 * @Component: AgreementsAcceptanceComponent
 * @Description: This component displays the terms and conditions that users must accept.
 * It fetches the terms content from the `SiteSettingsService` and presents it to the user.
 * The user must scroll to the bottom of the terms and check the acceptance checkbox,
 * then do the same for the privacy policy, which will automatically appear after accepting the terms.
 * On acceptance or rejection, the component emits an event to notify the parent component.
 *
 * Features:
 * - Fetches and displays terms and conditions dynamically.
 * - Handles errors gracefully in case the terms cannot be retrieved.
 *
 * Usage:
 * - Add <app-agreements-acceptance></app-agreements-acceptance> to a template where this component is needed.
 *
 * Inputs: None
 * Outputs: None
 */

import { Component, OnInit, OnDestroy } from "@angular/core";
import { Subscription } from "rxjs";

import { ApiResponse } from "app/shared/utils/elearning-types";
import { SnackbarService } from "app/_services/snackbar.service";
import { SiteSettingsService } from "app/_services/site-settings.service";
import { AgreementsService } from "app/_services/agreements.service";
import { ModalService } from "app/_services/modal.service";

@Component({
    selector: "app-agreements-acceptance",
    templateUrl: "./agreements-acceptance.component.html",
    styleUrls: ["./agreements-acceptance.component.scss"],
})
export class AgreementsAcceptanceComponent implements OnInit, OnDestroy {
    showModalSubscription: Subscription;

    constructor(
        private agreementsService: AgreementsService,
        private modalService: ModalService,
        private settingservice: SiteSettingsService,
        private snackbarservice: SnackbarService,
    ) {}

    public activeTab: string = "terms"; // Active tab for terms and conditions content
    private user: any = null; // User object from cookie to use user ID for terms acceptance database storage
    private static readonly SCROLL_END_TOLERANCE = 10; // Tolerance for scrolling to bottom detection
    // Content
    public termsAndConditions: any = []; // Terms and conditions content
    public privacyPolicy: any = []; // Privacy policy content
    // Flags
    public termsScrolledToBottom: boolean = false; // Flag to check if user has scrolled to bottom of terms
    public privacyScrolledToBottom: boolean = false; // Flag to check if user has scrolled to bottom of privacy policy
    public isTermsAccepted: boolean = false; // Flag to check if user has accepted terms
    public isPrivacyAccepted: boolean = false; // Flag to check if user has accepted privacy policy

    async ngOnInit() {
        this.subscribeToAgreementsModalEvents();
        await this.fetchTermsAndConditions();
        await this.fetchPrivacyPolicy();
    }

    ngOnDestroy() {
        this.showModalSubscription.unsubscribe();
    }

    /**
     * Subscribe to events from the agreements modal service.
     */
    protected subscribeToAgreementsModalEvents() {
        this.showModalSubscription = this.agreementsService.showModal$.subscribe(
            (shouldShowModal: boolean) => {
                if (shouldShowModal) {
                    setTimeout(() => {
                        this.modalService.openModal("agreements-modal");
                    }, 0);
                } else {
                    this.modalService.closeModal("agreements-modal");
                }
            },
        );
    }

    /**
     * Fetch terms and conditions content from API.
     */
    protected async fetchTermsAndConditions() {
        try {
            const res: ApiResponse = await this.settingservice.getContentByKeyword("terms");
            if (res.data.status) {
                this.termsAndConditions = res.data.results;
            } else {
                throw Error(res.data.message);
            }
        } catch (error) {
            console.error("Error fetching terms and conditions", error);
            this.snackbarservice.showSnackbar({
                msg: "Error fetching terms and conditions. Try reloading the page.",
                status: false,
            });
        }
    }

    /**
     * Fetch privacy policy content from API.
     */
    protected async fetchPrivacyPolicy() {
        try {
            const res: ApiResponse = await this.settingservice.getContentByKeyword("privacy");
            if (res.data.status) {
                this.privacyPolicy = res.data.results;
            } else {
                throw Error(res.data.message);
            }
        } catch (error) {
            console.error("Error fetching privacy policy", error);
            this.snackbarservice.showSnackbar({
                msg: "Error fetching privacy policy. Try reloading the page.",
                status: false,
            });
        }
    }

    /**
     * Handle click event on terms and conditions tab.
     * @param tab - Name of tab that was clicked, either 'terms' or 'privacy'.
     */
    public onTabClick(tab: string) {
        this.setActiveTab(tab);
    }

    /**
     * Set the active tab for terms and conditions content.
     * @param tab - Name of tab to set as active, either 'terms' or 'privacy'.
     */
    public setActiveTab(tab: string) {
        if (tab !== "terms" && tab !== "privacy") {
            console.error("Invalid tab name", tab);
            return;
        }
        this.activeTab = tab;
    }

    /**
     * Handle scroll event on terms and conditions content, in order to detect if user has scrolled to bottom,
     * which indicates that user has read the content, and the checkbox can be enabled.
     * @param tab - Name of tab that is being scrolled, either 'terms' or 'privacy'.
     * @param event - Scroll event.
     */
    public onScroll(tab: string, event: any) {
        const element = event.target as HTMLElement;

        const scrolledToBottom =
            Math.abs(element.scrollHeight - element.scrollTop - element.clientHeight) <
            AgreementsAcceptanceComponent.SCROLL_END_TOLERANCE;

        if (scrolledToBottom) {
            if (tab === "terms") {
                this.termsScrolledToBottom = true;
            } else if (tab === "privacy") {
                this.privacyScrolledToBottom = true;
            }
        }
    }

    /**
     * Handle change event on acceptance checkbox.
     * @param event - Change event on acceptance checkbox.
     */
    public onAcceptanceChange(tab: string, event: any) {
        if (tab === "terms") {
            this.isTermsAccepted = event.target.checked;
            if (this.isTermsAccepted && !this.isPrivacyAccepted) {
                this.setActiveTab("privacy");
            }
        } else if (tab === "privacy") {
            this.isPrivacyAccepted = event.target.checked;
            if (this.isPrivacyAccepted && !this.isTermsAccepted) {
                this.setActiveTab("terms");
            }
        }
    }

    /**
     * Handle press of 'Accept' button.
     * If both terms and privacy policy are accepted,
     * 1. save the acceptance,
     * 2. reset the form,
     * 3  and emit the event.
     */
    public acceptBtnPressed() {
        // Check if both terms and privacy policy are accepted
        if (!this.checkboxesAllChecked()) {
            return false;
        }

        // Emit response event
        this.agreementsService.submitResponse(true);

        this.resetForm();
    }

    /**
     * Check if both terms and privacy policy are accepted.
     * @returns boolean - True if both terms and privacy policy are accepted, false otherwise.
     */
    protected checkboxesAllChecked(): boolean {
        if (!this.isTermsAccepted || !this.isPrivacyAccepted) {
            this.snackbarservice.showSnackbar({
                msg: "Please accept both terms and privacy policy to continue",
                status: false,
            });
            // Don't reset the form, so user can accept the terms again
            return false;
        }

        return true;
    }

    /**
     * Handle press of 'Reject' button.
     * Reset the form and emit the event.
     */
    public rejectBtnPressed() {
        this.agreementsService.submitResponse(false);
        this.resetForm();
    }

    /**
     * Handle closure of the modal.
     */
    public resetForm() {
        this.isTermsAccepted = false;
        this.isPrivacyAccepted = false;
        this.activeTab = "terms";
    }
}
