import { fakeAsync, tick, ComponentFixture, TestBed, waitForAsync } from "@angular/core/testing";
import { RouterTestingModule } from "@angular/router/testing";
import { HttpClientModule } from "@angular/common/http";
import { CookieService as NgCookieService } from "ngx-cookie-service";

import { CookieService } from "app/_services/cookie.service";
import {
    SocialAuthService,
    SocialLoginModule,
    SocialAuthServiceConfig,
    GoogleLoginProvider,
    FacebookLoginProvider,
    AmazonLoginProvider,
} from "@abacritt/angularx-social-login";
import { ReactiveFormsModule } from "@angular/forms";

import { PartialsModule } from "app/_partials/partials.module";
import { LearningPathService } from "app/_services/learning-path.service";
import { Loader } from "app/_services/loader.service";
import { LocalStorageService } from "app/_services/local-storage.service";
import { LoginService } from "app/_services/login.service";
import { RegistrationService } from "app/_services/registration.service";
import { SettingsService } from "app/_services/settings.service";
import { LoginComponent } from "./login.component";
import { SocialWebService } from "app/_services/social-web.service";
import { ColorThemeRgb } from "../../../../../e2e/lib/color-theme";
import { BaseService } from "app/_services/base.service";
import { ForumService } from "app/_services/forum.service";

fdescribe("LoginComponent", () => {
    let component: LoginComponent;
    let fixture: ComponentFixture<LoginComponent>;
    const loginService: LoginService = new LoginService(null, null, null, null);
    const registrationService: RegistrationService = new RegistrationService(null, null, null, null);
    const socialWebService: SocialWebService = new SocialWebService(null, null);

    beforeEach(waitForAsync(() => {
        void TestBed.configureTestingModule({
            declarations: [LoginComponent],
            providers: [
                CookieService,
                NgCookieService,
                ForumService,
                LearningPathService,
                Loader,
                LocalStorageService,
                LoginService,
                RegistrationService,
                SettingsService,
                SocialAuthService,
                BaseService,
                { provide: LoginService, useValue: loginService },
                { provide: SocialWebService, useValue: socialWebService },
                {
                    provide: "SocialAuthServiceConfig",
                    useValue: {
                        providers: [
                            {
                                id: GoogleLoginProvider.PROVIDER_ID,
                                provider: new GoogleLoginProvider("clientId"),
                            },
                            {
                                id: FacebookLoginProvider.PROVIDER_ID,
                                provider: new FacebookLoginProvider("clientId"),
                            },
                            {
                                id: AmazonLoginProvider.PROVIDER_ID,
                                provider: new AmazonLoginProvider("clientId"),
                            },
                        ],
                    } as SocialAuthServiceConfig,
                },
            ],
            imports: [RouterTestingModule, HttpClientModule, SocialLoginModule, PartialsModule, ReactiveFormsModule],
        }).compileComponents();
    }));

    beforeEach(() => {
        spyOn(socialWebService, "initFacebook").and.returnValue(
            new Promise((resolve) => {
                resolve(true);
            }),
        );
        localStorage.clear();
        fixture = TestBed.createComponent(LoginComponent);
        component = fixture.componentInstance;
        spyOn(component, "getToken");
        fixture.detectChanges();
    });

    it("should create", () => {
        expect(component).toBeTruthy();
    });

    it('should have "Forgot Password?" displayed as link text', () => {
        const element: HTMLElement = fixture.nativeElement;
        const forgotPasswordBtn = element.querySelector("#forgot-pwd-btn");
        expect(forgotPasswordBtn.textContent).toEqual("Forgot Password?");
    });

    it('should have "Submit" displayed on submit button', () => {
        const element: HTMLElement = fixture.nativeElement;
        const submitBtn = element.querySelector("button[type='submit']");
        expect(submitBtn.textContent).toEqual("Submit");
        expect(window.getComputedStyle(submitBtn, null).getPropertyValue("background-color")).toEqual(
            ColorThemeRgb.UI_PANEL_LIGHT,
        );
        expect(window.getComputedStyle(submitBtn, null).getPropertyValue("color")).toEqual(ColorThemeRgb.TEXT_PRIMARY);
    });

    it("should have facebook button", () => {
        const element: HTMLElement = fixture.nativeElement;
        const btn = element.querySelector("#facebook-signin-link");
        expect(btn.textContent.trim()).toEqual("Sign in with Facebook");
        expect(window.getComputedStyle(btn, null).getPropertyValue("background-color")).toEqual(
            ColorThemeRgb.UI_PANEL_LIGHT,
        );
        expect(window.getComputedStyle(btn, null).getPropertyValue("color")).toEqual(
            ColorThemeRgb.TEXT_UI_PANEL_LIGHT_CONTRAST,
        );
    });

    it("should have clever button", () => {
        if (!component.cleverConfigValid) {
            expect(component.cleverConfigValid).toEqual(false);
            return;
        } else {
            const element: HTMLElement = fixture.nativeElement;
            const btn = element.querySelector("#clever-signin-link");
            expect(btn.textContent.trim()).toEqual("Sign in with Clever");
            expect(window.getComputedStyle(btn, null).getPropertyValue("background-color")).toEqual(
                ColorThemeRgb.UI_PANEL_LIGHT,
            );
            expect(window.getComputedStyle(btn, null).getPropertyValue("color")).toEqual(
                ColorThemeRgb.TEXT_UI_PANEL_LIGHT_CONTRAST,
            );
        }
    });

    // it("no or missing query params should not initiate clever login", () => {
    //     component.logInWithClever({ params: {} });
    //     expect(component.loginData).undefined;

    //     component.logInWithClever({ params: { code: "df" } });
    //     expect(component.loginData).undefined;
    // });

    it("clever login shouldn't set localStorage regProg for existing users", fakeAsync(() => {
        spyOn(loginService, "login").and.returnValue(
            new Promise((resolve) => {
                const results = {
                    "0": {
                        clever_id: "lkj",
                        id: 2,
                        firstLogin: false,
                    },
                };
                const response = { status: true, message: "message", results: results };
                resolve({ data: response });
            }),
        );
        spyOn(loginService, "authenticate").and.returnValue(
            new Promise((resolve) => {
                resolve("token");
            }),
        );
        spyOn(registrationService, "setUser");
        // component.logInWithClever({ params: { code: "df", scope: "gh" } });
        tick(500);
        fixture.detectChanges();
        const regProg = localStorage.getItem("regProg");
        expect(regProg).toBeFalsy();
    }));

    it("clever login should set localStorage regProg to true for firstLogins", fakeAsync(() => {
        spyOn(loginService, "login").and.returnValue(
            new Promise((resolve) => {
                const results = [
                    {
                        clever_id: "lkj",
                        id: 2,
                        firstLogin: true,
                    },
                ];
                const response = { status: true, message: "message", results: results };
                resolve({ data: response });
            }),
        );
        spyOn(loginService, "authenticate").and.returnValue(
            new Promise((resolve) => {
                resolve("token");
            }),
        );
        spyOn(registrationService, "setUser");
        // component.logInWithClever({ code: "df", scope: "gh" });
        tick(500);
        fixture.detectChanges();
        // const regProg = localStorage.getItem("regProg");
        // expect(regProg).toEqual("true");
    }));
});
