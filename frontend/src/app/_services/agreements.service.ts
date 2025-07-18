import { Injectable } from "@angular/core";
import { BehaviorSubject, firstValueFrom } from "rxjs";
import { BaseService } from "app/_services/base.service";
import * as API from "app/_constants/api.constants";
import { ApiResponse } from "app/shared/utils/elearning-types";
import { filter, take } from "rxjs/operators";
import { Loader } from "app/_services/loader.service";
import { SnackbarService } from "app/_services/snackbar.service";
import { SettingsService } from "./settings.service";

@Injectable({
    providedIn: "root",
})
export class AgreementsService {
    private showModalSubject = new BehaviorSubject<boolean>(false);
    public showModal$ = this.showModalSubject.asObservable();

    private responseSubject = new BehaviorSubject<{
        accepted: boolean | null;
    }>({
        accepted: null,
    });
    public response$ = this.responseSubject.asObservable();

    constructor(
        private loader: Loader,
        private snackbarService: SnackbarService,
        private baseService: BaseService,
        private settingsService: SettingsService,
    ) {}

    // Open modal
    openModal(): void {
        this.showModalSubject.next(true);
    }

    // Submit response
    submitResponse(accepted: boolean): void {
        this.responseSubject.next({ accepted });
        this.closeModal();
    }

    // Reset and close modal
    closeModal(): void {
        this.showModalSubject.next(false);
    }

    private hasAcceptedAgreements(authUser: any): boolean {
        return authUser?.agreements_accepted;
    }

    /**
     * Ensure the user has accepted the terms and conditions before continuing,
     * by showing the agreements modal if they haven't.
     * @param authUser {any} - User object from the cookie.
     * @returns {Promise<any>} User object if accepted, null if declined.
     */
    public async handleAgreementsAcceptance(authUser: any): Promise<any> {
        try {
            if (this.hasAcceptedAgreements(authUser)) {
                return authUser;
            }

            const user = await this.handleAgreementsNotYetAccepted(authUser);
            if (!user) {
                return null;
            }
            return user;
        } catch (error) {
            this.snackbarService.handleError(
                error,
                "Error processing agreements acceptance. Please try again.",
            );
            return null;
        } finally {
            this.loader.setLoader(false);
        }
    }

    private async handleAgreementsNotYetAccepted(authUser: any): Promise<any> {
        // Show agreements acceptance modal
        this.loader.setLoader(false);
        this.openModal();

        // Handle user response to agreements modal
        const response = await firstValueFrom(
            this.response$.pipe(
                filter(({ accepted }) => accepted !== null),
                take(1),
            ),
        );

        try {
            this.loader.setLoader(true);
            const user = await this.handleAgreementsModalResponse(response.accepted, authUser);
            if (!user) {
                return null;
            }
            return user;
        } catch (error) {
            this.snackbarService.handleError(
                error,
                "Error processing agreements acceptance. Please try again.",
            );
            return null;
        } finally {
            this.loader.setLoader(false);
        }
    }

    /**
     * Handles the user's response to the agreements modal.
     * @param accepted {boolean} - Whether the user accepted the agreements or not.
     * @returns {Promise<any>} - User object if agreements were accepted, null otherwise.
     */
    private async handleAgreementsModalResponse(accepted: boolean, authUser: any): Promise<any> {
        if (!accepted) {
            this.snackbarService.showSnackbar({
                msg: "You must accept the terms to use the app.",
                status: false,
            });
            return null;
        }

        // Save agreements acceptance to DB
        const user = await this.saveAgreementsAcceptance(authUser.id);
        if (!user) {
            throw new Error("Error saving agreements acceptance to DB");
        }
        return user;
    }

    /**
     * Saves the user's acceptance of the agreements.
     * Requires auth token to be set.
     * @param userId {number} User ID
     * @returns {Promise<any>} Promise that resolves with updated User entity
     * when the agreements acceptance is saved.
     */
    async saveAgreementsAcceptance(userId: number): Promise<any> {
        if (userId < 0) {
            throw new Error("User ID is invalid");
        }

        const res: ApiResponse = await this.baseService.callApi(
            API.User.SAVE_AGREEMENTS_ACCEPTANCE,
            "POST",
            { user_id: userId },
            {},
            "site",
            true,
        );
        if (!res.data.status || !res.data.results?.user) {
            throw Error(res.data.message);
        }
        return res.data.results.user;
    }
}
