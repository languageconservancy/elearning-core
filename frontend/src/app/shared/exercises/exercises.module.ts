import { NgModule } from "@angular/core";
import { CommonModule } from "@angular/common";
import { FormsModule } from "@angular/forms";
import { DragDropModule } from "@angular/cdk/drag-drop";

import { PipesModule } from "app/_pipes/pipes.module";
import { PartialsModule } from "app/_partials/partials.module";
import { McqComponent } from "./mcq/mcq.component";
import { MatchPairComponent } from "./match-pair/match-pair.component";
import { TrueFalseComponent } from "./true-false/true-false.component";
import { FillinComponent } from "./fillin/fillin.component";
import { AnagramComponent } from "./anagram/anagram.component";
import { RecordingComponent } from "./recording/recording.component";
import { SelectableCardComponent } from "../selectable-card/selectable-card.component";
import { NonSelectableCardComponent } from "../non-selectable-card/non-selectable-card.component";
import { DirectivesModule } from "app/_directives/directives.module";

@NgModule({
    // External modules needed by this module
    imports: [
        CommonModule,
        PipesModule,
        FormsModule,
        DragDropModule,
        PartialsModule,
        SelectableCardComponent,
        NonSelectableCardComponent,
        DirectivesModule,
    ],
    // Components in this module
    declarations: [
        McqComponent,
        MatchPairComponent,
        TrueFalseComponent,
        FillinComponent,
        AnagramComponent,
        RecordingComponent,
    ],
    // Components of this module used outside this module
    exports: [
        McqComponent,
        MatchPairComponent,
        TrueFalseComponent,
        FillinComponent,
        AnagramComponent,
        RecordingComponent,
    ],
})
export class ExercisesModule {}
