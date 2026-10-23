import { Injectable } from "@angular/core";
import { environment } from "@environments/environment";

interface BootstrapModalInstance {
    show(): void;
    hide(): void;
}

declare const bootstrap: {
    Modal: {
        getOrCreateInstance(element: Element): BootstrapModalInstance;
    };
};

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
        const element = this.getModalElement(modalId);
        if (!element) return;

        if (this.debug) {
            console.log(`Opening modal ${modalId}`);
        }

        bootstrap.Modal.getOrCreateInstance(element).show();
    }

    /**
     * Closes a Bootstrap modal with comprehensive cleanup.
     * @param modalId - The ID of the modal to close, without hash symbol.
     */
    closeModal(modalId: string): void {
        const element = this.getModalElement(modalId);
        if (!element) return;

        if (this.debug) {
            console.log(`Closing modal ${modalId}`);
        }

        element.addEventListener(
            "hidden.bs.modal",
            () => {
                if (this.debug) {
                    console.log(`Modal ${modalId} is hidden`);
                }
            },
            { once: true },
        );

        bootstrap.Modal.getOrCreateInstance(element).hide();
    }

    /**
     * Checks if a Bootstrap modal is open.
     * @param modalId - The ID of the modal to check, without hash symbol.
     * @returns True if the modal is open, false otherwise.
     */
    isModalOpen(modalId: string): boolean {
        return this.getModalElement(modalId)?.classList.contains("show") ?? false;
    }

    private getModalElement(modalId: string): HTMLElement | null {
        const element = document.getElementById(modalId);

        if (!element && this.debug) {
            console.warn(`Modal element ${modalId} was not found`);
        }

        return element;
    }
}
