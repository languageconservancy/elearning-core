import { Component, OnInit, OnDestroy } from "@angular/core";
import { UntypedFormGroup, UntypedFormControl, Validators } from "@angular/forms";
import { Router } from "@angular/router";
import { Subscription } from "rxjs";

import { LearningPathService } from "app/_services/learning-path.service";
import { Loader } from "app/_services/loader.service";
import { LocalStorageService } from "app/_services/local-storage.service";
import { LoginService } from "app/_services/login.service";
import { RegistrationService } from "app/_services/registration.service";
import { RegexConsts } from "app/_constants/app.constants";
import { SnackbarService } from "app/_services/snackbar.service";
import { environment } from "environments/environment";
import { SettingsService } from "app/_services/settings.service";
import { BaseService } from "app/_services/base.service";
import { AgreementsService } from "app/_services/agreements.service";
import { Routes } from "app/shared/utils/elearning-types";
import { RegionPolicyService } from "app/_services/region-policy.service";

@Component({
    selector: "app-registration",
    templateUrl: "./registration.component.html",
    styleUrls: ["./registration.component.scss"],
})
export class RegistrationComponent implements OnInit, OnDestroy {
    private userSubscription: Subscription;
    public userId: string;
    public registrationForm: any;
    public userData: any;
    public captchaResponse: boolean = false;
    public dobErrorMsg: string = "";
    public usernamePlaceholder: string = "Username";
    public emailPlaceholder: string = "Email";
    public tokenParams = { email: "", password: "", type: "site" };
    public captchaSiteKey: string = this.runningOnLocalhost()
        ? "6LfJRxwgAAAAAL7jSsHK4C9U0jgcAwWDy70Ylyl_"
        : "6LfZ48wfAAAAALDjum-CDaIHeM64Oxdfakkp_WPS";
    private previousAge: number = 0;
    private isChild: boolean = false;

    constructor(
        private router: Router,
        private learningPathService: LearningPathService,
        public loginService: LoginService,
        public registrationService: RegistrationService,
        private localStorage: LocalStorageService,
        private loader: Loader,
        private agreementsService: AgreementsService,
        private snackbarService: SnackbarService,
        private settingsService: SettingsService,
        private baseService: BaseService,
        private regionPolicyService: RegionPolicyService,
    ) {
        this.userSubscription = this.registrationService.currentUser.subscribe(
            (userId) => (this.userId = userId),
        );
    }

    ngOnInit() {
        const emailRegex = RegexConsts.EMAIL_REGEX;

        this.registrationForm = new UntypedFormGroup({
            age: new UntypedFormControl("", [
                // eslint-disable-next-line @typescript-eslint/unbound-method
                Validators.required,
                Validators.min(1),
                Validators.pattern(/^([1-9])([0-9]){0,1}$/),
            ]),
            // eslint-disable-next-line @typescript-eslint/unbound-method
            name: new UntypedFormControl("", [
                Validators.required,
                this.validateBlankValue.bind(this),
            ]),
            email: new UntypedFormControl("", {
                // eslint-disable-next-line @typescript-eslint/unbound-method
                validators: [
                    Validators.required,
                    Validators.pattern(emailRegex),
                    this.validateBlankValue.bind(this),
                ],
                updateOn: "blur",
            }),
            // eslint-disable-next-line @typescript-eslint/unbound-method
            password: new UntypedFormControl("", [
                Validators.required,
                this.validateBlankValue.bind(this),
            ]),
            confirmpassword: new UntypedFormControl("", {
                // eslint-disable-next-line @typescript-eslint/unbound-method
                validators: [Validators.required, this.validatePasswordConfirmation.bind(this)],
                updateOn: "blur",
            }),
        });
    }

    ngOnDestroy() {
        this.userSubscription.unsubscribe();
    }

    private validatePasswordConfirmation = (control: UntypedFormControl): any => {
        if (this.registrationForm) {
            if (control.value === this.registrationForm.get("password").value) {
                return null;
            } else {
                return { notSame: true };
            }
        }
    };

    private validateBlankValue = (control: UntypedFormControl): any => {
        if (this.registrationForm) {
            return control.value.trim() === "" ? { emptyValue: true } : null;
        }
    };

    public runningOnLocalhost(): boolean {
        return environment.ROOT.indexOf("localhost") >= 0;
    }

    addLeadingZero(number) {
        if (parseInt(number) < 10) {
            number = "0" + number;
        }
        return number;
    }

    /**
     * Triggered on age input change.
     * If age requires parental consent change the form to COPPA compliant.
     * If age doesn't parental consent or blank, change the form to non-COPPA compliant.
     * @param {string} ageStr - age input value
     */
    ageChanged(ageStr: string) {
        const age = parseInt(ageStr);

        if (this.regionPolicyService.isChild(age)) {
            this.usernamePlaceholder = "Username (not your real name)";
            this.emailPlaceholder = "Parent's email";
            this.isChild = true;
        } else {
            this.usernamePlaceholder = "Username";
            this.emailPlaceholder = "Email";
            this.isChild = false;
        }

        if (
            (this.previousAge === 0 || !this.regionPolicyService.isChild(this.previousAge)) &&
            this.regionPolicyService.isChild(age)
        ) {
            // Clear name and email fields if age is changed to child age so they don't accidentally
            // use their real name or their own email.
            this.registrationForm.controls["name"].setValue("");
            this.registrationForm.controls["email"].setValue("");
        }

        this.previousAge = age;
    }

