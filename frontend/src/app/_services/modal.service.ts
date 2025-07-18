import { Injectable } from "@angular/core";
import { environment } from "environments/environment";

declare let jQuery: any;

@Injectable({
    providedIn: "root",
})
export class ModalService {
    debug: boolean = !environment.production;

    /**
     * Opens a Bootstrap modal.
     * @param modalId - The ID of the modal to open, without hash symbol.
     */
    openModal(modalId: string): void {
        if (this.debug) {
            console.log(`Opening modal ${modalId}`);
        }
        jQuery(`#${modalId}`).modal("show");
    }

    /**
     * Closes a Bootstrap modal.
     * @param modalId - The ID of the modal to close, without hash symbol.
     */
    closeModal(modalId: string): void {
        if (this.debug) {
            console.log(`Closing modal ${modalId}`);
        }
        jQuery(`#${modalId}`).modal("hide");
    }

    /**
     * Checks if a Bootstrap modal is open.
     * @param modalId - The ID of the modal to check, without hash symbol.
     * @returns True if the modal is open, false otherwise.
     */
    isModalOpen(modalId: string): boolean {
        return jQuery(`#${modalId}`).hasClass("show");
    }
}
