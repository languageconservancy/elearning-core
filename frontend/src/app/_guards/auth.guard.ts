import { Injectable } from "@angular/core";
import { ActivatedRouteSnapshot, Router } from "@angular/router";
import { Observable, of, from } from "rxjs";
import { CookieService } from "app/_services/cookie.service";
import { LocalStorageService } from "app/_services/local-storage.service";
import { BaseService } from "app/_services/base.service";
import { SnackbarService } from "app/_services/snackbar.service";
import { AgreementsService } from "app/_services/agreements.service";
import { switchMap, catchError } from "rxjs/operators";
import { Routes } from "app/shared/utils/elearning-types";
import { environment } from "environments/environment";
import { RegistrationService } from "app/_services/registration.service";

@Injectable()
export class AuthGuard {
    private debug: boolean = !environment.production;

    constructor(
        private router: Router,
        private cookieService: CookieService,
        private baseService: BaseService,
        private snackService: SnackbarService,
        private registrationService: RegistrationService,
        private agreementsService: AgreementsService,
    ) {}

    canActivate(next: ActivatedRouteSnapshot): Observable<boolean> | Promise<boolean> | boolean {
        const currentRoute = next.routeConfig.path;

        if (this.isPublicRoute(currentRoute)) {
            return this.handlePublicRoute();
        } else {
            return this.handleProtectedRoute();
        }
    }

    /**
     * Check if the route is login or signup
     * @param route - The current route being navigated to.
     * @returns boolean - true if the route is login or signup, false otherwise.
     */
    private isPublicRoute(route: string): boolean {
        return ["", "register"].indexOf(route) > -1;
    }

    /**
     * If user is logged in, check if they have accepted terms, otherwise allow public route navigation.
     * @returns Observable<boolean> - true if user is logged in and has accepted
     * or accepts terms or isn't logged in, false otherwise.
     */
    private handlePublicRoute(): Observable<boolean> {
        return from(this.cookieService.get("AuthUser")).pipe(
            switchMap((user: string) => (user ? this.handleLoggedInUser(user) : of(true))),
            catchError(() => {
                // User not logged in. Allow navigation to public route.
                return of(true);
            }),
        );
    }

    /**
     * If user is logged in, check if they have accepted terms.
     * If accepted, redirect to dashboard, since already logged in.
     * If rejected, prompt to accept terms.
     * @param user - The user object from the AuthUser cookie.
     * @returns Observable<boolean> - true if user is logged in and has accepted terms or accepts terms.
     * false if user is logged in and declines terms.
     */
    private handleLoggedInUser(user: string): Observable<boolean> {
        const parsedUser = JSON.parse(user);

        if (!parsedUser.agreements_accepted) {
            return this.promptUserToAcceptTerms(parsedUser);
        }

        // User already logged in and has accepted terms. Redirect to dashboard.
        void this.router.navigate([Routes.Dashboard]);
        return of(false);
    }

    /**
     * If user is logged in, check if they have accepted terms.
     * If user not logged in, redirect to login page.
     * @returns Observable<boolean> - true if user is logged in and has accepted terms or accepts the terms.
     * false if user is not logged in or declines terms.
     */
    private handleProtectedRoute(): Observable<boolean> {
        return from(this.cookieService.get("AuthUser")).pipe(
            switchMap((userStr: string) =>
                userStr ? this.validateUserForProtectedRoute(userStr) : this.redirectToLogin(),
            ),
            catchError(() => this.redirectToLogin()),
        );
    }

    /**
     * If user is logged in, check if they have accepted terms or prompt to accept terms.
     * @param user - The user object string from the AuthUser cookie.
     * @returns Observable<boolean> - true if user has accepted terms or accepts terms.
     * false if user declines terms.
     */
    private validateUserForProtectedRoute(userStr: string): Observable<boolean> {
        const user = JSON.parse(userStr);

        if (!user.agreements_accepted) {
            return this.promptUserToAcceptTerms(user);
        }

        // AuthUser cookie found and terms accepted. Allow navigation.
        return of(true);
    }

