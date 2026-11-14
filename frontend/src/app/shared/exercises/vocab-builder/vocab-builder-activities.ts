import { Type } from "@angular/core";
import {
    ActivityType,
    BirdActivityComponent,
    ColoringActivityComponent,
    FillLetterActivityComponent,
    MatchSoundActivityComponent,
    MemoryActivityComponent,
    SelectGridActivityComponent,
    SliderActivityComponent,
    WordClustersActivityComponent,
    WordListActivityComponent,
} from "@languageconservancy/vocab-builder-library";

export type VocabBuilderActivity =
    | BirdActivityComponent
    | ColoringActivityComponent
    | FillLetterActivityComponent
    | MatchSoundActivityComponent
    | MemoryActivityComponent
    | SelectGridActivityComponent
    | SliderActivityComponent
    | WordClustersActivityComponent
    | WordListActivityComponent;

export const VOCAB_BUILDER_ACTIVITIES: Record<
    ActivityType,
    { component: Type<VocabBuilderActivity>; guided: boolean }
> = {
    "bird-activity": { component: BirdActivityComponent, guided: true },
    coloring: { component: ColoringActivityComponent, guided: true },
    "fill-letter": { component: FillLetterActivityComponent, guided: true },
    "match-sound": { component: MatchSoundActivityComponent, guided: true },
    "memory-activity": { component: MemoryActivityComponent, guided: true },
    "select-grid": { component: SelectGridActivityComponent, guided: true },
    "slider-activity": { component: SliderActivityComponent, guided: true },
    "word-clusters-activity": { component: WordClustersActivityComponent, guided: true },
    "word-list": { component: WordListActivityComponent, guided: false },
};
