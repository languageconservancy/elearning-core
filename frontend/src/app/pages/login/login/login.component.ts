/**
 * Login Component
 * This component is used to handle user login and authentication.
 * It handles login with email and password, as well as social login methods.
 * It also handles redirects from Clever Single-Sign-On.
 * After login is successful and an auth token is fetched,
 * the user must agree to the terms and conditions before being redirected to the main app.
 */
/* eslint-disable @typescript-eslint/unbound-method */
import {
    Component,
    OnInit,
    AfterViewInit,
    OnDestroy,
    ElementRef,
    ViewChild,
    NgZone,
} from "@angular/core";
import { UntypedFormGroup, UntypedFormControl, Validators } from "@angular/forms";
import { Router } from "@angular/router";
import { ActivatedRoute } from "@angular/router";
import { DeviceDetectorService } from "ngx-device-detector";
import { jwtDecode } from "jwt-decode";
import { SocialAuthService } from "@abacritt/angularx-social-login";
import { Capacitor } from "@capacitor/core";
import {
    SignInWithApple,
    SignInWithAppleResponse,
    SignInWithAppleOptions,
} from "@capacitor-community/apple-sign-in";
// Not sure why this isn't being found
// import { CredentialResponse, PromptMomentNotification } from 'google-one-tap';
import { Subscription, firstValueFrom } from "rxjs";
import { filter, take } from "rxjs/operators";
import { BaseService } from "app/_services/base.service";
import { LoginService } from "app/_services/login.service";
import { RegistrationService } from "app/_services/registration.service";
import { Loader } from "app/_services/loader.service";
import { environment } from "environments/environment";
import { LocalStorageService } from "app/_services/local-storage.service";
import { RegexConsts } from "app/_constants/app.constants";
import { SocialWebService } from "app/_services/social-web.service";
import { SocialMobileService } from "app/_services/social-mobile.service";
import { SnackbarService } from "app/_services/snackbar.service";
import { User as UserApi } from "app/_constants/api.constants";
import { LearningPathService } from "app/_services/learning-path.service";
import { Routes } from "app/shared/utils/elearning-types";
import { SettingsService } from "app/_services/settings.service";
import { AgreementsService } from "app/_services/agreements.service";
import { ModalService } from "app/_services/modal.service";
import { TrialAccountService } from "app/_services/trial-account.service";
import { AgePromptService } from "app/_services/age-prompt.service";
import { PlatformRolesService } from "app/_services/platform-roles.service";

declare let window: any;
declare let google: any;

@Component({
    selector: "app-login",
    templateUrl: "./login.component.html",
    styleUrls: ["./login.component.scss"],
})
export class LoginComponent implements OnInit, OnDestroy, AfterViewInit {
    public loginForm: UntypedFormGroup;
    public loggedIn: boolean = false;
    public authUser: any;
    public cleverConfigValid: boolean = false;
    public facebookConfigValid: boolean = false;
    public googleConfigValid: boolean = false;
    public appleConfigValid: boolean = false;
    public loginUri = UserApi.LOGIN;
    public environment = environment;
    private subscriptions: Subscription[] = [];
    private socialAuthServiceSubscription: Subscription;
    private queryParamsSubscriber: Subscription;
    public isIos: boolean = false;
    public isAndroid: boolean = false;
    public isNativePlatform: boolean = false;
    private fbBtnClicked: boolean = false;
    private loginData: any;
    @ViewChild("googleSigninBtn") googleSigninButton: ElementRef;

    constructor(
        private agePromptService: AgePromptService,
        private loginService: LoginService,
        private authService: SocialAuthService,
        private router: Router,
        private loader: Loader,
        private registrationService: RegistrationService,
        private localStorage: LocalStorageService,
        private route: ActivatedRoute,
        private socialWebService: SocialWebService,
        private socialMobileService: SocialMobileService,
        private snackbarService: SnackbarService,
        private ngZone: NgZone,
        private baseService: BaseService,
        public deviceDetector: DeviceDetectorService,
        private learningPathService: LearningPathService,
        private settingsService: SettingsService,
        private agreementsService: AgreementsService,
        public modalService: ModalService,
        private trialAccountService: TrialAccountService,
        private platformRolesService: PlatformRolesService,
    ) {}

