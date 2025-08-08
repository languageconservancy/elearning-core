/// <reference types="jasmine" />
import { ComponentFixture, TestBed, waitForAsync } from "@angular/core/testing";
import { RouterTestingModule } from "@angular/router/testing";
import { Router } from "@angular/router";
import { HttpClientModule } from "@angular/common/http";
import { CookieService as NgCookieService } from "ngx-cookie-service";
import {
    SocialAuthServiceConfig,
    GoogleLoginProvider,
    FacebookLoginProvider,
    AmazonLoginProvider,
} from "@abacritt/angularx-social-login";

import { CookieService } from "app/_services/cookie.service";
import { RegistrationService } from "app/_services/registration.service";
import { SettingsService } from "app/_services/settings.service";
import { LocalStorageService } from "app/_services/local-storage.service";
import { ReviewService } from "app/_services/review.service";
import { BaseService } from "app/_services/base.service";
import { ForumService } from "app/_services/forum.service";
import { NavbarComponent } from "./navbar.component";
import { ColorThemeRgb } from "../../../../e2e/lib/color-theme";

describe("NavbarComponent", () => {
    let component: NavbarComponent;
    let fixture: ComponentFixture<NavbarComponent>;
    let router: Router;

    beforeEach(waitForAsync(() => {
        void TestBed.configureTestingModule({
            declarations: [NavbarComponent],
            providers: [
                CookieService,
                NgCookieService,
                RegistrationService,
                SettingsService,
                LocalStorageService,
                ReviewService,
                BaseService,
                ForumService,
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
            imports: [RouterTestingModule, HttpClientModule],
        }).compileComponents();
    }));

    beforeEach(() => {
        router = TestBed.get(Router);
        spyOnProperty(router, "url", "get").and.returnValue("/start-learning");
        fixture = TestBed.createComponent(NavbarComponent);
        component = fixture.componentInstance;
        spyOn<any>(component, "getUserSettings").and.returnValue(true);
        component.loggedIn = true;
        component.showReview = true;
        component.user = {
            role_id: 2,
            classroom_count: 1,
            agreements_accepted: true, // This is required for the nav menu to show
        };
        fixture.detectChanges();
    });

    it("should create", () => {
        expect(component).toBeTruthy();
    });

    it("should not error before user.usersetting becomes non-null and name should be non-null", () => {
        component.loggedIn = true;
        component.user = {
            usersetting: null,
        };
        fixture.detectChanges();
        expect(component).toBeTruthy();
    });

    it("should have buttons displayed correctly", () => {
        const element: HTMLElement = fixture.nativeElement;
        let el = element.querySelector("#nav-learn-btn");
        expect(el).toBeTruthy(); // Check element exists first
        expect(el.textContent).toBeTruthy();
        expect(el.textContent).toEqual("Learn");
        expect(window.getComputedStyle(el, null).getPropertyValue("color")).toBe(
            ColorThemeRgb.TEXT_UI_PANEL_DARK_CONTRAST,
        );
        expect(window.getComputedStyle(el, null).getPropertyValue("background-color")).toBe(
            ColorThemeRgb.PRIMARY_COLOR_DARK,
        );

        el = element.querySelector("#nav-review-btn");
        expect(el).toBeTruthy(); // Check element exists first
        expect(el.textContent).toBeTruthy();
        expect(el.textContent).toEqual("Review");
        expect(window.getComputedStyle(el, null).getPropertyValue("color")).toBe(
            ColorThemeRgb.TEXT_UI_PANEL_DARK_CONTRAST,
        );

        el = element.querySelector("#nav-progress-btn");
        expect(el).toBeTruthy(); // Check element exists first
        expect(el.textContent).toBeTruthy();
        expect(el.textContent).toEqual("Progress");
        expect(window.getComputedStyle(el, null).getPropertyValue("color")).toBe(
            ColorThemeRgb.TEXT_UI_PANEL_DARK_CONTRAST,
        );

        el = element.querySelector("#nav-leaderboard-btn");
        expect(el).toBeTruthy(); // Check element exists first
        expect(el.textContent).toBeTruthy();
        expect(el.textContent).toEqual("Leaderboard");
        expect(window.getComputedStyle(el, null).getPropertyValue("color")).toBe(
            ColorThemeRgb.TEXT_UI_PANEL_DARK_CONTRAST,
        );

        el = element.querySelector("#nav-village-btn");
        expect(el).toBeTruthy(); // Check element exists first
        expect(el.textContent).toBeTruthy();
        expect(el.textContent).toEqual("Village");
        expect(window.getComputedStyle(el, null).getPropertyValue("color")).toBe(
            ColorThemeRgb.TEXT_UI_PANEL_DARK_CONTRAST,
        );

        el = element.querySelector("#nav-teachers-btn");
        expect(el).toBeTruthy(); // Check element exists first
        expect(el.textContent).toBeTruthy();
        expect(el.textContent).toEqual("Teachers");
        expect(window.getComputedStyle(el, null).getPropertyValue("color")).toBe(
            ColorThemeRgb.TEXT_UI_PANEL_DARK_CONTRAST,
        );

        el = element.querySelector("#nav-classroom-btn");
        expect(el).toBeTruthy(); // Check element exists first
        expect(el.textContent).toBeTruthy();
        expect(el.textContent).toEqual("Classroom");
        expect(window.getComputedStyle(el, null).getPropertyValue("color")).toBe(
            ColorThemeRgb.TEXT_UI_PANEL_DARK_CONTRAST,
        );
    });
});
