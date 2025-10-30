import { Injectable } from "@angular/core";
import { environment } from "environments/environment";

declare let jQuery: any;

@Injectable({
    providedIn: "root",
})
export class ModalService {
    debug: boolean = !environment.production;

    /**
     * Opens a Bootstrap modal with proper initialization.
     * @param modalId - The ID of the modal to open, without hash symbol.
     */
    openModal(modalId: string): void {
        if (this.debug) {
            console.log(`Opening modal ${modalId}`);
        }

        jQuery(`#${modalId}`).modal("show");
    }

    /**
     * Closes a Bootstrap modal with comprehensive cleanup.
     * @param modalId - The ID of the modal to close, without hash symbol.
     */
    closeModal(modalId: string): void {
        if (this.debug) {
            console.log(`Closing modal ${modalId}`);
        }

        const modal = jQuery(`#${modalId}`);

        // Use Bootstrap's proper close method
        modal.modal("hide");

        // Set up one-time event listener for when modal is fully hidden
        modal.one("hidden.bs.modal", () => {
            this.performModalCleanup(modalId);
        });
    }

    /**
     * Checks if a Bootstrap modal is open.
     * @param modalId - The ID of the modal to check, without hash symbol.
     * @returns True if the modal is open, false otherwise.
     */
    isModalOpen(modalId: string): boolean {
        return jQuery(`#${modalId}`).hasClass("show");
    }

    /**
     * Performs comprehensive modal cleanup following Bootstrap best practices.
     * This method only runs after Bootstrap's hidden.bs.modal event fires.
     * @param modalId - The ID of the modal that was closed.
     */
    private performModalCleanup(modalId: string): void {
        if (this.debug) {
            console.log(`Performing cleanup for modal ${modalId}`);
        }

        // Small delay to ensure Bootstrap's cleanup has completed
        setTimeout(() => {
            // Only clean up if there are no other open modals
            const openModals = document.querySelectorAll(".modal.show");

            if (openModals.length === 0) {
                // Remove any lingering backdrops (should be cleaned by Bootstrap, but just in case)
                const backdrops = document.querySelectorAll(".modal-backdrop");
                if (backdrops.length > 0) {
                    if (this.debug) {
                        console.log(`Removing ${backdrops.length} lingering backdrop(s)`);
                    }
                    backdrops.forEach((backdrop) => backdrop.remove());
                }

                // Ensure body classes are clean
                document.body.classList.remove("modal-open");

                // Reset body styles that Bootstrap might have set
                document.body.style.paddingRight = "";
                document.body.style.overflow = "";

                if (this.debug) {
                    console.log(`Modal cleanup completed for ${modalId}`);
                }
            } else {
                if (this.debug) {
                    console.log(`Skipping body cleanup - ${openModals.length} modal(s) still open`);
                }
            }
        }, 50);
    }
}
