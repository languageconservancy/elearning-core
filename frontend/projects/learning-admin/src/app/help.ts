import { ChangeDetectionStrategy, Component, computed, input, signal } from "@angular/core";
import { ExerciseRule, FieldValue, RecordType } from "./models";

const storageKey = "learning-studio-help-collapsed";

function collapsedTopics(): string[] {
    try {
        const value: unknown = JSON.parse(localStorage.getItem(storageKey) ?? "[]");
        return Array.isArray(value) ? value.filter((item) => typeof item === "string") : [];
    } catch {
        return [];
    }
}

/** Collapsible guidance panel; open by default, and remembers when an author collapses a topic. */
@Component({
    selector: "studio-help",
    changeDetection: ChangeDetectionStrategy.OnPush,
    template: `
        <details class="help-panel" [open]="open()" (toggle)="toggled($event)">
            <summary>
                <span class="help-icon" aria-hidden="true">?</span>
                <span>{{ heading() }}</span>
                <span class="help-toggle">{{ open() ? "Hide help" : "Show help" }}</span>
            </summary>
            <div class="help-body">
                @for (line of lines(); track line) {
                    <p>{{ line }}</p>
                }
                <ng-content />
            </div>
        </details>
    `,
})
export class StudioHelp {
    /** Identifies the panel for remembering its collapsed state. */
    readonly topic = input.required<string>();
    readonly heading = input("How this works");
    readonly lines = input<string[]>([]);
    private readonly collapsed = signal(collapsedTopics());
    readonly open = computed(() => !this.collapsed().includes(this.topic()));

    toggled(event: Event) {
        const open = (event.target as HTMLDetailsElement).open;
        if (open === this.open()) return;
        const topics = this.collapsed().filter((topic) => topic !== this.topic());
        if (!open) topics.push(this.topic());
        this.collapsed.set(topics);
        try {
            localStorage.setItem(storageKey, JSON.stringify(topics));
        } catch {
            // Storage can be unavailable (private mode); the panel still toggles for this session.
        }
    }
}

type HelpTab = "Overview" | "Relationships" | "Media";

const shared = {
    Relationships:
        "Shows every record this one is linked to, with counts. Open a related record to edit it in the side drawer without leaving this one.",
    Media:
        "Attach media from the site library, the shared image archive, or upload or record new audio and video. Replacing or clearing media only changes this record, not the file.",
};

/** Help for the record editor tabs, by record type. */
export const RECORD_HELP: Record<RecordType, Partial<Record<HelpTab, string[]>>> = {
    paths: {
        Overview: [
            "A learning path is the top-level course a learner follows. It contains levels, which contain units of lessons and exercises.",
            "The label and description identify the path to learners. The image is the path's picture.",
            "Add or reorder levels from the browser: select the path, then use + Level or drag levels into order.",
        ],
        Relationships: [shared.Relationships],
        Media: [shared.Media, "Paths use a single image."],
    },
    levels: {
        Overview: [
            "A level groups units in a fixed order within a path. The same level can be shared by more than one path, so edits here show everywhere it is used.",
            "Add units with + Unit in the browser, and drag units to change their order.",
        ],
        Relationships: [shared.Relationships, "Check the Learning paths count before renaming: a shared level changes in every path."],
        Media: [shared.Media, "The level image identifies the level in the app."],
    },
    units: {
        Overview: [
            "A unit is an ordered sequence of lessons and exercises that learners work through in turn.",
            "Use + Add in the browser to create a new lesson or exercise in this unit or to link an existing one. Drag items to change the order.",
            "Removing an item from a unit keeps the lesson or exercise itself. Use Copy to duplicate a unit with its children.",
        ],
        Relationships: [shared.Relationships],
    },
    lessons: {
        Overview: [
            "Lessons teach before learners practise. The name identifies the lesson in the unit; build its slides on the Content tab.",
        ],
        Relationships: [shared.Relationships, "A lesson can be linked into more than one unit; content edits apply everywhere it is used."],
    },
    exercises: {
        Overview: [
            "The name identifies the exercise in its unit. Its type, content and display options are edited on the Content tab.",
        ],
        Relationships: [shared.Relationships, "An exercise can be linked into more than one unit; content edits apply everywhere it is used."],
    },
    cards: {
        Overview: [
            "A card is one word, verb or pattern with its translation and media. Cards are reused by exercises, lessons and card groups, so edits show everywhere the card is used.",
            "Language text and English may use rich text tags: <b>bold</b>, <i>italic</i>, <u>underline</u>, <color=\"red\">…</color> or <color=#00ff00>…</color>, and <size=18>…</size>.",
            "Alternate language text and Alternate English hold other spellings or translations and are included in searches.",
            "Card type is Word, Verb or Pattern. Leave Gender as default unless the word is gender-specific.",
        ],
        Relationships: [shared.Relationships, "Check usage before deleting a card: exercises and lessons that use it lose that content."],
        Media: [
            shared.Media,
            "The image and pronunciation audio are used when an exercise shows the card's Image or Audio. Lesson blocks play the card audio automatically when Audio is shown.",
        ],
    },
    groups: {
        Overview: [
            "A card group is a pool of cards that card-group exercises draw from at random, a set number per round.",
            "The group type (listening, reading, writing or speaking) categorises the group. Add and remove cards on the Relationships tab.",
        ],
        Relationships: [
            shared.Relationships,
            "Removing a card only removes it from this group; the card itself is kept.",
        ],
    },
};