    ngOnInit() {
        if (this.deviceDetector.isMobile() || this.deviceDetector.isTablet()) {
            if (["iOS", "ios", "Mac", "mac"].indexOf(this.deviceDetector.os) > -1) {
                this.isIos = true;
            } else if (["android", "Android"].indexOf(this.deviceDetector.os) > -1) {
                this.isAndroid = true;
            }
        }
        if (Capacitor.isNativePlatform()) {
            this.isNativePlatform = true;
        }

        this.baseService.setLoginType("");

        // Hide or show social login butons based on config values
        this.checkSocialLoginConfigs();

        // Set up form bindings and validations
        this.setUpLoginForm();

        // Set up query params handling
        this.subscribeToQueryParams();

        if (
            (this.deviceDetector.isMobile() || this.deviceDetector.isTablet()) &&
            this.googleConfigValid
        ) {
            // Init Google login on mobile
            this.socialMobileService.initGoogle();
        } else if (this.facebookConfigValid) {
            // Set up callback for Facebook login on web
            this.setUpFacebookAuthSubscriber();
        }
    }

    setUpLoginForm() {
        this.loginForm = new UntypedFormGroup({
            email: new UntypedFormControl("", [
                Validators.required,
                Validators.pattern(RegexConsts.EMAIL_REGEX),
            ]),
            password: new UntypedFormControl("", Validators.required),
        });
    }

    subscribeToQueryParams() {
        // Handle redirects from Clever Single-Sign-On method, or email validation link
        this.queryParamsSubscriber = this.route.queryParamMap.subscribe((params: any) => {
            // Get Clever login params
            const loginParams = {
                code: params.get("code"),
                scope: params.get("scope"),
            };

            if (!loginParams.code || !loginParams.scope) {
                // Make sure there are no other query params that weren't handled
                const remainingParams = Array.from(params.keys).reduce((acc, key: any) => {
                    acc[key] = params.get(key);
                    return acc;
                }, {});
                if (Object.keys(remainingParams).length > 0) {
                    console.warn("Got unhandled query params: ", remainingParams);
                }
                return;
            }

            // If Clever config is invalid, display a message and return
            if (!this.cleverConfigValid) {
                this.socialWebService.displayCleverDisabledMessage();
                return;
            }

            // Extract Clever user data from query params
            const authData = this.socialWebService.extractCleverUserFromQueryParams(loginParams);
            if (!authData) return;

            // Log user in with Clever data
            void this.handleAsyncLogin(authData);
        });
        // Add subscription to list
        this.addSubscription(this.queryParamsSubscriber);
    }

    addSubscription(sub: Subscription) {
        this.subscriptions.push(sub);
    }

    setUpFacebookAuthSubscriber() {
        this.socialAuthServiceSubscription = this.authService.authState.subscribe((user) => {
            if (!user) {
                console.error("authServiceSubscriber bad user");
                return;
            }
            const provider = user?.provider.toLowerCase();
            if (provider !== "facebook") {
                console.warn("Got unhandled sign-in provider: ", user.provider);
                return;
            }
            if (provider === "facebook" && !this.fbBtnClicked) {
                // facebook login invalid repeat
                return;
            }
            const fbUser = this.socialWebService.extractFacebookUserData(user);
            void this.handleAsyncLogin(fbUser);
        });
        this.addSubscription(this.socialAuthServiceSubscription);
    }

    ngAfterViewInit() {
        if (this.deviceDetector.isMobile() || this.deviceDetector.isTablet()) {
            if (this.facebookConfigValid) {
                this.socialMobileService.initFacebook();
            }
        } else {
            if (this.googleConfigValid) {
                if (typeof google === "undefined") {
                    window.onload = () => {
                        this.setUpGoogleSignInWebBtn();
                    };
                } else {
                    this.setUpGoogleSignInWebBtn();
                }
            }
        }
    }

    ngOnDestroy() {
        this.subscriptions.forEach((sub) => sub?.unsubscribe());
    }

