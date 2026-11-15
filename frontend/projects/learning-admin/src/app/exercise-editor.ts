import {
    ChangeDetectionStrategy,
    Component,
    computed,
    effect,
    inject,
    input,
    output,
    signal,
    untracked,
} from "@angular/core";
import { ContentAsset } from "./content-asset";
import { FormsModule } from "@angular/forms";
import { ContextPanel } from "./context-panel";
import { ContentOption, ExerciseData, ExerciseRule, FieldValue } from "./models";
import { errorMessage, WorkspaceApi } from "./workspace-api";

const sources = [
    { value: "card", label: "Individual cards", hint: "Pick specific cards for each item." },
    { value: "card_group", label: "Card groups", hint: "Draw a set number of cards per round from groups." },
    { value: "custom", label: "Custom text and media", hint: "Write text or attach media directly." },
];

@Component({
    selector: "studio-exercise-editor",
    imports: [ContentAsset, ContextPanel, FormsModule],
    templateUrl: "./exercise-editor.html",
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ExerciseEditor {
    readonly id = input.required<number>();
    readonly saved = output<void>();
    readonly dirtyChange = output<boolean>();
    readonly data = signal<ExerciseData | null>(null);
    readonly dirty = signal(false);
    readonly loading = signal(false);
    readonly saving = signal(false);
    readonly error = signal("");
    readonly notice = signal("");
    readonly tab = signal<"Setup" | "Content" | "Display">("Setup");
    readonly steps = ["Setup", "Content", "Display"] as const;
    readonly selected = signal(0);
    readonly confirmRemove = signal(false);
    readonly confirmReload = signal(false);
    readonly option = computed(() => this.data()?.options[this.selected()] ?? null);
    readonly kind = computed(() => String(this.data()?.fields["exercise_type"] ?? ""));
    readonly source = computed(() => String(this.data()?.fields["card_type"] ?? "card"));
    readonly allSources = sources;
    readonly sides = ["prompt", "response"] as const;
    readonly paired = computed(
        () =>
            this.source() !== "card_group" &&
            (["match-the-pair", "truefalse", "recording"].includes(this.kind()) ||
                (this.kind() === "multiple-choice" && this.source() === "custom")),
    );
    readonly modalities = computed(() => Object.entries(this.data()?.modalities ?? {}));
    readonly exerciseTypes = computed(() => Object.entries(this.data()?.types ?? {}));
    readonly activities = computed(() => Object.entries(this.data()?.activities ?? {}));
    readonly vocabularyModes = computed(() =>
        Object.entries(this.data()?.activities[String(this.field("vbl_activity"))]?.modes ?? {}),
    );
    readonly rule = computed<ExerciseRule | null>(
        () => this.data()?.rules?.[this.kind()]?.[this.source()] ?? null,
    );
    readonly roles = computed(() => this.rule()?.roles ?? []);
    readonly counts = computed(() => {
        const counts: Record<string, number> = {};
        for (const option of this.data()?.options ?? []) {
            const role = String(option.fields["card_type"] ?? "");
            counts[role] = (counts[role] ?? 0) + 1;
        }
        return counts;
    });
    /** Items that this setup would never show, or that have no content at all. */
    readonly unused = computed(() =>
        (this.data()?.options ?? [])
            .map((option, index) => ({ option, index }))
            .filter(({ option }) => !this.roleFits(option) || this.incomplete(option))
            .map(({ index }) => index),
    );
    readonly setupProblems = computed(() => {
        const problems: string[] = [];
        if (!String(this.field("name") ?? "").trim()) problems.push("Name the exercise.");
        if (!this.rule()) problems.push("Choose an exercise type and content source.");
        else if (!this.pairAllowed(this.modality("prompt"), this.modality("response")))
            problems.push("Choose a valid prompt and response.");
        if (this.rule()?.roundSize === "required" && !(Number(this.field("noofcard")) >= 1))
            problems.push("Set how many cards to use per round.");
        if (
            this.kind() === "fill_in_the_blanks" &&
            this.source() === "card" &&
            !this.codes("responsetype").includes("l")
        )
            problems.push("Fill-in-the-blank responses must show the language text (Display step).");
        return problems;
    });
    readonly requirements = computed(() =>
        this.roles().map((role) => {
            const count = this.counts()[role.value] ?? 0;
            return {
                ...role,
                count,
                ok: count >= role.min && (role.max === null || count <= role.max),
                full: role.max !== null && count >= role.max,
                range:
                    role.max === null
                        ? `${role.min}+`
                        : role.min === role.max
                          ? `${role.min}`
                          : `${role.min}–${role.max}`,
            };
        }),
    );
    readonly contentProblems = computed(() => {
        const problems = this.requirements()
            .filter((role) => !role.ok)
            .map((role) =>
                role.count < role.min
                    ? `Add ${role.min - role.count} more ${role.label.toLowerCase()}.`
                    : `Remove ${role.count - role.max!} ${role.label.toLowerCase()}.`,
            );
        if (this.unused().length)
            problems.push(`${this.unused().length} item(s) are empty or not used by this setup.`);
        const sentence = this.data()?.options.find((o) => o.fields["card_type"] === "P");
        if (
            this.kind() === "fill_in_the_blanks" &&
            sentence &&
            !/\[[^\]]+\]/.test(String(sentence.fields["text_option"] ?? ""))
        )
            problems.push("Mark at least one blank in the sentence with [brackets].");
        return problems;
    });
    private readonly customMode = signal<Record<string, boolean>>({});
    private readonly reload = signal(0);
    private readonly api = inject(WorkspaceApi);

    constructor() {
        effect((cleanup) => {
            const id = this.id();
            this.reload();
            this.data.set(null);
            untracked(() => this.markDirty(false));
            this.loading.set(true);
            this.error.set("");
            const subscription = this.api.exerciseContent(id).subscribe({
                next: (data) => {
                    this.data.set(data);
                    this.loading.set(false);
                    this.selected.set(0);
                    this.tab.set(
                        data.options.length && !this.setupProblems().length ? "Content" : "Setup",
                    );
                    this.customMode.set({});
                    this.markDirty(false);
                },
                error: (error: unknown) => {
                    this.error.set(errorMessage(error));
                    this.loading.set(false);
                },
            });
            cleanup(() => subscription.unsubscribe());
        });
    }
    text(event: Event) {
        return (event.target as HTMLInputElement).value;
    }
    checked(event: Event) {
        return (event.target as HTMLInputElement).checked;
    }
    value(field: string): FieldValue {
        return this.option()?.fields[field] ?? null;
    }
    customValue(field: string): FieldValue {
        return this.option()?.custom[field] ?? null;
    }
    field(field: string): FieldValue {
        return this.data()?.fields[field] ?? null;
    }
    setField(field: string, value: FieldValue) {
        this.data.update((data) =>
            data ? { ...data, fields: { ...data.fields, [field]: value } } : data,
        );
        this.markDirty(true);
    }
    setOption(field: string, value: FieldValue, custom = false) {
        this.data.update((data) =>
            data
                ? {
                      ...data,
                      options: data.options.map((option, index) =>
                          index === this.selected()
                              ? {
                                    ...option,
                                    [custom ? "custom" : "fields"]: {
                                        ...option[custom ? "custom" : "fields"],
                                        [field]: value,
                                    },
                                }
                              : option,
                      ),
                  }
                : data,
        );
        this.markDirty(true);
    }
    title(option: ContentOption, index: number): string {
        const card =
            this.data()?.cards[
                Number(option.fields["card_id"] ?? option.fields["responce_card_id"])
            ];
        const role =
            this.roles().find((role) => role.value === option.fields["card_type"])?.label ??
            "Content";
        const label = `${index + 1}. ${role}${card ? " · " + card.title : option.fields["text_option"] ? " · " + option.fields["text_option"] : ""}`;
        return this.incomplete(option) ? `${label} · Incomplete` : label;
    }
    incomplete(option: ContentOption): boolean {
        const filled = (value: unknown) =>
            value !== null && value !== undefined && String(value).trim() !== "" && value !== 0;
        return ![
            option.fields["card_id"],
            option.fields["responce_card_id"],
            option.fields["group_id"],
            option.fields["text_option"],
            option.fields["vbl_letter"],
            ...Object.values(option.custom ?? {}),
        ].some(filled);
    }
    add(role: string) {
        const options = this.data()!.options;
        const fields: Record<string, FieldValue> = {
            type: this.source() === "card_group" && this.groupOnly() ? "group" : "card",
            card_type: role,
            card_id: null,
            responce_card_id: null,
            group_id: null,
            prompt_preview_option: null,
            responce_preview_option: null,
        };
        if (this.kind() === "fill_in_the_blanks") {
            fields["fill_in_the_blank_type"] = "typing";
            fields["text_option"] = "";
            fields["option_position"] =
                role === "P"
                    ? null
                    : options.filter((o) => o.fields["card_type"] === "O").length + 1;
        }
        if (["truefalse", "recording"].includes(this.kind())) fields["response_true_false"] = "N";
        this.data.update((data) =>
            data ? { ...data, options: [...data.options, { fields, custom: {} }] } : data,
        );
        this.selected.set(options.length);
        this.markDirty(true);
    }
    /** Roles whose limit is reached cannot be chosen for another item. */
    roleChoices() {
        const current = this.value("card_type");
        return this.requirements().filter((role) => !role.full || role.value === current);
    }
    /** Group-based multiple choice and matching always draw from whole groups. */
    groupOnly() {
        return ["multiple-choice", "match-the-pair"].includes(this.kind());
    }
    roleFits(option: ContentOption) {
        return this.roles().some((role) => role.value === option.fields["card_type"]);
    }
    removeUnused() {
        const unused = new Set(this.unused());
        this.data.update((data) =>
            data ? { ...data, options: data.options.filter((_, i) => !unused.has(i)) } : data,
        );
        this.customMode.set({});
        this.selected.set(0);
        this.markDirty(true);
    }
    sourceLabel(source: string) {
        return sources.find((item) => item.value === source)?.label ?? source;
    }
    modalityAvailable(side: "prompt" | "response", code: string) {
        if (side === "response") return this.pairAllowed(this.modality("prompt"), code);
        return this.modalities().some(([response]) => this.pairAllowed(code, response));
    }
    sourceAllowed(source: string, type = this.kind()) {
        return !!this.data()?.rules?.[type]?.[source];
    }
    pairAllowed(prompt: string, response: string, type = this.kind(), source = this.source()) {
        return !!this.data()?.rules?.[type]?.[source]?.pairs.includes(`${prompt}-${response}`);
    }
    /** Changing the exercise type or source keeps the setup valid by moving to the closest allowed choice. */
    setSetup(type: string, source: string) {
        const rules = this.data()?.rules?.[type];
        if (!rules) return;
        if (!rules[source]) source = (this.data()!.sources[type] ?? Object.keys(rules))[0];
        const pairs = rules[source].pairs;
        const prompt = this.modality("prompt"),
            response = this.modality("response");
        const pair =
            [`${prompt}-${response}`]
                .concat(pairs.filter((p) => p.startsWith(prompt + "-")))
                .concat(pairs.filter((p) => p.endsWith("-" + response)))
                .concat(pairs)
                .find((p) => pairs.includes(p)) ?? "";
        this.data.update((data) =>
            data ? { ...data, fields: { ...data.fields, exercise_type: type, card_type: source } } : data,
        );
        if (pair) this.applyPair(pair);
        this.markDirty(true);
    }
    pickModality(side: "prompt" | "response", value: string) {
        const prompt = side === "prompt" ? value : this.modality("prompt");
        let response = side === "response" ? value : this.modality("response");
        if (!this.pairAllowed(prompt, response))
            response = this.modalities().find(([code]) => this.pairAllowed(prompt, code))?.[0] ?? "";
        if (!response) return;
        this.applyPair(`${prompt}-${response}`);
        this.markDirty(true);
    }
    pairReason(prompt: string, response: string) {
        if (response === "r") return "Recording responses are only for recording exercises.";
        if (this.kind() === "recording") return "Recording exercises need a Recording response.";
        if (prompt === response) return "Not available with the same prompt type for this setup.";
        return "Not available for this setup.";
    }
    private applyPair(pair: string) {
        const [prompt, response] = pair.split("-");
        const keep = (field: string, code: string) => {
            const codes = this.codes(field).filter(Boolean);
            return codes.includes(code) ? codes.join(", ") : [code, ...codes].join(", ");
        };
        this.data.update((data) =>
            data
                ? {
                      ...data,
                      fields: {
                          ...data.fields,
                          promteresponsetype: pair,
                          promotetype: keep("promotetype", prompt),
                          responsetype: keep("responsetype", response),
                      },
                  }
                : data,
        );
    }
    remove() {
        const index = this.selected();
        this.data.update((data) =>
            data ? { ...data, options: data.options.filter((_, i) => i !== index) } : data,
        );
        this.customMode.set({});
        this.selected.set(Math.max(0, index - 1));
        this.confirmRemove.set(false);
        this.markDirty(true);
    }
    isCustom(side: string): boolean {
        const override = this.customMode()[`${this.selected()}:${side}`];
        if (override !== undefined) return override;
        return (
            this.source() === "custom" &&
            !this.value(side === "prompt" ? "card_id" : "responce_card_id")
        );
    }
    changeSide(side: string, custom: boolean) {
        this.customMode.update((modes) => ({ ...modes, [`${this.selected()}:${side}`]: custom }));
        if (custom) this.setOption(side === "prompt" ? "card_id" : "responce_card_id", null);
        else
            for (const suffix of ["html", "audio_id", "image_id"])
                this.setOption(side + "_" + suffix, null, true);
    }
    modality(side: string) {
        return (
            String(this.field("promteresponsetype") ?? "a-l").split("-")[
                side === "prompt" ? 0 : 1
            ] ?? ""
        );
    }
    codes(field: string, option = false) {
        return String(option ? (this.value(field) ?? "") : (this.field(field) ?? ""))
            .split(",")
            .map((v) => v.trim());
    }
    toggleCode(field: string, code: string, option = false) {
        const current = this.codes(field, option).filter(Boolean);
        const next = current.includes(code)
            ? current.filter((v) => v !== code)
            : [...current, code];
        if (option) this.setOption(field, next.length ? next.join(", ") : null);
        else this.setField(field, next.join(", "));
    }
    reloadData() {
        this.confirmReload.set(false);
        this.reload.update((n) => n + 1);
    }
    private markDirty(dirty: boolean) {
        this.dirty.set(dirty);
        this.dirtyChange.emit(dirty);
        if (dirty) this.notice.set("");
    }
    save() {
        if (!this.data() || this.saving()) return;
        this.saving.set(true);
        this.error.set("");
        this.notice.set("");
        this.api.saveExerciseContent(this.id(), this.data()!).subscribe({
            next: (data) => {
                this.data.set(data);
                this.saving.set(false);
                this.markDirty(false);
                this.notice.set("Exercise content saved.");
                this.saved.emit();
            },
            error: (error: unknown) => {
                this.saving.set(false);
                this.error.set(errorMessage(error));
            },
        });
    }
}