    async emailChanged(email: string) {
        try {
            if (await this.registrationService.isEmailRegistered({ email: email })) {
                this.registrationForm.controls["email"].setErrors({ nonUniqueEmail: true });
            }
        } catch (error) {
            console.error(error);
        }
    }

    captchaResolved(data) {
        this.loader.setLoader(true);
        this.registrationService
            .getCaptchaResponse({ token: data })
            .then((res: any) => {
                this.captchaResponse = res.data.results.success;
            })
            .catch((err) => {
                console.error(err);
            })
            .finally(() => {
                this.loader.setLoader(false);
            });
    }

    convertAgeToDob(age: string): string {
        // Generate date of birth from age but set month and day to January 1st
        const date = new Date();
        date.setFullYear(date.getFullYear() - parseInt(age));
        const isoDate = date.toISOString().split("T")[0];
        return isoDate;
    }

    register(form) {
        if (!form.valid) {
            this.displayFormErrorMessage(this.registrationForm);
            return;
        }

        if (this.captchaResponse || this.runningOnLocalhost()) {
            const data = {
                name: form.value.name,
                dob: this.convertAgeToDob(form.value.age),
                email: form.value.email,
                password: form.value.password,
                repassword: form.value.confirmpassword,
            };
            this.doRegister(data);
        } else {
            this.snackbarService.showSnackbar({
                status: false,
                msg: "Please verify the recaptcha before moving forward.",
            });
        }
    }

    displayFormErrorMessage(form) {
        let msg = "";
        if (form.get("name").hasError("required")) {
            msg = "Please enter a username for your profile";
        } else if (form.get("age").hasError("required")) {
            msg = "Please enter your age";
        } else if (form.get("email").hasError("required")) {
            msg = "Please enter your email";
        } else if (form.get("email").hasError("pattern")) {
            msg = "Please enter a valid email";
        }
        if (msg === "") {
            msg = "Form invalid. Please check the form for errors.";
        }

        this.snackbarService.showSnackbar({ status: false, msg: msg });
    }

    doRegister(data: {
        name?: string;
        dob?: string;
        email: string;
        password: string;
        repassword?: string;
    }) {
        this.loader.setLoader(true);
        this.registrationService
            .register(data)
            .then(async (authUser: any) => {
                this.userData = authUser;
                this.localStorage.setItem("regProg", "true");
                this.tokenParams.email = data.email;
                this.tokenParams.password = data.password;
                this.tokenParams.type = "site";
                if (!(await this.getToken(this.tokenParams))) {
                    this.snackbarService.handleError("Error getting token", "Please try again.");
                    await this.handleFailedSignup();
                    return;
                }
                const updatedUser =
                    await this.agreementsService.handleAgreementsAcceptance(authUser);
                if (!updatedUser) {
                    await this.handleFailedSignup();
                    return;
                }
                this.userData = updatedUser;
                await this.setAuthValuesToEnableSignup();
                try {
                    await this.settingsService.notifyParentOfChildSignup({
                        parents_email: data.email,
                        user_id: this.userData.id,
                    });
                } catch (error) {
                    console.error("Error notifying parent of child signup", error);
                    this.snackbarService.handleError(
                        error,
                        "Error notifying parent of child signup. Please check your email.",
                    );
                }
                await this.navigateToNextPage();
            })
            .catch((err) => {
                this.snackbarService.handleError(err, "Error registering. Please try again.");
            })
            .finally(() => {
                this.loader.setLoader(false);
            });
    }

    async handleFailedSignup(): Promise<void> {
        await this.settingsService.deleteAccount({ userId: this.userData.id });
        await this.baseService.logout();
        await this.router
            .navigateByUrl(Routes.Register, { skipLocationChange: true })
            .then(() => {
                this.navigateTo(Routes.Register);
            })
            .catch((err) => {
                console.error("Error navigating to registration page", err);
            });
    }

    async setAuthValuesToEnableSignup() {
        await this.baseService.setAuthUserCookie(this.userData);
        this.registrationService.setUser(this.userData);
    }

    async getToken(params: { email: string; password: string; type: string }) {
        try {
            const res = await this.loginService.getAuthToken(params);
            if (res == "") {
                throw new Error("Error getting token");
            }
            return true;
        } catch (err) {
            this.snackbarService.handleError(err, "Invalid email or password. Please check.");
            return false;
        }
    }

    navigateTo(route: string) {
        void this.router.navigate([route]);
    }

    async navigateToNextPage() {
        if (this.userData.firstLogin) {
            // If it's the user's first login, navigate to learning path
            // so user can choose their learning path.
            void this.router.navigate(["learning-path"]);
            return;
        }

        try {
            this.loader.setLoader(true);
            const userLearningPaths = await this.learningPathService.getLearningPaths({
                user_id: this.userData.id,
            });
            if (userLearningPaths.length === 1) {
                this.setLearningPath(userLearningPaths[0]);
            } else {
                void this.router.navigate([Routes.LearningPath]);
            }
        } catch (error) {
            console.error("Error getting learning paths", error);
            void this.router.navigate([Routes.LearningPath]);
        } finally {
            this.loader.setLoader(false);
        }
    }

    setLearningPath(pathId: number) {
        const postData = {
            id: this.userData.id,
            learningpath_id: pathId,
        };
        this.learningPathService
            .setLearningPath(postData)
            .then(() => {
                void this.router.navigate([Routes.LearningSpeed]);
            })
            .catch(() => {
                void this.router.navigate([Routes.LearningPath]);
            })
            .finally(() => {
                this.loader.setLoader(false);
            });
    }
}
