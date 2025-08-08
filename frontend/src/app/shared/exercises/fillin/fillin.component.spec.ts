/// <reference types="jasmine" />
import { ComponentFixture, tick, fakeAsync, TestBed } from "@angular/core/testing";
import { RouterTestingModule } from "@angular/router/testing";
import { HttpClientTestingModule } from "@angular/common/http/testing";
import { ReactiveFormsModule, FormsModule } from "@angular/forms";
import { DeviceDetectorService } from "ngx-device-detector";
import { of } from "rxjs";

import { CookieService } from "app/_services/cookie.service";
import { LocalStorageService } from "app/_services/local-storage.service";
import { SettingsService } from "app/_services/settings.service";
import { LessonsService } from "app/_services/lessons.service";
import { ReviewService } from "app/_services/review.service";
import { ExerciseService } from "app/_services/exercise.service";
import { KeyboardService } from "app/shared/keyboard/keyboard.service";
import { AudioService } from "app/_services/audio.service";
import { SnackbarService } from "app/_services/snackbar.service";
import { BaseService } from "app/_services/base.service";
import { FillinComponent } from "./fillin.component";
import { VirtualKeyboardComponent } from "app/_partials/virtual-keyboard/virtual-keyboard.component";
import { AnswerType } from "app/shared/utils/elearning-types";

