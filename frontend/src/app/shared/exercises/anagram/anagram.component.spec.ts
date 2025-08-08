import { fakeAsync, ComponentFixture, TestBed, waitForAsync } from "@angular/core/testing";
import { CookieService } from "app/_services/cookie.service";
import { RouterTestingModule } from "@angular/router/testing";
import { HttpClientModule } from "@angular/common/http";
import { CookieService as NgCookieService } from "ngx-cookie-service";
import {
    SocialAuthServiceConfig,
    GoogleLoginProvider,
    FacebookLoginProvider,
    AmazonLoginProvider,
} from "@abacritt/angularx-social-login";

import { Loader } from "app/_services/loader.service";
import { LessonsService } from "app/_services/lessons.service";
import { ReviewService } from "app/_services/review.service";
import { ExerciseService } from "app/_services/exercise.service";
import { LocalStorageService } from "app/_services/local-storage.service";
import { KeyboardService } from "app/shared/keyboard/keyboard.service";
import { AudioService } from "app/_services/audio.service";
import { AnagramComponent } from "./anagram.component";
import { NonSelectableCardComponent } from "app/shared/non-selectable-card/non-selectable-card.component";

describe("AnagramComponent", () => {
    let component: AnagramComponent;
    let fixture: ComponentFixture<AnagramComponent>;

    beforeEach(waitForAsync(() => {
        void TestBed.configureTestingModule({
            declarations: [AnagramComponent, NonSelectableCardComponent],
            providers: [
                CookieService,
                LessonsService,
                Loader,
                LocalStorageService,
                LessonsService,
                ReviewService,
                ExerciseService,
                AudioService,
                KeyboardService,
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
            imports: [RouterTestingModule, HttpClientModule],
        }).compileComponents();
    }));

    beforeEach(() => {
        fixture = TestBed.createComponent(AnagramComponent);
        component = fixture.componentInstance;
        component.sessionType = "exercise";
        fixture.detectChanges();
    });

    it("should create", () => {
        expect(component).toBeTruthy();
    });

    it("should show instruction if they are not empty", fakeAsync(() => {
        // Set exercise instructions and update page
        const instructionText = "These are the instructions";
        component.exerciseService.exercise = {
            instruction: instructionText,
        };
        fixture.detectChanges();
        // Assert that the instructions are shown
        const element: HTMLElement = fixture.nativeElement;
        const instructions = element.querySelector("h5");
        expect(instructions.textContent).toEqual(instructionText);
    }));
});