    /**
     * Prompt user to accept terms and handles their response.
     * @param user - The user object from the AuthUser cookie.
     * @returns Observable<boolean> - true if user accepts terms, false if user declines terms.
     */
    private promptUserToAcceptTerms(user: any): Observable<boolean> {
        this.agreementsService
            .handleAgreementsAcceptance(user)
            .then((authUser) => {
                if (!authUser) {
                    return this.handleDeclinedTerms();
                }
                this.baseService
                    .setAuthUserCookie(authUser)
                    .then(() => {
                        this.registrationService.setUser(authUser);
                        return of(true);
                    })
                    .catch((error) => {
                        this.snackService.handleError(error, "Error setting user cookie.");
                        return of(false);
                    });
            })
            .catch((error) => {
                this.snackService.handleError(error, "Error processing agreements acceptance.");
                return of(false);
            });

        return of(false);
    }

    private handleDeclinedTerms(): Observable<boolean> {
        if (this.debug) {
            console.debug(`[${this.constructor.name}] User declined terms.`);
        }

        this.baseService
            .logout()
            .then(() => {
                this.snackService.showSnackbar({
                    msg: "You must accept the terms to use the app.",
                    status: false,
                });
                return this.redirectToLogin();
            })
            .catch((error) => {
                this.snackService.handleError(error, "Error logging out user.");
                return this.redirectToLogin();
            });

        return this.redirectToLogin();
    }

    private redirectToLogin(): Observable<boolean> {
        if (this.debug) {
            console.debug(
                `[${this.constructor.name}] User not logged in. Redirecting to login page.`,
            );
        }

        void this.router.navigate([Routes.Login]);
        return of(false);
    }
}

@Injectable()
export class RegProgGuard {
    private debug: boolean = !environment.production;

    constructor(
        private cookieService: CookieService,
        private router: Router,
        private localStorage: LocalStorageService,
        private snackService: SnackbarService,
        private baseService: BaseService,
        private registrationService: RegistrationService,
        private agreementsService: AgreementsService,
    ) {}

    canActivate(): Observable<boolean> | Promise<boolean> | boolean {
        if (this.signupInProgress()) {
            return true;
        }

        // If user is not in the process of signing up, check if they are logged in.
        // If logged in, check if they have accepted terms. If not, prompt them to accept terms,
        // and redirect them to the dashboard.
        // If not logged in, redirect them to the login page.
        // If they decline terms, redirect them to the login page.
        return from(this.cookieService.get("AuthUser")).pipe(
            switchMap((user: string) =>
                user ? this.handleLoggedInUser(user) : this.handleLoggedOutUser(),
            ),
            catchError(() => this.redirectToRoute(Routes.Login)),
        );
    }

    private handleLoggedInUser(userStr: string): Observable<boolean> {
        const parsedUser = JSON.parse(userStr);

        if (!parsedUser.agreements_accepted) {
            const authUser: any = this.agreementsService.handleAgreementsAcceptance(parsedUser);
            if (!authUser) {
                return this.handleDeclinedTerms();
            }
            void this.baseService.setAuthUserCookie(authUser);
            this.registrationService.setUser(authUser);
        }

        // AuthUser cookie found and terms accepted. Direct to dashboard.
        this.redirectToRoute(Routes.Dashboard);
    }

    private handleLoggedOutUser(): Observable<boolean> {
        return this.redirectToRoute(Routes.Login);
    }

    private handleDeclinedTerms(): Observable<boolean> {
        if (this.debug) {
            console.debug(`[${this.constructor.name}] User declined terms.`);
        }

        void this.baseService.logout();
        this.snackService.showSnackbar({
            msg: "You must accept the terms to continue.",
            status: false,
        });
        return this.redirectToRoute(Routes.Login);
    }

    private redirectToRoute(route: string): Observable<boolean> {
        if (this.debug) {
            console.debug(`[${this.constructor.name}] Redirecting to route: ${route}`);
        }

        void this.router.navigate([route]);
        return of(false);
    }

    private signupInProgress(): boolean {
        return this.localStorage.getItem("regProg") === "true";
    }
}
