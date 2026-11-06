import {
    ChangeDetectionStrategy,
    ChangeDetectorRef,
    Component,
    ComponentRef,
    DestroyRef,
    inject,
    Input,
    Injector,
    OnDestroy,
    OnInit,
    ViewChild,
    ViewContainerRef,
} from "@angular/core";
import { DOCUMENT } from "@angular/common";
import { takeUntilDestroyed } from "@angular/core/rxjs-interop";
import {
    ActivityContent,
    ActivityTargetConfig,
    ActivityType,
    ColoringService,
    AudioService as VblAudioService,
    defaultAssetPaths,
    PlayModeConfig,
    vblAssetPaths,
} from "@languageconservancy/vocab-builder-library";

import { LessonsService } from "@app/_services/lessons.service";
import { ReviewService } from "@app/_services/review.service";
import { CookieService } from "@app/_services/cookie.service";
import { LocalStorageService } from "@app/_services/local-storage.service";
import { AudioService } from "@app/_services/audio.service";
import { VOCAB_BUILDER_ACTIVITIES, VocabBuilderActivity } from "./vocab-builder-activities";

export interface VocabBuilderPayload {
    activity: ActivityType;
    mode?: "word" | "letter";
    content: ActivityContent;
    config: ActivityTargetConfig & PlayModeConfig;
    card_ids: number[];
    target_word_index: number | null;
    question: { id: number | null; exerciseOptions: { id: number } };
}

interface VocabBuilderExercise {
    id?: number;
    exercise_type?: string;
    instruction?: string;
    promotetype?: string;
    responsetype?: string;
    vocab_builder?: VocabBuilderPayload;
}

@Component({
    selector: "app-vocab-builder",
    standalone: true,
    changeDetection: ChangeDetectionStrategy.OnPush,
    templateUrl: "./vocab-builder.component.html",
    styleUrls: ["./vocab-builder.component.scss"],
    providers: [
        VblAudioService,
        ColoringService,
        {
            provide: vblAssetPaths,
            useFactory: () => {
                const base = inject(DOCUMENT).baseURI;
                return {
                    ...defaultAssetPaths,
                    audioBase: "",
                    imageBase: "",
                    coloringBase: "",
                    birdGameBase: new URL("assets/vbl/bird-activity/", base).href,
                    cardBack: new URL("assets/vbl/memory-activity/card-back.png", base).href,
                    matchSoundBase: new URL("assets/vbl/match-sound-activity/", base).href,
                    coloringWorker: new URL(
                        "assets/vbl/coloring-activity/flood-fill.worker.js",
                        base,
                    ).href,
                };
            },
        },
    ],
})
export class VocabBuilderComponent implements OnInit, OnDestroy {
    private readonly lessons = inject(LessonsService);
    private readonly review = inject(ReviewService);
    private readonly cookies = inject(CookieService);
    private readonly storage = inject(LocalStorageService);
    private readonly hostAudio = inject(AudioService);
    private readonly libraryAudio = inject(VblAudioService);
    private readonly destroyRef = inject(DestroyRef);
    private readonly changeDetector = inject(ChangeDetectorRef);
    private readonly injector = inject(Injector);
    private coloringService: ColoringService | null = null;

    @Input({ required: true }) sessionType: "exercise" | "review";
    @ViewChild("activity", { read: ViewContainerRef, static: true })
    private activityContainer: ViewContainerRef;

    public exercise: VocabBuilderExercise = {};
    public payload: VocabBuilderPayload | null = null;
    public error = "";
    public submitted = false;
    public userId: number | null = null;
    private componentRef: ComponentRef<VocabBuilderActivity> | null = null;

    ngOnInit(): void {
        this.cookies
            .get("AuthUser")
            .then((value) => {
                const user: { id?: number } = JSON.parse(value);
                if (!Number.isInteger(user?.id) || user.id <= 0) {
                    throw new Error("A signed-in user is required to save activity completion.");
                }
                this.userId = user.id;
                this.changeDetector.markForCheck();
            })
            .catch((error: Error) => {
                console.error("[vocab-builder] Authentication unavailable", error);
                this.error = "Unable to save activity completion. Please sign in again.";
                this.changeDetector.markForCheck();
            });

        const service = this.sessionType === "exercise" ? this.lessons : this.review;
        service.currentExercise
            .pipe(takeUntilDestroyed(this.destroyRef))
            .subscribe((exercise: VocabBuilderExercise) => {
                this.exercise = exercise;
                if (this.sessionType === "review" && exercise.exercise_type === "vocab-builder") {
                    this.renderActivity(exercise.vocab_builder);
                }
            });
        if (this.sessionType === "exercise") {
            this.lessons.currentQuestion
                .pipe(takeUntilDestroyed(this.destroyRef))
                .subscribe((question: { vocab_builder?: VocabBuilderPayload }) => {
                    if (this.exercise.exercise_type === "vocab-builder") {
                        this.renderActivity(question.vocab_builder);
                    }
                });
        }
    }