/** What the learner experiences for each exercise type. */
export const EXERCISE_TYPE_HELP: Record<string, string> = {
    "multiple-choice":
        "Learners see a prompt and pick the matching answer from up to four choices.",
    "match-the-pair":
        "Learners match up to six pairs: each card's prompt side is matched with its response side.",
    truefalse: "Learners decide whether the response shown belongs with the prompt.",
    anagram: "Learners rebuild a word from its scrambled letters.",
    fill_in_the_blanks:
        "Learners fill the blanks in a sentence by typing or by choosing from answer choices.",
    recording:
        "Learners see or hear a prompt and record themselves saying it.",
    "vocab-builder":
        "A vocabulary-builder game played with word cards or letters. The activity sets the game and what content it needs.",
};

const roundNote =
    "Each round asks up to Cards per round cards the learner has not answered yet. Once every card has been answered, the cards they got wrong come back.";

/** How the selected content source works for an exercise type. */
export function sourceHelp(type: string, source: string, rule: ExerciseRule | null): string[] {
    const lines: string[] = [];
    const key = type + "/" + source;
    const text: Record<string, string> = {
        "multiple-choice/card":
            "Add one Prompt card and one Correct answer card. Distractors are the wrong choices: up to three are picked at random each time, alongside the correct answer.",
        "multiple-choice/card_group":
            "Prompt groups supply the questions; Answer groups supply the choices. Each question shows its correct card plus up to three random wrong cards.",
        "multiple-choice/custom":
            "The Prompt item holds both the question and its correct answer (a card or custom text and media). Answer options are the wrong choices, up to three.",
        "match-the-pair/card":
            "Add one Pair item per card (up to six). The prompt and response types decide which two sides of the card are matched, for example Audio to Image.",
        "match-the-pair/card_group":
            "Pairs are drawn from the Pair groups, up to six per round.",
        "match-the-pair/custom":
            "Add up to six pairs. Each side can be a card or custom text and media.",
        "truefalse/card":
            "Add one Statement with a prompt card and a response card, then set Correct result to True if they match or False if they don't.",
        "truefalse/card_group":
            "Each card is shown either with its own response (true) or with another card from the groups (false), at random. The correct result is set automatically.",
        "truefalse/custom":
            "Add one Statement with a prompt and response (card or custom text and media), then set whether it is true.",
        "anagram/card":
            "Add one Word card. Its language text is scrambled for the learner to rebuild.",
        "anagram/card_group": "Words are drawn from the groups and scrambled.",
        "fill_in_the_blanks/card":
            "Add one Sentence and type it in its text box, marking each blank with [brackets], for example: The [dog] runs. The Display tab's responses must include Language text.",
        "fill_in_the_blanks/custom":
            "Add one Sentence (card or custom text) and mark each blank with [brackets]. Custom sentences are always answered by typing.",
        "recording/card": "Add one Prompt card. Learners record themselves saying it.",
        "recording/card_group": "Cards are drawn from the groups for the learner to record.",
        "recording/custom":
            "Add one Prompt with custom text or media and a response card whose audio is the model pronunciation.",
        "vocab-builder/card": "Choose the activity and mode, then add the target and the other responses it needs.",
    };
    if (text[key]) lines.push(text[key]);
    if (type === "fill_in_the_blanks") {
        lines.push(
            "Answer choices give each blank's answer and its blank position (1 for the first blank, 2 for the second). Use position 0 for extra wrong choices. With Multiple choice the choices are shuffled.",
        );
    }
    if (rule?.roundSize === "required") lines.push(roundNote);
    if (rule?.roundSize === "derived") lines.push("Pairs per round is set automatically from the number of pairs.");
    return lines;
}