    /**
     * Creates Sign In With Google for Web button.
     * Initializes and renders the button.
     * Includes FedCM compliance workaround for iframe permissions.
     * References:
     *   - https://developers.google.com/identity/gsi/web/reference/js-reference
     *   - https://stackoverflow.com/questions/65439066/using-google-one-tap-in-angular
     *   - https://developer.mozilla.org/en-US/docs/Web/API/FedCM_API
     */
    setUpGoogleSignInWebBtn(): void {
        if (
            this.baseService.loginType != "" ||
            this.deviceDetector.isMobile() ||
            this.deviceDetector.isTablet()
        ) {
            return;
        }

        google.accounts.id.initialize({
            client_id: environment.GOOGLE_CLIENT_ID_WEB,
            callback: this.handleGoogleLoginBtnWebClick.bind(this),
            auto_select: false,
            cancel_on_tap_outside: true,
        });

        window.google.accounts.id.renderButton(this.googleSigninButton.nativeElement, {
            theme: "outline",
            size: "large",
            shape: "pill",
            width: "250",
            logo_alignment: "left",
        });

        // FedCM compliance workaround - add required permission to Google's iframe
        this.addFedCMPermissionToGoogleIframes();

        google.accounts.id.prompt((/*notification: any*/) => {
            // console.info("notification: ", notification);
        });
    }