describe("FillinComponent", () => {
    let component: FillinComponent;
    let fixture: ComponentFixture<FillinComponent>;
    let mockExerciseService: any;
    let mockLessonsService: any;
    let mockReviewService: any;
    let mockAudioService: any;
    let mockKeyboardService: any;
    let mockCookieService: any;
    let mockLocalStorageService: any;
    let mockSnackbarService: any;
    let mockBaseService: any;
    let mockDeviceDetectorService: any;

    beforeEach(() => {
        // Create mocks for all dependencies
        mockExerciseService = {
            question: {},
            exercise: {},
            choices: [],
            user: { id: 1 },
            userAnswer: AnswerType.NONE,
            promptTypes: [],
            responseTypes: [],
            setPromptResponseTypes: jasmine.createSpy("setPromptResponseTypes"),
        };

        mockLessonsService = {
            currentExercise: of({}),
            currentQuestion: of({}),
            popup: of({}),
            answerGiven: jasmine.createSpy("answerGiven"),
            wrongAnswerGiven: jasmine.createSpy("wrongAnswerGiven"),
            setWrongCards: jasmine.createSpy("setWrongCards"),
        };

        mockReviewService = {
            currentExercise: of({}),
            popup: of({}),
            answerGiven: jasmine.createSpy("answerGiven"),
            wrongAnswerGiven: jasmine.createSpy("wrongAnswerGiven"),
            setWrongCards: jasmine.createSpy("setWrongCards"),
        };

        mockAudioService = {
            playPauseAudio: jasmine.createSpy("playPauseAudio"),
            pauseAudio: jasmine.createSpy("pauseAudio"),
        };

        mockKeyboardService = {
            toggleSelectionEvent: of({}),
            toggleMediaEvent: of({}),
            submitOrCloseEvent: of({}),
            typingEvent: of({}),
        };

        mockCookieService = {
            get: jasmine.createSpy("get").and.returnValue(Promise.resolve('{"id": 1}')),
        };

        mockLocalStorageService = {
            getItem: jasmine.createSpy("getItem").and.returnValue("1"),
        };

        mockSnackbarService = {
            showSnackbar: jasmine.createSpy("showSnackbar"),
        };

        mockBaseService = {
            logout: jasmine.createSpy("logout"),
        };

        mockDeviceDetectorService = {
            isMobile: jasmine.createSpy("isMobile").and.returnValue(false),
            isTablet: jasmine.createSpy("isTablet").and.returnValue(false),
        };

        TestBed.configureTestingModule({
            declarations: [FillinComponent, VirtualKeyboardComponent],
            providers: [
                { provide: BaseService, useValue: mockBaseService },
                { provide: CookieService, useValue: mockCookieService },
                { provide: LessonsService, useValue: mockLessonsService },
                { provide: ReviewService, useValue: mockReviewService },
                { provide: ExerciseService, useValue: mockExerciseService },
                { provide: AudioService, useValue: mockAudioService },
                { provide: LocalStorageService, useValue: mockLocalStorageService },
                { provide: KeyboardService, useValue: mockKeyboardService },
                { provide: DeviceDetectorService, useValue: mockDeviceDetectorService },
                { provide: SnackbarService, useValue: mockSnackbarService },
                SettingsService,
            ],
            imports: [
                RouterTestingModule,
                HttpClientTestingModule,
                ReactiveFormsModule,
                FormsModule,
            ],
        });

        fixture = TestBed.createComponent(FillinComponent);
        component = fixture.componentInstance;
        component.sessionType = "exercise";
    });

    it("should create", () => {
        // Set up basic exercise data
        mockExerciseService.question = {
            exerciseOptions: {
                fill_in_the_blank_type: "typing",
                text_option: "He is playing. [Škáte]. Citation: [SápA].",
            },
            question: "Type the Lakota equivalent of the English phrase.",
        };
        mockExerciseService.exercise = {
            exercise_type: "fill_in_the_blanks",
        };

        fixture.detectChanges();
        expect(component).toBeTruthy();
    });

    describe("typing exercises", () => {
        beforeEach(() => {
            mockExerciseService.question = {
                exerciseOptions: {
                    fill_in_the_blank_type: "typing",
                    text_option:
                        "Yes, I knew Ella Deloria. [Háŋ], [Ella] [Deloria] [slolwáye]. verb base form: [slolyÁ]",
                },
                question: "Fill in the blanks",
                FullAudioUrl: "test-audio.mp3",
            };
            mockExerciseService.exercise = {
                exercise_type: "fill_in_the_blanks",
            };
            mockExerciseService.choices = [];
            component.sessionType = "exercise";
        });

        it("should initialize UI correctly for typing exercise", () => {
            spyOn<any>(component, "initUi").and.callThrough();
            component.ngOnInit();

            expect(component.fillInType).toBe("");
            expect(mockExerciseService.setPromptResponseTypes).toHaveBeenCalled();
        });

        it("should parse text with brackets correctly", () => {
            const testText = "Hello [world] and [universe]";
            const result = component["parseText"](testText, 0);

            expect(result.parsedText).toEqual([
                { type: "text", value: "Hello " },
                { type: "blank", optionName: "world", position: 0 },
                { type: "text", value: " and " },
                { type: "blank", optionName: "universe", position: 1 },
            ]);
            expect(result.positionCounter).toBe(2);
        });

        it("should create UI response text array from exercise text", () => {
            component.fillInType = "typing";
            component["createUiResponseTextArray"]();

            expect(component.uiGroupedTextArray.length).toBeGreaterThan(0);
            // Should have parsed the text into groups of UI text objects
            const hasBlankType = component.uiGroupedTextArray.some((group) =>
                group.some((item) => item.type === "blank"),
            );
            expect(hasBlankType).toBe(true);
        });

        it("should set up blank model correctly", () => {
            component.fillInType = "typing";
            component["createUiResponseTextArray"]();
            component["setUpBlankModel"]();

            expect(component.answer.length).toBeGreaterThan(0);
            expect(component.blankFlatIndexMap.length).toBeGreaterThan(0);
            expect(component.activeBlankIndex).toBe(0);
        });

        it("should handle blank click for typing mode", () => {
            component.fillInType = "typing";
            component["createUiResponseTextArray"]();
            component["setUpBlankModel"]();

            const mockEvent = { target: { focus: jasmine.createSpy("focus") } };
            spyOn(component, "setActiveBlankIndex");

            component.typingResponseBlankClicked(0, 0, mockEvent);

            expect(component.setActiveBlankIndex).toHaveBeenCalledWith(0, 0);
        });
    });

    describe("multiple choice exercises", () => {
        beforeEach(() => {
            mockExerciseService.question = {
                exerciseOptions: {
                    fill_in_the_blank_type: "mcq",
                    text_option: "The [dog] is [big].",
                },
                question: "Choose the correct words",
            };
            mockExerciseService.exercise = {
                exercise_type: "fill_in_the_blanks",
            };
            mockExerciseService.choices = [
                { option_name: "dog", position: 0, used: false },
                { option_name: "cat", position: 1, used: false },
                { option_name: "big", position: 2, used: false },
                { option_name: "small", position: 3, used: false },
            ];
            component.fillInType = "mcq";
        });

        it("should initialize choices correctly", () => {
            component["initializeChoices"]();

            expect(mockExerciseService.choices[0].optionName).toBe("dog");
            expect(mockExerciseService.choices[0].used).toBe(false);
        });

        it("should handle MCQ choice click", () => {
            component["createUiResponseTextArray"]();
            component["setUpBlankModel"]();

            const choice = { optionName: "dog", position: 0, used: false };
            spyOn(component, "addMcqChoiceToBlank");
            spyOn(component, "setNextActiveMcqInput");
            spyOn(component, "setChoiceUsed");

            component.mcqChoiceClicked(choice, 0);

            expect(component.addMcqChoiceToBlank).toHaveBeenCalledWith(choice);
            expect(component.setNextActiveMcqInput).toHaveBeenCalled();
            expect(component.setChoiceUsed).toHaveBeenCalledWith(0, true);
        });

        it("should check if all MCQ blanks are filled", () => {
            component["createUiResponseTextArray"]();
            component["setUpBlankModel"]();

            // Initially should be false
            expect(component.areAllMcqBlanksFilledIn()).toBe(false);

            // Fill in all blanks
            component.blankFlatIndexMap.forEach((blank, index) => {
                const ans = component.answer[blank.groupIndex][blank.partIndex];
                if (ans) {
                    ans.userMcqChoice = {
                        optionName: `test${index}`,
                        position: index,
                        used: false,
                    };
                }
            });

            expect(component.areAllMcqBlanksFilledIn()).toBe(true);
        });
    });

    describe("answer submission", () => {
        beforeEach(() => {
            mockExerciseService.question = {
                exerciseOptions: {
                    fill_in_the_blank_type: "typing",
                    text_option: "He is playing. [Škáte].",
                },
                question: "Type the word",
                FullAudioUrl: "test-audio.mp3",
            };
            mockExerciseService.exercise = {
                exercise_type: "fill_in_the_blanks",
                id: 1,
            };
            component.fillInType = "typing";
        });

        it("should submit correct answer", fakeAsync(() => {
            component["createUiResponseTextArray"]();
            component["setUpBlankModel"]();

            // Mock jQuery to return the correct answer
            const mockJQuery = jasmine.createSpy("jQuery").and.returnValue({
                val: jasmine.createSpy("val").and.returnValue("Škáte"),
            });
            (window as any).jQuery = mockJQuery;

            spyOn(component, "submitAnswer");

            component.submitButtonClicked();

            expect(component.answerSubmitted).toBe(true);
            expect(component.submitAnswer).toHaveBeenCalledWith(AnswerType.CORRECT);
        }));

        it("should submit incorrect answer", fakeAsync(() => {
            component["createUiResponseTextArray"]();
            component["setUpBlankModel"]();

            // Mock jQuery to return wrong answer
            const mockJQuery = jasmine.createSpy("jQuery").and.returnValue({
                val: jasmine.createSpy("val").and.returnValue("wrong"),
            });
            (window as any).jQuery = mockJQuery;

            spyOn(component, "submitAnswer");

            component.submitButtonClicked();

            expect(component.submitAnswer).toHaveBeenCalledWith(AnswerType.INCORRECT);
        }));

        it("should build answer parameters correctly", () => {
            mockExerciseService.question = {
                id: 123,
                exerciseOptions: { id: 456 },
                PromptType: "card",
            };
            mockExerciseService.exercise = {
                id: 789,
                card_type: "regular",
            };

            const params = component["buildAnswerParams"](AnswerType.CORRECT);

            expect(params).toEqual({
                level_id: 1,
                unit_id: 1,
                card_id: 123,
                activity_type: "exercise",
                user_id: 1,
                answar_type: "right",
                popup_status: true,
                experiencecard_ids: "123",
                exercise_id: 789,
                exercise_option_id: 456,
            });
        });
    });

    describe("keyboard navigation", () => {
        beforeEach(() => {
            mockExerciseService.question = {
                exerciseOptions: {
                    fill_in_the_blank_type: "typing",
                    text_option: "[First] and [Second] blanks.",
                },
                question: "Fill in the blanks",
            };
            component.fillInType = "typing";
        });

        it("should handle virtual key press", () => {
            spyOn(component, "submitButtonClicked");

            component.virtualKeyPressed("{enter}");

            expect(component.submitButtonClicked).toHaveBeenCalled();
        });

        it("should handle physical key press", () => {
            spyOn(component, "submitButtonClicked");

            component.physicalKeyPressed("Enter");

            expect(component.submitButtonClicked).toHaveBeenCalled();
        });

        it("should set active blank index correctly", () => {
            component["createUiResponseTextArray"]();
            component["setUpBlankModel"]();

            component.setActiveBlankIndex(0, 0);

            expect(component.activeBlankIndex).toBe(0);
        });
    });

    describe("device detection", () => {
        it("should detect mobile device", () => {
            mockDeviceDetectorService.isMobile.and.returnValue(true);
            mockDeviceDetectorService.isTablet.and.returnValue(false);

            component["getDeviceInfo"]();

            expect(component.isMobileOrTablet).toBe(true);
        });

        it("should detect tablet device", () => {
            mockDeviceDetectorService.isMobile.and.returnValue(false);
            mockDeviceDetectorService.isTablet.and.returnValue(true);

            component["getDeviceInfo"]();

            expect(component.isMobileOrTablet).toBe(true);
        });

        it("should detect desktop device", () => {
            mockDeviceDetectorService.isMobile.and.returnValue(false);
            mockDeviceDetectorService.isTablet.and.returnValue(false);

            component["getDeviceInfo"]();

            expect(component.isMobileOrTablet).toBe(false);
        });
    });

    describe("session types", () => {
        it("should set service for exercise session", () => {
            component.sessionType = "exercise";

            component.setService();

            expect(component["specifiedService"]).toBe(mockLessonsService);
        });

        it("should set service for review session", () => {
            component.sessionType = "review";

            component.setService();

            expect(component["specifiedService"]).toBe(mockReviewService);
        });

        it("should throw error for invalid session type", () => {
            component.sessionType = "invalid";

            expect(() => component.setService()).toThrowError("Unhandled session type: invalid");
        });
    });

    describe("cleanup", () => {
        it("should unsubscribe on destroy", () => {
            spyOn(component, "turnOffKeyboardListeners");
            spyOn(component["subscriptions"], "unsubscribe");

            component.ngOnDestroy();

            expect(component.turnOffKeyboardListeners).toHaveBeenCalled();
            expect(component["subscriptions"].unsubscribe).toHaveBeenCalled();
        });
    });
});