/** Help for the role of the selected content item. */
export function roleHelp(type: string, source: string, role: FieldValue): string {
    const key = type + "/" + source + "/" + role;
    const text: Record<string, string> = {
        "multiple-choice/card/P": "The question the learner sees.",
        "multiple-choice/card/R": "The correct answer. It is always one of the choices.",
        "multiple-choice/card/O": "A wrong choice. Three distractors at most are shown at a time.",
        "multiple-choice/card_group/P": "Each card in this group can be asked as a question.",
        "multiple-choice/card_group/O": "Cards in this group are offered as choices.",
        "multiple-choice/custom/P": "Set the question on the prompt side and the correct answer on the response side.",
        "multiple-choice/custom/O": "A wrong choice shown alongside the correct answer.",
        "fill_in_the_blanks/card/P": "Type the sentence and put each blank in [brackets].",
        "fill_in_the_blanks/custom/P": "Type the sentence and put each blank in [brackets].",
        "fill_in_the_blanks/card/O": "One answer choice. Set the blank position it fills, or 0 for a wrong choice.",
        "fill_in_the_blanks/custom/O": "One answer choice. Set the blank position it fills, or 0 for a wrong choice.",
        "vocab-builder/card/P": "The target response the activity is built around.",
        "vocab-builder/card/O": "Another response used alongside the target.",
    };
    return text[key] ?? "";
}

export interface VocabularyActivity {
    label: string;
    letters: number;
    words: number;
    review: boolean;
}

/** Content requirements for a vocabulary-builder activity, matching the server's checks. */
export function vocabularyHelp(
    activity: string,
    definition: VocabularyActivity | undefined,
    mode: FieldValue,
    variant: FieldValue,
): string {
    if (!definition) return "Choose a vocabulary activity to see what content it needs.";
    if (activity === "fill-letter") {
        return (
            "Choose a target word card and its missing letter, then add at least two other letters. " +
            "Only the target needs a word card. Images and pronunciation audio are optional."
        );
    }
    if (mode === "letter") {
        let help =
            "Add at least " + definition.letters + " distinct letters. " +
            (activity === "match-sound" ? "Pronunciation audio is required. " : "Pronunciation audio is optional. ") +
            "No word cards are needed.";
        if (activity === "memory-activity") help += " Four letters allow 8 cards; six allow 12; eight allow 16.";
        if (activity === "coloring") help += " The target letter needs a line-art Letter image.";
        return help;
    }
    const pictures = activity === "memory-activity" || activity === "bird-activity";
    let help =
        "Add at least " + definition.words + " distinct word card(s) with language text. " +
        (activity === "match-sound" || activity === "word-clusters-activity"
            ? "Pronunciation audio is required. "
            : activity === "slider-activity"
              ? "The target needs pronunciation audio and syllable timings. "
              : "Audio is optional. ") +
        (pictures && variant === "image"
            ? "Pictures are required. " + (activity === "memory-activity" ? "Use at least two different images. " : "")
            : "Images are optional. ") +
        "No letter attachments are needed.";
    if (activity === "coloring") help += " The target word needs a line-art Coloring image.";
    if (activity === "word-list") help += " All cards appear in the word list; Continue completes this activity.";
    if (activity === "word-clusters-activity") {
        help +=
            " Use single words of at least two characters with exact shared characters. " +
            "The library builds a connected 3–5-word puzzle and reports incompatible pools.";
    }
    if (definition.review) help += " In word mode, Include scored review adds a scored word review.";
    return help;
}

export const LESSON_HELP = [
    "A lesson is a sequence of frames (slides). Learners move through them in order; drag frames to reorder them.",
    "Each frame holds up to three blocks: a card, HTML text, an image, audio, or a video. One or two blocks sit side by side; three blocks use a two-column layout on large screens.",
    "When a frame opens, its narration plays first, then the audio of each card block that shows Audio and each audio block, in order.",
    "For card blocks, choose which parts of the card to show. Duration and Layout are stored with the frame; the web learner app lays frames out by block count.",
];

export const DISPLAY_HELP = [
    "Choose which parts of each card are shown on the prompt and on the response or choices, for example Image on the prompt and Language text on the choices.",
    "Individual content items can override these under Per-item display fields on the Content tab.",
];
