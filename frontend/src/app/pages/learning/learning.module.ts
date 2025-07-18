import { NgModule } from "@angular/core";
import { CommonModule } from "@angular/common";
import { RouterModule } from "@angular/router";
import { FormsModule } from "@angular/forms";
import { DragDropModule } from "@angular/cdk/drag-drop";
import { BrowserAnimationsModule } from "@angular/platform-browser/animations";

// Components to declare
import { UnitComponent } from "./unit/unit.component";
// import { CardsComponent } from './cards/cards.component';
import { ExerciseComponent } from "./card-details/exercises/exercise/exercise.component";
import { LessonsComponent } from "./card-details/lessons/lessons.component";
import { RewardPopupsComponent } from "./_partials/reward-popups/reward-popups.component";
import { SingleCardComponent } from "./card-details/single-card/single-card.component";
// Modules to import
import { ExercisesModule } from "app/shared/exercises/exercises.module";
import { KeyboardModule } from "app/shared/keyboard/keyboard.module";
import { PartialsModule } from "app/_partials/partials.module";
import { PipesModule } from "app/_pipes/pipes.module";

@NgModule({
    imports: [
        CommonModule,
        RouterModule,
        FormsModule,
        DragDropModule,
        BrowserAnimationsModule,
        PartialsModule,
        PipesModule,
        ExercisesModule,
        KeyboardModule.forRoot(),
    ],
    declarations: [
        UnitComponent,
        // CardsComponent,
        ExerciseComponent,
        LessonsComponent,
        SingleCardComponent,
        RewardPopupsComponent,
    ],
})
export class LearningModule {}
