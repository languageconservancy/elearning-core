import { ComponentFixture, TestBed } from "@angular/core/testing";
import { By } from "@angular/platform-browser";
import { BehaviorSubject } from "rxjs";
import {
    AudioService as VblAudioService,
    ActivityType,
    MemoryActivityComponent,
    ColoringActivityComponent,
    ColoringService,
} from "@languageconservancy/vocab-builder-library";
import { LessonsService } from "@app/_services/lessons.service";
import { ReviewService } from "@app/_services/review.service";
import { CookieService } from "@app/_services/cookie.service";
import { LocalStorageService } from "@app/_services/local-storage.service";
import { AudioService } from "@app/_services/audio.service";
import { VocabBuilderComponent, VocabBuilderPayload } from "./vocab-builder.component";
import { VOCAB_BUILDER_ACTIVITIES } from "./vocab-builder-activities";

describe("VocabBuilderComponent", () => {
    let fixture: ComponentFixture<VocabBuilderComponent>;
    let exercise: BehaviorSubject<object>;
    let question: BehaviorSubject<object>;
    let reviewExercise: BehaviorSubject<object>;
    let lessonAnswer: jasmine.Spy;
    let reviewAnswer: jasmine.Spy;
    let imageLoad: jasmine.Spy;

    function payload(activity: ActivityType = "select-grid"): VocabBuilderPayload {
        const image =
            "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aWQAAAABJRU5ErkJggg==";
        const letterNames =
            activity === "memory-activity"
                ? ["a", "b", "c", "d", "e", "f", "g", "h"]
                : ["a", "b", "c"];
        const letters = letterNames.map((letter) => ({
            letter,
            img: image,
            mp3: "letter.mp3",
            words: [
                {
                    word: `[${letter}]word`,
                    eng: letter,
                    img: image,
                    mp3: "word.mp3",
                    coloring: image,
                },
            ],
        }));
        return {
            activity,
            content: { availableLetters: letters, selectedLetter: letters[0] },
            config: {
                playMode: "guided",
                targetLetter: letters[0],
                targetWord: letters[0].words[0],
            },
            card_ids: letters.map((_, index) => index + 1),
            target_word_index: 0,
            question: { id: 1, exerciseOptions: { id: 42 } },
        };
    }

    beforeEach(async () => {
        exercise = new BehaviorSubject<object>({});
        question = new BehaviorSubject<object>({});
        reviewExercise = new BehaviorSubject<object>({});
        lessonAnswer = jasmine.createSpy("lessonAnswer");
        reviewAnswer = jasmine.createSpy("reviewAnswer");
        await TestBed.configureTestingModule({
            imports: [VocabBuilderComponent],
            providers: [
                {
                    provide: LessonsService,
                    useValue: {
                        currentExercise: exercise,
                        currentQuestion: question,
                        answerGiven: lessonAnswer,
                    },
                },
                {
                    provide: ReviewService,
                    useValue: {
                        currentExercise: reviewExercise,
                        answerGiven: reviewAnswer,
                    },
                },
                { provide: CookieService, useValue: { get: () => Promise.resolve('{"id":7}') } },
                {
                    provide: LocalStorageService,
                    useValue: { getItem: (key: string) => (key === "LevelID" ? "4" : "5") },
                },
                {
                    provide: AudioService,
                    useValue: { pauseAndClearAudioSrc: jasmine.createSpy("pauseAudio") },
                },
            ],
        }).compileComponents();
        fixture = TestBed.createComponent(VocabBuilderComponent);
        const audio = fixture.debugElement.injector.get(VblAudioService);
        spyOn(audio, "play");
        spyOn(audio, "playWithCallback");
        spyOn(ColoringService.prototype, "saveToHistory").and.returnValue(Promise.resolve());
        imageLoad = spyOn(ColoringActivityComponent.prototype, "loadImage").and.callThrough();
    });

    async function start(sessionType: "exercise" | "review" = "exercise"): Promise<void> {
        fixture.componentRef.setInput("sessionType", sessionType);
        fixture.detectChanges();
        await fixture.whenStable();
        exercise.next({ id: 10, exercise_type: "vocab-builder", instruction: "Find the letter" });
    }

    it("registers and renders all seven library activities in the exercise space", async () => {
        await start();
        const activities: ActivityType[] = [
            "bird-activity",
            "coloring",
            "fill-letter",
            "match-sound",
            "memory-activity",
            "select-grid",
            "word-list",
        ];
        expect(Object.keys(VOCAB_BUILDER_ACTIVITIES).length).toBe(activities.length);
        for (const activity of activities) {
            const data = payload(activity);
            question.next({ vocab_builder: data });
            fixture.detectChanges();
            const rendered = fixture.debugElement.query(
                By.directive(VOCAB_BUILDER_ACTIVITIES[activity].component),
            );
            expect(rendered).withContext(activity).not.toBeNull();
            expect(rendered.componentInstance.content()).toEqual(data.content);
            if (rendered.componentInstance.setupError) {
                expect(rendered.componentInstance.setupError()).withContext(activity).toBeNull();
            }
            if (VOCAB_BUILDER_ACTIVITIES[activity].guided) {
                expect(rendered.componentInstance.config().playMode).toBe("guided");
            }
            if (activity === "memory-activity") {
                const memory: MemoryActivityComponent = rendered.componentInstance;
                for (const [size, rows] of [
                    [8, 2],
                    [12, 3],
                    [16, 4],
                ]) {
                    memory.setGridSize(size, rows);
                    expect(memory.setupError()).toBeNull();
                    expect(memory.gridCards().length).toBe(size);
                }
            }
            if (activity === "coloring") {
                const coloring: ColoringActivityComponent = rendered.componentInstance;
                expect(coloring.selectedColoringItem()?.type).toBe("word");
                await imageLoad.calls.mostRecent().returnValue;
            }
        }
    });

    it("submits exactly one lesson answer with the target option and experienced cards", async () => {
        await start();
        question.next({ vocab_builder: payload() });
        fixture.detectChanges();
        const activity = fixture.debugElement.query(
            By.directive(VOCAB_BUILDER_ACTIVITIES["select-grid"].component),
        );
        activity.componentInstance.completed.emit();
        activity.componentInstance.completed.emit();
        expect(lessonAnswer).toHaveBeenCalledTimes(1);
        expect(lessonAnswer).toHaveBeenCalledWith(
            jasmine.objectContaining({
                activity_type: "exercise",
                answar_type: "right",
                exercise_id: 10,
                exercise_option_id: 42,
                card_id: 1,
                user_id: 7,
                level_id: 4,
                unit_id: 5,
                experiencecard_ids: "1,2,3",
            }),
        );
        expect(reviewAnswer).not.toHaveBeenCalled();
    });

    it("uses the review answer pipeline for generated exercises", async () => {
        await start("review");
        reviewExercise.next({
            exercise_type: "vocab-builder",
            promotetype: "a",
            responsetype: "l",
            vocab_builder: payload(),
        });
        fixture.detectChanges();
        fixture.componentInstance.complete();
        expect(reviewAnswer).toHaveBeenCalledWith(
            jasmine.objectContaining({
                activity_type: "review",
                exercise_type: "vocab-builder",
                prompt_type: "a",
                response_type: "l",
                card_id: 1,
                answar_type: "right",
            }),
        );
        expect(lessonAnswer).not.toHaveBeenCalled();
    });

    it("requires an explicit Continue for Word List", async () => {
        await start();
        question.next({ vocab_builder: payload("word-list") });
        fixture.detectChanges();
        expect(lessonAnswer).not.toHaveBeenCalled();
        const button: HTMLButtonElement = fixture.nativeElement.querySelector("button.btn-primary");
        button.click();
        expect(lessonAnswer).toHaveBeenCalledTimes(1);
    });

    it("resets completion for the next question and rejects stale completion", async () => {
        await start();
        const first = payload();
        question.next({ vocab_builder: first });
        fixture.detectChanges();
        fixture.componentInstance.complete();
        const next = payload("memory-activity");
        question.next({ vocab_builder: next });
        fixture.detectChanges();
        fixture.componentInstance.complete(first);
        expect(lessonAnswer).toHaveBeenCalledTimes(1);
        fixture.componentInstance.complete(next);
        expect(lessonAnswer).toHaveBeenCalledTimes(2);
    });

    it("unsubscribes from question updates when destroyed", async () => {
        await start();
        const first = payload();
        question.next({ vocab_builder: first });
        fixture.detectChanges();
        fixture.destroy();
        question.next({ vocab_builder: payload("memory-activity") });
        expect(fixture.componentInstance.payload).toBe(first);
    });

    it("shows an error instead of completing an unsupported activity", async () => {
        await start();
        spyOn(console, "error");
        question.next({ vocab_builder: { ...payload(), activity: "future-activity" } });
        fixture.detectChanges();
        expect(fixture.nativeElement.querySelector('[role="alert"]').textContent).toContain(
            "unsupported",
        );
        fixture.componentInstance.complete();
        expect(lessonAnswer).not.toHaveBeenCalled();
    });

    it("does not record completion without a signed-in user", async () => {
        await start();
        spyOn(console, "error");
        question.next({ vocab_builder: payload() });
        fixture.detectChanges();
        fixture.componentInstance.userId = null;
        fixture.componentInstance.complete();
        fixture.detectChanges();
        expect(fixture.nativeElement.querySelector('[role="alert"]').textContent).toContain(
            "sign in",
        );
        expect(lessonAnswer).not.toHaveBeenCalled();
    });

    it("preserves full Coloring URLs and restores target word identity after JSON", async () => {
        await start();
        imageLoad.and.returnValue(Promise.resolve());
        const data = payload("coloring");
        const url = "https://cdn.example.test/art.line.png?version=1";
        data.content.selectedLetter.words[0].coloring = url;
        question.next(JSON.parse(JSON.stringify({ vocab_builder: data })));
        fixture.detectChanges();
        const coloring: ColoringActivityComponent = fixture.debugElement.query(
            By.directive(ColoringActivityComponent),
        ).componentInstance;
        expect(coloring.setupError()).toBeNull();
        expect(coloring.selectedColoringItem()?.type).toBe("word");
        expect(coloring.getColoringOptions().find((option) => option.type === "word")?.img).toBe(
            url,
        );
    });

    it("renders letter-only activities without requiring unrelated word cards", async () => {
        await start();
        imageLoad.and.returnValue(Promise.resolve());
        for (const activity of [
            "select-grid",
            "memory-activity",
            "match-sound",
            "coloring",
        ] as const) {
            const data = payload(activity);
            data.mode = "letter";
            data.card_ids = [];
            data.question.id = null;
            data.target_word_index = null;
            data.config.targetWord = null;
            data.content.availableLetters.forEach((letter) => letter.words.splice(0));
            question.next(JSON.parse(JSON.stringify({ vocab_builder: data })));
            fixture.detectChanges();
            const rendered = fixture.debugElement.query(
                By.directive(VOCAB_BUILDER_ACTIVITIES[activity].component),
            ).componentInstance;
            expect(fixture.componentInstance.error).withContext(activity).toBe("");
            expect(rendered.setupError()).withContext(activity).toBeNull();
            expect(rendered.config().targetWord).toBeNull();
            if (activity === "match-sound") {
                expect(rendered.currentTestType()).toBe("letter");
                expect(rendered.letterOptions().length).toBe(3);
                expect(rendered.wordOptions().length).toBe(0);
            }
            if (activity === "coloring") {
                expect(rendered.selectedColoringItem()?.type).toBe("letter");
            }
            if (activity === "memory-activity") {
                for (const [size, rows] of [
                    [8, 2],
                    [12, 3],
                    [16, 4],
                ]) {
                    rendered.setGridSize(size, rows);
                    expect(rendered.setupError()).toBeNull();
                    expect(rendered.gridCards().length).toBe(size);
                }
            }
            fixture.componentInstance.complete();
            expect(lessonAnswer.calls.mostRecent().args[0]).toEqual(
                jasmine.objectContaining({
                    card_id: null,
                    experiencecard_ids: "",
                    exercise_option_id: 42,
                }),
            );
        }
    });

    it("renders word Match Sound without changing it into a letter question", async () => {
        await start();
        const data = payload("match-sound");
        data.mode = "word";
        data.config.targetLetter = null;
        question.next(JSON.parse(JSON.stringify({ vocab_builder: data })));
        fixture.detectChanges();
        const rendered = fixture.debugElement.query(
            By.directive(VOCAB_BUILDER_ACTIVITIES["match-sound"].component),
        ).componentInstance;
        expect(rendered.setupError()).toBeNull();
        expect(rendered.currentTestType()).toBe("word");
        expect(rendered.wordOptions().length).toBe(3);
        expect(rendered.letterOptions().length).toBe(0);
        expect(rendered.config().targetLetter).toBeNull();
    });

    it("renders Fill Letter using a target word and letter-only distractors", async () => {
        await start();
        const data = payload("fill-letter");
        data.mode = "word";
        data.content.availableLetters.slice(1).forEach((letter) => letter.words.splice(0));
        question.next(JSON.parse(JSON.stringify({ vocab_builder: data })));
        fixture.detectChanges();
        const rendered = fixture.debugElement.query(
            By.directive(VOCAB_BUILDER_ACTIVITIES["fill-letter"].component),
        ).componentInstance;
        expect(rendered.setupError()).toBeNull();
        expect(rendered.letterOptions().length).toBe(3);
        expect(rendered.currentWord().word).toBe("[a]word");
    });

    it("shows a missing-content error instead of leaving the activity space blank", async () => {
        await start();
        spyOn(console, "error");
        question.next({});
        fixture.detectChanges();
        expect(fixture.nativeElement.querySelector('[role="alert"]').textContent).toContain(
            "missing its content",
        );
        fixture.componentInstance.complete();
        expect(lessonAnswer).not.toHaveBeenCalled();
    });

    it("restores a non-first Coloring target within a word response pool", async () => {
        await start();
        imageLoad.and.returnValue(Promise.resolve());
        const data = payload("coloring");
        data.mode = "word";
        data.content.selectedLetter.words.push(data.content.availableLetters[1].words[0]);
        data.target_word_index = 1;
        question.next(JSON.parse(JSON.stringify({ vocab_builder: data })));
        fixture.detectChanges();
        const rendered: ColoringActivityComponent = fixture.debugElement.query(
            By.directive(ColoringActivityComponent),
        ).componentInstance;
        expect(rendered.setupError()).toBeNull();
        expect(rendered.config().targetWord).toBe(rendered.content().selectedLetter.words[1]);
        expect(rendered.selectedColoringItem()?.data).toBe(rendered.config().targetWord);
    });

    it("sizes Coloring controls from its frame rather than the browser viewport", async () => {
        await start();
        imageLoad.and.returnValue(Promise.resolve());
        question.next({ vocab_builder: payload("coloring") });
        fixture.detectChanges();
        const host: HTMLElement = fixture.nativeElement.querySelector("app-coloring-activity");
        const frame: HTMLElement = host.parentElement;
        const coloring: HTMLElement = host.querySelector(".coloring-activity");
        const canvasArea: HTMLElement = host.querySelector(".canvas-area");
        frame.style.height = "504px";
        frame.style.transition = "none";
        host.style.transition = "none";
        for (const width of [320, 650, 900, 1100]) {
            frame.style.width = `${width}px`;
            host.style.width = `${width}px`;
            await new Promise<void>((resolve) => requestAnimationFrame(() => resolve()));
            expect(getComputedStyle(coloring).flexDirection)
                .withContext(`frame width ${width}`)
                .toBe(width >= 1024 ? "row" : "column");
            expect(canvasArea.getBoundingClientRect().width)
                .withContext(`canvas width at ${width}`)
                .toBeGreaterThan(width / 2);
            expect(canvasArea.getBoundingClientRect().height)
                .withContext(`canvas height at ${width}`)
                .toBeGreaterThan(180);
            expect(frame.scrollHeight)
                .withContext(`vertical overflow at ${width}`)
                .toBeLessThanOrEqual(frame.clientHeight + 1);
            if (width < 768) {
                for (const selector of [".toolbar", ".brush-controls", ".color-buttons"]) {
                    expect(getComputedStyle(host.querySelector(selector)).flexWrap)
                        .withContext(`${selector} wrapping at ${width}`)
                        .toBe("nowrap");
                }
            }
        }
    });
});