    private renderActivity(payload: VocabBuilderPayload | undefined): void {
        this.changeDetector.markForCheck();
        this.componentRef = null;
        this.activityContainer.clear();
        this.libraryAudio.stop();
        this.hostAudio.pauseAndClearAudioSrc();
        this.payload = payload ?? null;
        this.submitted = false;
        this.error = "";
        if (!payload) {
            this.error = "This activity is missing its content. Please reload to try again.";
            console.error("[vocab-builder]", this.error);
            return;
        }
        const activity = VOCAB_BUILDER_ACTIVITIES[payload.activity];
        const mode =
            payload.mode ??
            (payload.activity === "select-grid" || payload.activity === "memory-activity"
                ? "letter"
                : "word");
        const targetWord =
            payload.target_word_index === null
                ? null
                : payload.content?.selectedLetter?.words?.[payload.target_word_index];
        if (
            !Object.prototype.hasOwnProperty.call(VOCAB_BUILDER_ACTIVITIES, payload.activity) ||
            !activity ||
            !payload.content?.selectedLetter ||
            !Array.isArray(payload.content.availableLetters) ||
            payload.content.availableLetters.some((letter) => !Array.isArray(letter.words)) ||
            !payload.question?.exerciseOptions?.id ||
            !Array.isArray(payload.card_ids) ||
            (mode === "word" &&
                (!payload.question.id || !targetWord || !payload.card_ids.length)) ||
            (mode !== "word" && mode !== "letter")
        ) {
            this.error = "This activity has unsupported or missing word content.";
            console.error("[vocab-builder]", this.error, payload);
            return;
        }
        if (payload.activity === "coloring") {
            this.coloringService = this.injector.get(ColoringService);
        }
        // The library filters letter choices by words.length, even in letter-only activities.
        const availableLetters = payload.content.availableLetters.map((letter) =>
            letter.words.length
                ? letter
                : {
                      ...letter,
                      words: [{ word: letter.letter, mp3: letter.mp3, img: letter.img }],
                  },
        );
        const selectedLetter = availableLetters.find(
            (letter) => letter.letter === payload.content.selectedLetter.letter,
        );
        if (
            !selectedLetter ||
            (mode === "word" && !selectedLetter.words[payload.target_word_index])
        ) {
            this.error = "The target response is missing from this activity's response pool.";
            console.error("[vocab-builder]", this.error, payload);
            return;
        }
        const content = { ...payload.content, availableLetters, selectedLetter };
        this.componentRef = this.activityContainer.createComponent(activity.component);
        const element: HTMLElement = this.componentRef.location.nativeElement;
        element.style.display = "block";
        element.style.height = "100%";
        element.style.width = "100%";
        this.componentRef.setInput("content", content);
        if (activity.guided) {
            this.componentRef.setInput("config", {
                ...payload.config,
                playMode: "guided",
                targetLetter:
                    mode === "letter" || payload.activity === "fill-letter" ? selectedLetter : null,
                // JSON cannot preserve the reference identity Coloring uses to select a word.
                targetWord:
                    mode === "word" ? selectedLetter.words[payload.target_word_index] : null,
            });
        }
        const instance = this.componentRef.instance;
        if ("completed" in instance) {
            instance.completed.subscribe(() => this.complete(payload));
        }
    }

    complete(payload = this.payload): void {
        if (
            !payload ||
            payload !== this.payload ||
            this.submitted ||
            this.error ||
            !this.componentRef
        ) {
            return;
        }
        if (!this.userId) {
            this.error = "Unable to save activity completion. Please sign in again.";
            this.changeDetector.markForCheck();
            console.error("[vocab-builder] Completion without a signed-in user");
            return;
        }
        this.submitted = true;
        this.changeDetector.markForCheck();
        this.libraryAudio.stop();
        const params = {
            level_id: parseInt(this.storage.getItem("LevelID"), 10) || null,
            unit_id: parseInt(this.storage.getItem("unitID"), 10) || null,
            user_id: this.userId,
            card_id: payload.question.id,
            activity_type: this.sessionType,
            answar_type: "right",
            experiencecard_ids: payload.card_ids.join(","),
            popup_status: true,
        };
        if (this.sessionType === "exercise") {
            this.lessons.answerGiven({
                ...params,
                exercise_id: this.exercise.id,
                exercise_option_id: payload.question.exerciseOptions.id,
            });
        } else {
            this.review.answerGiven({
                ...params,
                exercise_type: "vocab-builder",
                prompt_type: this.exercise.promotetype,
                response_type: this.exercise.responsetype,
            });
        }
    }

    ngOnDestroy(): void {
        this.libraryAudio.stop();
        this.coloringService?.destroy();
        this.componentRef = null;
    }
}