    /**
     * FedCM Compliance Workaround
     * Monitors for Google Sign-in iframes and adds the required
     * allow="identity-credentials-get" attribute for FedCM compliance.
     * This is necessary because Google's SDK doesn't currently include
     * this attribute when creating iframes.
     */
    private addFedCMPermissionToGoogleIframes(): void {
        // Check for existing iframes first
        this.updateExistingGoogleIframes();

        // Monitor for new iframes being added
        if (typeof MutationObserver !== "undefined") {
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.type === "childList") {
                        mutation.addedNodes.forEach((node) => {
                            if (node.nodeType === Node.ELEMENT_NODE) {
                                const element = node as Element;
                                this.processElementForGoogleIframes(element);
                            }
                        });
                    }
                });
            });

            observer.observe(document.body, {
                childList: true,
                subtree: true,
            });

            // Clean up observer after a reasonable time
            setTimeout(() => observer.disconnect(), 10000);
        }
    }

    /**
     * Updates existing Google Sign-in iframes with FedCM permissions
     */
    private updateExistingGoogleIframes(): void {
        // Wait a bit for Google's SDK to create the iframe
        setTimeout(() => {
            this.processElementForGoogleIframes(document.body);
        }, 100);
    }

    /**
     * Recursively processes an element and its children to find and update Google iframes
     */
    private processElementForGoogleIframes(element: Element): void {
        // Check if this element is a Google iframe
        if (element.tagName === "IFRAME") {
            const iframe = element as HTMLIFrameElement;
            if (this.isGoogleSignInIframe(iframe)) {
                this.addFedCMPermissionToIframe(iframe);
            }
        }

        // Check all child elements
        element.querySelectorAll("iframe").forEach((iframe) => {
            if (this.isGoogleSignInIframe(iframe)) {
                this.addFedCMPermissionToIframe(iframe);
            }
        });
    }

    /**
     * Determines if an iframe is a Google Sign-in iframe
     */
    private isGoogleSignInIframe(iframe: HTMLIFrameElement): boolean {
        const src = iframe.src || "";
        const id = iframe.id || "";

        // Check for Google Sign-in iframe indicators
        return (
            src.includes("accounts.google.com") ||
            src.includes("gstatic.com") ||
            id.includes("gsi") ||
            iframe.getAttribute("data-gs-iframe") !== null ||
            iframe.closest("[data-gs-iframe]") !== null
        );
    }

    /**
     * Adds FedCM permission to a Google iframe
     */
    private addFedCMPermissionToIframe(iframe: HTMLIFrameElement): void {
        try {
            const currentAllow = iframe.getAttribute("allow") || "";

            // Check if identity-credentials-get is already present
            if (!currentAllow.includes("identity-credentials-get")) {
                const newAllow = currentAllow
                    ? `${currentAllow}; identity-credentials-get`
                    : "identity-credentials-get";

                iframe.setAttribute("allow", newAllow);
                console.log("Added FedCM permission to Google Sign-in iframe");
            }
        } catch (error) {
            console.warn("Failed to add FedCM permission to iframe:", error);
        }
    }

    /**
     * Sets social login config validity variables
     */
    checkSocialLoginConfigs() {
        this.cleverConfigValid = this.socialWebService.cleverConfigValid();
        this.facebookConfigValid = this.socialWebService.facebookConfigValid();
        this.googleConfigValid = this.socialWebService.googleConfigValid();
        this.appleConfigValid = this.socialWebService.appleConfigValid();
    }

    /**
     * Attempts to sign user in on mobile via Sign In With Google API.
     */
    async handleGoogleLoginBtnMobileTap(): Promise<void> {
        try {
            const authData = await this.socialMobileService.signInWithGoogle();
            await this.handleAsyncLogin(authData);
        } catch (err) {
            this.snackbarService.handleError(err, "Google login failed.");
        }
    }

    /**
     * Handles when the user clicks the google sign-in button on web.
     * @param {CredentialResponse} response - Google sign-in response
     */
    handleGoogleLoginBtnWebClick(response: any /*CredentialResponse*/) {
        const authData = this.socialWebService.extractGoogleUserFromCredentialResponse(response);
        this.ngZone.run(() => {
            void this.handleAsyncLogin(authData);
        });
    }

    /**
     * Attempts to log the user in using Facebook Login API.
     * Handles mobile and web.
     */
    async handleFacebookLoginBtnClickOrTap(): Promise<void> {
        if (this.deviceDetector.isMobile() || this.deviceDetector.isTablet()) {
            try {
                const fbUser = await this.socialMobileService.signInWithFacebook();
                await this.handleAsyncLogin(fbUser);
            } catch (err) {
                this.snackbarService.handleError(err, "Facebook login failed.");
            }
        } else {
            this.fbBtnClicked = true;
            try {
                await this.socialWebService.signInWithFacebook();
            } catch (err) {
                this.snackbarService.handleError(err, "Facebook login failed.");
            }
        }
    }

    /**
     * Initiates Sign in with Apple and passes that user data to the backend
     * for login into our app.
     */
    async handleAppleLoginBtnTap(): Promise<void> {
        try {
            const authData = await this.signInWithApple();
            await this.handleAsyncLogin(authData);
        } catch (err) {
            this.snackbarService.handleError(err, "Sign in with Apple failed.");
        }
    }

    /**
     * Handles signing in the Apple process. Subsequent calls don't include
     * the user's email or name. To reset this, on the phone, go to
     * Settings->Apple ID->Password & Security->Sign in with Apple and
     * swipe left on the app and tap Delete.
     */
    async signInWithApple(): Promise<any> {
        let appId = environment.APP_ID;
        appId += Capacitor.isNativePlatform() ? "" : ".web";
        const options: SignInWithAppleOptions = {
            clientId: appId,
            redirectURI: environment.LOGIN_URI,
            scopes: "email name",
            state: "12345",
            nonce: "nonce",
        };

        return SignInWithApple.authorize(options).then((result: SignInWithAppleResponse) => {
            if (!result?.response) {
                throw new Error("Error signing in with Apple");
            } else if (!result.response.identityToken) {
                throw new Error("Identity token missing from Apple sign in response");
            }

            // Extract response and fill in name and email if they're there.
            const appleUser = result.response;
            const errorMsg = "Bad Apple JWT. Try a different login method.";
            let decoded = null;
            try {
                decoded = jwtDecode(appleUser.identityToken);
            } catch (err) {
                this.snackbarService.handleError(err, errorMsg);
                return Promise.reject(err);
            }

            if (!decoded) {
                this.snackbarService.handleError(new Error("Bad Apple JWT"), errorMsg);
                return Promise.reject(new Error(errorMsg));
            }

            const loginData = {
                type: "apple",
                social_id: decoded.sub,
                name: "user",
                email: decoded.email || "default@email.com",
            };

            if (!!appleUser.givenName) {
                if (!!appleUser.familyName) {
                    loginData.name = `${appleUser.givenName} ${appleUser.familyName}`;
                } else {
                    loginData.name = appleUser.givenName;
                }
            } else if (!!appleUser.familyName) {
                loginData.name = appleUser.familyName;
            }
            return loginData;
        });
    }

    /**
     * Redirects to 'Sign In with Clever' button link defined in documentation
     * with our redirect URI and Client ID defined in our account settings on
     * the clever website.
     */
    handleCleverLoginBtnClick() {
        this.socialWebService.handleCleverLoginBtnClick();
    }

    /**
     * Attempts to log user in with the email and password they entered into
     * the login form.
     * @param {any} form - Login form (email and password)
     */
    async logInWithEmailAndPassword(form: any) {
        if (!form.valid) {
            this.snackbarService.handleError(null, "Please fill in all fields.");
            return;
        }

        const authData = {
            email: form.value.email,
            password: form.value.password,
            type: "site",
        };

        await this.handleAsyncLogin(authData);
    }

    openTrialModal() {
        if (document.activeElement instanceof HTMLElement) {
            document.activeElement.blur();
        }
        this.modalService.openModal("trial-message");
    }

    async tryApp() {
        this.modalService.closeModal("trial-message");
        try {
            const { authUser, authData } = await this.trialAccountService.handleTrialAccount();
            await this.handleLoginResponse(authUser, authData, true);
        } catch (error) {
            this.snackbarService.handleError(
                error,
                "Error creating trial account. Please try again.",
            );
        }
    }

    async handleAsyncLogin(loginData: any) {
        this.loader.setLoader(true);
        try {
            const authUser: any = await this.loginService.login(loginData);
            await this.handleLoginResponse(authUser, loginData);
        } catch (error) {
            this.snackbarService.handleError(error, "Login failed.");
            await this.handleFailedLogin();
        } finally {
            this.loader.setLoader(false);
        }
    }

    async handleLoginResponse(
        authUser: any,
        loginData: any,
        trial: boolean = false,
    ): Promise<void> {
        if (!authUser || !loginData) {
            throw new Error("Bad login response");
        }

        this.authUser = authUser;
        this.baseService.setLoginType(loginData.type);
        this.loginData = this.updateAuthDataPerType(loginData);

        if (authUser.firstLogin) {
            this.localStorage.setItem("regProg", "true");
        }

        if (!(await this.getToken(this.loginData))) {
            this.snackbarService.handleError("Error getting auth token. Please try again.");
            await this.handleFailedLogin();
            return;
        }

        const user = await this.agreementsService.handleAgreementsAcceptance(authUser);

        if (!user) {
            await this.deleteFirstTimeUser();
            await this.handleFailedLogin();
            return;
        }
        this.authUser = user;

        if (!trial) {
            if (!(await this.handleAgePrompt(this.authUser))) {
                await this.handleFailedLogin();
                return;
            }
        }

        if (!(await this.setAuthValuesToEnableLogin())) {
            await this.handleFailedLogin();
            return;
        }

        this.navigateToNextPage();
    }

    private async deleteFirstTimeUser() {
        if (this.authUser.firstLogin) {
            try {
                await this.settingsService.deleteAccount({ userId: this.authUser.id });
            } catch {
                this.snackbarService.handleError(null, "Error deleting first time user account.");
            }
        }
    }

    private updateAuthDataPerType(loginData: any): any {
        if (loginData.type === "clever") {
            loginData.email = this.authUser.email;
            loginData.social_id = this.authUser.clever_id;
        } else {
            // If missing type, default to site
            if (!loginData.type) {
                loginData.type = "site";
            }
        }
        return loginData;
    }

    /**
     * Logs the user out and reloads the login page to fix the navbar.
     * There is probably a better way to do this.
     * @returns {Promise<void>}
     */
    async handleFailedLogin(): Promise<void> {
        await this.baseService.logout();
        await this.router
            .navigateByUrl(Routes.Login, { skipLocationChange: true })
            .then(() => {
                this.navigateTo(Routes.Login);
            })
            .catch((err) => {
                console.error("Error navigating to home page", err);
            });
    }

    /**
     * If user used a social login method, prompt them to enter their age.
     * If they are under 13, prompt for parent's email.
     * @param {object} authUser - User object from login response.
     * @returns {Promise<boolean>} - True if age prompt was handled, false otherwise.
     */
    async handleAgePrompt(authUser: any): Promise<boolean> {
        try {
            // Only prompt users associated with a school for their age if we don't already
            // have their date of birth. This is so proper age related restrictions can be enforced.
            if (this.platformRolesService.isStudent(authUser.role_id) && authUser.dob !== null) {
                return true;
            }

            // If DOB is null, we never got their date of birth from their login method.
            // If their age is null, they are a minor and not part of a school, so we must
            // prompt them for their age and ensure underage users have parental notification.
            // The age null check here ensure we get parent emails for minors.
            if (authUser.dob === null || authUser.approximate_age === null) {
                return await this.promptForAge(authUser);
            }

            return true;
        } catch (error) {
            this.snackbarService.handleError(error, "Error getting auth token. Please try again.");
            return false;
        }
    }

    async promptForAge(user: any): Promise<boolean> {
        this.agePromptService.setUser(user);
        this.loader.setLoader(false);
        this.modalService.openModal("age-prompt");
        // Wait for the user to submit the form
        const response = await firstValueFrom(
            this.agePromptService.response$.pipe(
                filter(({ ok }) => ok !== null),
                take(1),
            ),
        );

        return response.ok;
    }

    private async setAuthValuesToEnableLogin(): Promise<boolean> {
        try {
            await this.baseService.setAuthUserCookie(this.authUser);
            this.registrationService.setUser(this.authUser);
            return true;
        } catch (error) {
            return false;
        }
    }

    /**
     * Attempts to get Auth Token if user is properly signed in and then route
     * them to the main app.
     * @param {object} params - Login authentication parameters.
     *     Varies for each login type.
     */
    private async getToken(params: any) {
        try {
            const res = await this.loginService.getAuthToken(params);
            if (!res) {
                throw res;
            }
            return true;
        } catch (err) {
            this.snackbarService.handleError(err, "Error getting auth token. Please try again.");
            return false;
        }
    }

    navigateToNextPage() {
        if (this.authUser.firstLogin) {
            this.checkLearningPath();
        } else {
            this.navigateTo(Routes.Dashboard);
        }
    }

    navigateTo(route: string) {
        void this.router.navigate([route]);
    }

    goToSignup() {
        this.navigateTo(Routes.Register);
    }

    checkLearningPath() {
        this.learningPathService
            .getLearningPaths({ user_id: this.authUser.id })
            .then((userLearningPaths: any) => {
                this.setLearningPath(userLearningPaths[0]);
            })
            .catch((err) => {
                console.error(err);
                this.navigateTo(Routes.LearningPath);
            })
            .finally(() => {
                this.loader.setLoader(false);
            });
    }

    setLearningPath(data: any) {
        const postData = {
            id: this.authUser.id,
            learningpath_id: data.id,
        };
        this.learningPathService
            .setLearningPath(postData)
            .then(() => {
                this.navigateTo(Routes.LearningSpeed);
            })
            .catch(() => {
                this.navigateTo(Routes.LearningPath);
            })
            .finally(() => {
                this.loader.setLoader(false);
            });
    }

    /**
     * Opens popup to prompt user to sign up instead of continuing with the
     * trial account, in order to use the app across devices, browsers, updates, etc.
     * @returns boolean - True if trial account was handled, false otherwise.
     */
    handleTrialAccounts(): boolean {
        if (
            this.trialAccountService.accountIsTrial(this.authUser.name, this.authUser.email) &&
            this.trialAccountService.trialPromptIsRequired(this.authUser.registered)
        ) {
            this.modalService.openModal("signup-suggestion");
            return true;
        }
        return false;
    }

    signupSuggestionModalClosed(continueWithTrial: boolean) {
        this.modalService.closeModal("signup-suggestion");
        if (continueWithTrial) {
            this.navigateToNextPage();
        } else {
            this.goToSignup();
        }
    }
}
