import { ComponentFixture, tick, fakeAsync, TestBed, waitForAsync } from "@angular/core/testing";
import { RouterTestingModule } from "@angular/router/testing";
import { HttpClientModule } from "@angular/common/http";
import { CookieService as NgCookieService } from "ngx-cookie-service";
import { ReactiveFormsModule, FormsModule } from "@angular/forms";
import {
    SocialAuthServiceConfig,
    GoogleLoginProvider,
    FacebookLoginProvider,
    AmazonLoginProvider,
} from "@abacritt/angularx-social-login";

import { CookieService } from "app/_services/cookie.service";
import { LocalStorageService } from "app/_services/local-storage.service";
import { Loader } from "app/_services/loader.service";
import { SettingsService } from "app/_services/settings.service";
import { LessonsService } from "app/_services/lessons.service";
import { ReviewService } from "app/_services/review.service";
import { ExerciseService } from "app/_services/exercise.service";
import { KeyboardService } from "app/shared/keyboard/keyboard.service";
import { AudioService } from "app/_services/audio.service";
import { FillinComponent } from "./fillin.component";
import { PartialsModule } from "app/_partials/partials.module";
import { VirtualKeyboardComponent } from "app/_partials/virtual-keyboard/virtual-keyboard.component";

fdescribe("FillinComponent", () => {
    let component: FillinComponent;
    let fixture: ComponentFixture<FillinComponent>;

    beforeEach(waitForAsync(() => {
        void TestBed.configureTestingModule({
            declarations: [FillinComponent],
            providers: [
                SettingsService,
                Loader,
                LessonsService,
                ReviewService,
                ExerciseService,
                KeyboardService,
                AudioService,
                CookieService,
                LocalStorageService,
                PartialsModule,
                VirtualKeyboardComponent,
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
            imports: [RouterTestingModule, HttpClientModule, PartialsModule, ReactiveFormsModule, FormsModule],
        }).compileComponents();
    }));

    beforeEach(() => {
        fixture = TestBed.createComponent(FillinComponent);
        component = fixture.componentInstance;
        component.sessionType = "exercise";
        fixture.detectChanges();
    });

    it("should create", () => {
        // Set up member variables
        component.exerciseService.question = {
            exerciseOptions: {
                fill_in_the_blank_type: "typing",
                text_option: "He is playing. [Škáte]. Citation: [SápA].",
            },
            question: "Type the Lakota equivalent of the English phrase.",
        };
        component.exerciseService.exercise = {
            exercise_type: "fill_in_the_blanks",
        };
        fixture.detectChanges();
        expect(component).toBeTruthy();
    });

    it("should replace words in brackets with blanks (exercise session)", () => {
        // Set up member variables
        component.exerciseService.question = {
            exerciseOptions: {
                fill_in_the_blank_type: "typing",
                text_option: "Yes, I knew Ella Deloria. [Háŋ], [Ella] [Deloria] [slolwáye]. verb base form: [slolyÁ]",
            },
            question: "This is the question",
        };
        component.sessionType = "exercise";

        // Set up blanks and make sure they are correct and nothing else has a blank
        const matches = component["setUpBlanks"]();
        const expectedMatches = ["Háŋ", "Ella", "Deloria", "slolwáye", "slolyÁ"];
        const expectedInputArray = [
            ["Yes,"],
            ["I"],
            ["knew"],
            ["Ella"],
            ["Deloria."],
            ["", "Háŋ", ","],
            ["", "Ella", ""],
            ["", "Deloria", ""],
            ["", "slolwáye", "."],
            ["verb"],
            ["base"],
            ["form:"],
            ["", "slolyÁ", ""],
        ];
        for (let i = 0; i < matches.length; ++i) {
            expect(matches[i]).toEqual(expectedMatches[i]);
        }
        expect(component.uiResponseTextArray).toEqual(expectedInputArray);

        component["setUpBlankModel"](matches);
        let j = 0;
        for (let i = 0; i < component.blankText.length; ++i) {
            if (expectedInputArray[i].length == 1) {
                expect(component.blankText[i]).toEqual([]);
            } else {
                expect(component.blankText[i][1]).toEqual(expectedMatches[j]);
                ++j;
            }
        }
    });

    it("should setup blanks correctly", fakeAsync(() => {
        fixture.detectChanges();
        component.ngOnInit();
        component.sessionType = "exercise";
        component.exerciseService.question = {
            exerciseOptions: {
                fill_in_the_blank_type: "typing",
                text_option: "He is playing. [Škáte]. Citation: [SápA].",
            },
            question: "Type the Lakota equivalent of the English phrase.",
        };
        component.exerciseService.exercise = {
            exercise_type: "fill_in_the_blanks",
        };
        fixture.detectChanges();
        const matches = component["setUpBlanks"]();
        component["setUpBlankModel"](matches);
        tick(400);
        fixture.detectChanges();
        expect(component.uiResponseTextArray.length).toBeGreaterThan(0);
    }));

    it("should handle case correctly", fakeAsync(() => {
        fixture.detectChanges();
        component.ngOnInit();
        // Set up member variables
        component.exerciseService.question = {
            exerciseOptions: {
                fill_in_the_blank_type: "typing",
                text_option: "He is playing. [Škáte]. Citation: [SápA].",
            },
            question: "Type the Lakota equivalent of the English phrase.",
        };
        component.exerciseService.exercise = {
            exercise_type: "fill_in_the_blanks",
        };
        fixture.detectChanges();
        const matches = component["setUpBlanks"]();
        component["setUpBlankModel"](matches);
        tick(400);
        fixture.detectChanges();
        expect(component.uiResponseTextArray.length).toBeGreaterThan(0);

        // Set citation form to correct value so we can test other blank
        const citationForm = fixture.nativeElement.querySelector("#input_6_2") as HTMLInputElement;
        citationForm.value = "SápA";
        const blank = fixture.nativeElement.querySelector("#input_4_2") as HTMLInputElement;
        blank.value = "Škáte";
        component["submitTypeAnswer"]();
        tick(200);
        fixture.detectChanges();
        void fixture.whenStable().then(() => {
            expect(component.exerciseService.userAnswer).toEqual(component.AnswerType.CORRECT);
        });

        // Make sure wrong answer registers as INCORRECT
        citationForm.value = "SápA";
        blank.value = "dkáte";
        component["submitTypeAnswer"]();
        tick(200);
        fixture.detectChanges();
        void fixture.whenStable().then(() => {
            expect(component.exerciseService.userAnswer).toEqual(component.AnswerType.INCORRECT);
        });

        // Make sure lowercase first letter works
        citationForm.value = "SápA";
        blank.value = "škáte";
        component["submitTypeAnswer"]();
        tick(200);
        fixture.detectChanges();
        void fixture.whenStable().then(() => {
            expect(component.exerciseService.userAnswer).toEqual(component.AnswerType.CORRECT);
        });

        // Make sure uppercase first letter works
        citationForm.value = "SápA";
        blank.value = "Škáte";
        component["submitTypeAnswer"]();
        tick(200);
        fixture.detectChanges();
        void fixture.whenStable().then(() => {
            expect(component.exerciseService.userAnswer).toEqual(component.AnswerType.CORRECT);
        });

        // Now make sure citation form is case-sensitive for non-first letters
        citationForm.value = "Sápa";
        blank.value = "Škáte";
        component["submitTypeAnswer"]();
        tick(200);
        fixture.detectChanges();
        void fixture.whenStable().then(() => {
            expect(component.exerciseService.userAnswer).toEqual(component.AnswerType.INCORRECT);
        });
    }));
});
