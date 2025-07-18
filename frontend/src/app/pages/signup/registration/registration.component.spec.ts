import { ComponentFixture, TestBed, waitForAsync } from "@angular/core/testing";
import { RouterTestingModule } from "@angular/router/testing";
import { HttpClientModule } from "@angular/common/http";
import { CookieService as NgCookieService } from "ngx-cookie-service";
import {
    SocialAuthServiceConfig,
    GoogleLoginProvider,
    FacebookLoginProvider,
    AmazonLoginProvider,
} from "@abacritt/angularx-social-login";

import { CookieService } from "app/_services/cookie.service";
import { LearningPathService } from "../../../_services/learning-path.service";
import { Loader } from "../../../_services/loader.service";
import { LocalStorageService } from "../../../_services/local-storage.service";
import { LoginService } from "../../../_services/login.service";
import { RegistrationService } from "../../../_services/registration.service";
import { ReactiveFormsModule } from "@angular/forms";
import { RecaptchaModule, RecaptchaFormsModule } from "ng-recaptcha";
import { RegistrationComponent } from "./registration.component";
import { ForumService } from "app/_services/forum.service";
import { PartialsModule } from "app/_partials/partials.module";
import { PageTitleComponent } from "app/_partials/page-title/page-title.component";

fdescribe("RegistrationComponent", () => {
    let component: RegistrationComponent;
    let fixture: ComponentFixture<RegistrationComponent>;

    beforeEach(waitForAsync(() => {
        void TestBed.configureTestingModule({
            declarations: [RegistrationComponent],
            providers: [
                CookieService,
                LearningPathService,
                ForumService,
                Loader,
                LocalStorageService,
                LoginService,
                RegistrationService,
                PartialsModule,
                PageTitleComponent,
                NgCookieService,
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
            imports: [
                RouterTestingModule,
                HttpClientModule,
                ReactiveFormsModule,
                RecaptchaModule,
                RecaptchaFormsModule,
                PartialsModule,
            ],
        }).compileComponents();
    }));

    beforeEach(() => {
        fixture = TestBed.createComponent(RegistrationComponent);
        component = fixture.componentInstance;
        component.ngOnInit();
        fixture.detectChanges();
    });

    it("should create", () => {
        expect(component).toBeTruthy();
    });

    it("should get correct form control validity", () => {
        const nameInput = component.registrationForm.controls.name;
        const dayInput = component.registrationForm.controls.day;
        const monthInput = component.registrationForm.controls.month;
        const yearInput = component.registrationForm.controls.year;
        const passwordInput = component.registrationForm.controls.password;
        const confirmPasswordInput = component.registrationForm.controls.confirmpassword;
        const emailInput = component.registrationForm.controls.email;

        expect(nameInput.valid).toBeFalse();
        expect(dayInput.valid).toBeFalse();
        expect(monthInput.valid).toBeFalse();
        expect(yearInput.valid).toBeFalse();
        expect(passwordInput.valid).toBeFalse();
        expect(confirmPasswordInput.valid).toBeFalse();
        expect(emailInput.valid).toBeFalse();

        nameInput.setValue("Test Name");
        expect(nameInput.valid).toBeTrue();
        dayInput.setValue("10");
        expect(dayInput.valid).toBeTrue();
        monthInput.setValue("1");
        expect(monthInput.valid).toBeTrue();
        yearInput.setValue("1984");
        expect(yearInput.valid).toBeTrue();
        passwordInput.setValue("myP@asswor d");
        expect(passwordInput.valid).toBeTrue();
        confirmPasswordInput.setValue("myP@asswor d");
        expect(confirmPasswordInput.valid).toBeTrue();
        emailInput.setValue("!hello");
        expect(emailInput.valid).toBeFalse();
    });

    it("should accept these email addresses", () => {
        const validEmails = [
            "test@domain.com", // standard
            "test.email.with+symbol@domain.com", // multiple dots in local part and plus sign
            "id-with-dash@domain.com", // dashes in local part
            "example-abc@abc-domain.com", // dash in domain label
            "#!$%&'*+-/=?^_{}|~@domain.org", // special characters
            "example@s.solutions", // single character domain label
            "d+og@gm-mail.com", // dash in domain label
            "test@s.s", // single character top-level-domain
            "-sdf@gmail.com", // local part starting with dash
            "asdf-@gmail.com", // local part ending with dash
            "sdf--sdf@gmail.com", // local part with repeating special character
            "sdfsdfsdfsdfsdfsdfsdfsdfsdfsdfsdfsdfsdfsdfsdfsdfsdfsdfsdfsdfsdsd@gail.com", // local part of length 64
            "sdf@sdf.sdf.sdf.fdf", // more than two domain labels
            "sdf@sdfsdfsdfsdfsdfsdfsdfsdfsdfsdfsdfsdfsdfsdfsdfsdfsdfsdfsfsdfsdfs.com", // domain label of 63 characters
            // Emails that should fail but we aren't excluding because regex is complicated without negative look-behind
            "cat@-gmai.com", // domain label starting with dash
            "sdf@asf--asdf.com", // two hyphens in a row in domain label
            "sdf@asd-.com", // domain label ending in hyphen
            "cat@-gmai.co", // domain label starting with hyphen
        ];

        const emailInput = component.registrationForm.controls.email;
        expect(emailInput.valid).toBeFalse();

        validEmails.forEach(function (email) {
            emailInput.setValue(email);
            expect(emailInput.valid).toBeTrue();
        });
    });

    it("should NOT accept these email addresses", () => {
        const invalidEmails = [
            '"abc.testemail"@domain.com', // quotes in local part
            "test@gmail.sd-", // dash in top-level-domain
            "test@gmail.cd2", // number in top-level-domain
            "a@domain.com (one-letter local part)", // parenthese
            '"abc.test email"@domain.com', // quotes and space in local part
            '"xyz.test.@.test.com"@domain.com', // quotes and multiple @ symbols
            '"abc.(),:;<>[]".EMAIL."email@ "email".test"@strange.domain.com', // multiple quotes
            "“()<>[]:,;@\\”!#$%&’-/=?^_`{}| ~.a”@domain.org", // quotes with special characters
            "” “@domain.org (space between the quotes)", // quotes and space and parentheses
            "example@localhost (sent from localhost)", // parentheses
            "test@localserver", // no top-level-domain
            "cat@_gmail.com", // domain starting with underscore
            "test@[IPv6:2018:db8::1]", // brackets
            "sdf@sdfsdfsdfsdfsdfsdfsdfsdfsdfsdfsdfsdfsdfsdfsdfsdfsdfsdfsdfsdfsdfs.com", // domain label longer than 63 characters
            "pete@gamil.", // domain ending in period
            "pete@.sd", // domain starting with period
            "@sdf.com", // empty local part
            "pete@as..sdf", // domain with two periods in a row
            "sdfsdfsdfsdfsdfsdfsdfsdfsdfsdfsdfsdfsdfsdfsdfsdfsdfsdfsdfsdfsdfdg@gmail.com", // local part with more than 63 characters
        ];

        const emailInput = component.registrationForm.controls.email;
        expect(emailInput.valid).toBeFalse();

        invalidEmails.forEach(function (email) {
            emailInput.setValue(email);
            if (emailInput.valid) {
                console.log("Email: ", emailInput.value);
                console.log("Email.valid: ", emailInput.valid);
            }
            expect(emailInput.valid).toBeFalse();
        });
    });
});
