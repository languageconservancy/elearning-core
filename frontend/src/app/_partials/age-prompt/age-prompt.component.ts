import { Component, OnInit } from "@angular/core";
import { UntypedFormGroup, UntypedFormControl, Validators } from "@angular/forms";
import { RegexConsts } from "app/_constants/app.constants";
import { AgePromptService } from "app/_services/age-prompt.service";
import { SnackbarService } from "app/_services/snackbar.service";
import { SettingsService } from "app/_services/settings.service";
import { Routes } from "app/shared/utils/elearning-types";
import { ModalService } from "app/_services/modal.service";
import { RegionPolicyService } from "app/_services/region-policy.service";
import { PlatformRolesService } from "app/_services/platform-roles.service";
import { CookieService } from "app/_services/cookie.service";
import { BaseService } from "app/_services/base.service";

@Component({
    selector: "app-age-prompt",
    templateUrl: "./age-prompt.component.html",
    styleUrls: ["./age-prompt.component.css"],
})
export class AgePromptComponent implements OnInit {
    public Routes = Routes;
    public form: UntypedFormGroup;
    public show = {
        usernameField: false,
        parentsEmailField: false,
    };

    constructor(
        private agePromptService: AgePromptService,
        private settingsService: SettingsService,
        private snackbarService: SnackbarService,
        private modalService: ModalService,
        private regionPolicyService: RegionPolicyService,
        private platformRolesService: PlatformRolesService,
        private cookieService: CookieService,
        private baseService: BaseService,
    ) {}

    ngOnInit() {
        this.form = new UntypedFormGroup({
            age: new UntypedFormControl("", [
                // eslint-disable-next-line @typescript-eslint/unbound-method
                Validators.required,
                Validators.min(1),
                Validators.pattern(RegexConsts.AGE_REGEX),
            ]),
            // eslint-disable-next-line @typescript-eslint/unbound-method
            username: new UntypedFormControl("", {
                // eslint-disable-next-line @typescript-eslint/unbound-method
                validators: [Validators.required, this.validateBlankValue.bind(this)],
                updateOn: "blur",
            }),
            parentsEmail: new UntypedFormControl("", {
                // eslint-disable-next-line @typescript-eslint/unbound-method
                validators: [
                    // eslint-disable-next-line @typescript-eslint/unbound-method
                    Validators.required,
                    Validators.pattern(RegexConsts.EMAIL_REGEX),
                    this.validateBlankValue.bind(this),
                ],
                updateOn: "blur",
            }),
        });
    }

    public validateBlankValue = (control: UntypedFormControl): any => {
        if (this.form) {
            return control.value.trim() === "" ? { emptyValue: true } : null;
        }
    };

    public isFormValid = (): boolean => {
        const age = parseInt(this.form.get("age").value);
        if (this.form.get("age").invalid) {
            return false;
        }
        if (this.regionPolicyService.isChild(age)) {
            if (this.form.get("username").invalid || this.form.get("parentsEmail").invalid) {
                return false;
            }
        }
        return true;
    };

    /**
     * Triggered on age input change.
     * If age <13 change the form to COPPA compliant.
     * @param {string} ageStr - age input value
     */
    public ageChanged(ageStr: string): void {
        const age = parseInt(ageStr);

        if (this.regionPolicyService.isChild(age)) {
            this.show.usernameField = true;
            this.show.parentsEmailField = true;
        }

        if (!this.regionPolicyService.isChild(age)) {
            this.form.value.username = "";
            this.form.value.parentsEmail = "";
            this.show.usernameField = false;
            this.show.parentsEmailField = false;
        }
    }

    public displayFormErrorMessage(form: UntypedFormGroup): void {
        let msg = "";
        if (form.get("username").hasError("required")) {
            msg = "Please enter a username for your profile";
        } else if (form.get("age").hasError("required")) {
            msg = "Please enter your age";
        } else if (form.get("parentsEmail").hasError("required")) {
            msg = "Please enter your email";
        } else if (form.get("parentsEmail").hasError("pattern")) {
            msg = "Please enter a valid email";
        }
        if (msg === "") {
            msg = "Form invalid. Please check the form for errors.";
        }

        this.snackbarService.showSnackbar({ status: false, msg: msg });
    }

