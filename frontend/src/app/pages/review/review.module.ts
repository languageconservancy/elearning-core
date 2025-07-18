import { NgModule } from "@angular/core";
import { CommonModule } from "@angular/common";
import { FormsModule } from "@angular/forms";
import { DragDropModule } from "@angular/cdk/drag-drop";
import { BrowserAnimationsModule } from "@angular/platform-browser/animations";
import { RouterModule } from "@angular/router";

import { ReviewCardsComponent } from "./review-cards/review-cards.component";
import { ReviewExerciseComponent } from "./review-exercise/review-exercise.component";
import { ReviewRewardPopupComponent } from "./_partials/review-reward-popup/review-reward-popup.component";
import { ReviewTimerComponent } from "./_partials/review-timer/review-timer.component";
import { ExercisesModule } from "app/shared/exercises/exercises.module";
import { PartialsModule } from "app/_partials/partials.module";
import { ReviewNextPopupComponent } from "./_partials/review-next-popup/review-next-popup.component";
import { PipesModule } from "app/_pipes/pipes.module";
import { KeyboardModule } from "app/shared/keyboard/keyboard.module";

@NgModule({
    imports: [
        CommonModule,
        FormsModule,
        DragDropModule,
        PartialsModule,
        BrowserAnimationsModule,
        PipesModule,
        ExercisesModule,
        RouterModule,
        KeyboardModule.forRoot(),
    ],
    declarations: [
        ReviewCardsComponent,
        ReviewExerciseComponent,
        ReviewRewardPopupComponent,
        ReviewTimerComponent,
        ReviewNextPopupComponent,
    ],
})
export class ReviewModule {}
