export type RecordType =
    "paths" | "levels" | "units" | "lessons" | "exercises" | "cards" | "groups";
export type BrowseType = RecordType | "activities";
export type FieldValue = string | number | boolean | null;
export interface Definition {
    label: string;
    title: string;
    fields: string[];
    required: string[];
    media: Record<string, string>;
    create: boolean;
    references?: Record<string, string>;
    /** Fixed allowed values, rendered as a dropdown. */
    options?: Record<string, string[]>;
    /** Values prefilled on new records. */
    defaults?: Record<string, FieldValue>;
}
export interface Summary {
    id: number;
    type: RecordType;
    title: string;
    subtitle: string | null;
    editor: string | null;
    metadata?: string[];
    description?: string;
    previewCards?: CardPreview[];
    audioUrl?: string | null;
    /** Id of the sequenced link row when the record is listed inside an ordered parent. */
    linkId?: number;
}
export interface RecordDetail extends Summary {
    fields: Record<string, FieldValue>;
    revision: string;
    relations: { type: RecordType; label: string; count: number }[];
    media: {
        field: string;
        kind: string;
        id: number | null;
        url: string | null;
        missing: boolean;
    }[];
    created: string | null;
    modified: string | null;
}
export interface OrderScope {
    scope: "path-levels" | "level-units" | "unit-activities" | "lesson-frames";
    parentId: number;
    pathId: number | null;
}
export interface Page<T> {
    items: T[];
    total: number;
    page: number;
    pageSize: number;
    /** Present only when the complete, unfiltered list is loaded and may be reordered. */
    order?: OrderScope | null;
}
export interface ArchiveImage {
    key: string;
    name: string;
    url: string;
}
export interface MediaFile {
    id: number;
    name: string;
    url: string;
    type?: string;
}
export interface CardPreview {
    id: number;
    title: string;
    english: string;
    imageUrl: string | null;
    audioUrls: string[];
}
export interface ContentOption {
    id?: number | null;
    fields: Record<string, FieldValue>;
    custom: Record<string, FieldValue>;
}
export interface ContentBlock {
    id?: number | null;
    fields: Record<string, FieldValue>;
}
export interface ContentReferences {
    cards: Record<number, CardPreview>;
    files: Record<number, string>;
}
export interface ExerciseRole {
    value: string;
    label: string;
    min: number;
    max: number | null;
}
export interface ExerciseRule {
    roles: ExerciseRole[];
    /** Allowed "prompt-response" modality pairs, e.g. "a-l". */
    pairs: string[];
    roundSize: "required" | "derived" | null;
}
export interface ExerciseData extends ContentReferences {
    id: number;
    fields: Record<string, FieldValue>;
    options: ContentOption[];
    revision: string;
    schema: string[];
    fieldSchema: string[];
    types: Record<string, string>;
    modalities: Record<string, string>;
    sources: Record<string, string[]>;
    /** Server-defined setup rules by exercise type, then content source. */
    rules: Record<string, Record<string, ExerciseRule>>;
    activities: Record<
        string,
        {
            label: string;
            letters: number;
            words: number;
            review: boolean;
            modes: Record<string, string>;
        }
    >;
}
export interface FrameData extends ContentReferences {
    id: number;
    fields: Record<string, FieldValue>;
    blocks: ContentBlock[];
    revision: string;
}
export interface FrameSummary {
    id: number;
    name: string;
    order: number;
    blocks: number;
    duration: number;
    orientation: string;
}
export interface Bootstrap {
    token: string;
    types: Record<RecordType, Definition>;
    language: string;
    appName: string;
    legacy: string;
    userId: number;
    exerciseTypes: Record<string, string>;
}
export interface StructureResult {
    type: RecordType;
    id: number;
    linkId: number | null;
}
export interface CopyPlan {
    type: RecordType;
    id: number;
    title: string;
    name: string;
    lessons: number;
    exercises: number;
    frames: number;
    items: number;
    cards: number;
    groups: number;
}
export interface CopyOptions {
    name: string;
    activities: "link" | "copy";
    cards: "link" | "copy";
    groups: "link" | "copy";
    parent: Parent | null;
}
export interface Parent {
    type: RecordType;
    id: number;
    pathId?: number;
}
export function fieldLabel(field: string): string {
    const labels: Record<string, string> = {
        lakota: "Language text",
        alt_lakota: "Alternate language text",
        alt_english: "Alternate English",
        english: "English",
        label: "Name",
        card_type_id: "Card type",
        card_group_type_id: "Group type",
        gender: "Gender",
    };
    return labels[field] ?? field.replaceAll("_", " ").replace(/^\w/, (s) => s.toUpperCase());
}