    /**
     * Submit form data to update user info in the database
     * and notify the age prompt subscribers.
     * @param form {UntypedFormGroup} - form data
     */
    public async submitForm(form: UntypedFormGroup): Promise<void> {
        if (!this.isFormValid()) {
            return;
        }
        const userData = this.setDataBasedOnAge(form);
        try {
            if (!(await this.updateUserInfo(userData))) {
                return;
            }
            if (this.regionPolicyService.isChild(parseInt(form.get("age").value))) {
                await this.notifyParentOfChildSignup(form.get("parentsEmail").value);
            }
            this.agePromptService.ageUpdated(true);
            this.modalService.closeModal("age-prompt");
        } catch (error) {
            this.snackbarService.handleError(error, "Error updating user data");
            this.agePromptService.ageUpdated(false);
        }
    }

    /**
     * Set user data based on age, adding parent email and username if they are a minor.
     * @param form {UntypedFormGroup} - form data
     * @returns {any} - user data to update in the database
     */
    private setDataBasedOnAge(
        form: UntypedFormGroup,
    ):
        | { id: number; name: string; email: string; approximate_age: number; dob: string }
        | { id: number; approximate_age: number; dob: string } {
        const userId = this.agePromptService.user.id;
        if (!userId) {
            throw new Error("User ID is invalid");
        }

        // Compute date of birth from today based on age
        const dob = new Date();
        dob.setFullYear(dob.getFullYear() - form.value.age);
        const formattedDob = dob.toISOString().split("T")[0];

        if (
            this.regionPolicyService.isChild(form.value.age) &&
            !this.platformRolesService.isStudent(this.agePromptService.user.role_id)
        ) {
            // Child not associated with an educational institution
            return {
                id: userId,
                name: form.value.username,
                email: form.value.parentsEmail,
                approximate_age: parseInt(form.value.age, 10),
                dob: formattedDob,
            };
        } else {
            // Non-child or student user
            return {
                id: userId,
                approximate_age: parseInt(form.value.age, 10),
                dob: formattedDob,
            };
        }
    }

    /**
     * Update user info in the database and refresh the AuthUser cookie.
     * @param userData {any} - user data to update in the database
     * @returns {Promise<boolean>} - true if successful, false otherwise
     */
    private async updateUserInfo(
        userData:
            | { id: number; name: string; email: string; approximate_age: number; dob: string }
            | { id: number; approximate_age: number; dob: string },
    ): Promise<boolean> {
        try {
            const res = await this.settingsService.updateUserData(userData);
            if (!res.data.status) {
                throw new Error(res.data.message);
            }

            // Update the AuthUser cookie with the new approximate_age
            const updatedAuthUser = await this.updateAuthUserCookie(userData);

            // Emit the updated user data to refresh navbar and other components
            if (updatedAuthUser) {
                // Add a small delay to ensure cookie is fully written before emitting
                setTimeout(() => {
                    this.settingsService.setUser(updatedAuthUser);
                }, 100);
            }

            return true;
        } catch (error) {
            this.snackbarService.handleError(error, "Error updating user data");
            return false;
        }
    }

    /**
     * Updates the AuthUser cookie with the new user data.
     * @param userData - The updated user data containing approximate_age
     * @returns The updated user object, or null if update failed
     */
    private async updateAuthUserCookie(userData: any): Promise<any> {
        try {
            // Get current AuthUser from cookie
            const currentAuthUserStr = await this.cookieService.get("AuthUser");
            if (!currentAuthUserStr) {
                console.warn("No AuthUser cookie found to update");
                return null;
            }

            const currentAuthUser = JSON.parse(currentAuthUserStr);

            // Update the user object with new data
            const updatedAuthUser = {
                ...currentAuthUser,
                approximate_age: parseInt(userData.approximate_age, 10),
                dob: userData.dob,
            };

            // If it's a child user, also update name and email
            if ("name" in userData && "email" in userData) {
                updatedAuthUser.name = userData.name;
                updatedAuthUser.email = userData.email;
            }

            // Save the updated user object back to the cookie
            await this.baseService.setAuthUserCookie(updatedAuthUser);

            return updatedAuthUser;
        } catch (error) {
            console.error("Error updating AuthUser cookie:", error);
            // Don't throw here - the database update was successful, cookie update is secondary
            return null;
        }
    }

    private async notifyParentOfChildSignup(parentEmail: string): Promise<boolean> {
        try {
            await this.settingsService.notifyParentOfChildSignup({
                user_id: this.agePromptService.user.id,
                parents_email: parentEmail,
            });
            return true;
        } catch (error) {
            this.snackbarService.handleError(error, "Error notifying parent of child signup");
            return false;
        }
    }
}
