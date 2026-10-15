import { Component, inject, OnDestroy } from "@angular/core";
import { Subscription } from "rxjs";

import { AnagramComponent } from "@app/shared/exercises/anagram/anagram.component";
import { FillinComponent } from "@app/shared/exercises/fillin/fillin.component";
import { MatchPairComponent } from "@app/shared/exercises/match-pair/match-pair.component";
import { McqComponent } from "@app/shared/exercises/mcq/mcq.component";
import { RecordingComponent } from "@app/shared/exercises/recording/recording.component";
import { TrueFalseComponent } from "@app/shared/exercises/true-false/true-false.component";
import { LessonsService } from "@app/_services/lessons.service";

@Component({
    selector: "app-exercise",
    templateUrl: "./exercise.component.html",
    styleUrls: ["./exercise.component.scss"],
    standalone: true,
    imports: [
        AnagramComponent,
        FillinComponent,
        MatchPairComponent,
        McqComponent,
        RecordingComponent,
        TrueFalseComponent,
    ],
})
export class ExerciseComponent implements OnDestroy {
    private lessonService = inject(LessonsService);

    public exercise: any = {};
    public exerciseSubscription: Subscription;

    constructor() {
        this.exerciseSubscription = this.lessonService.currentExercise.subscribe((exercise) => {
            if (exercise) {
                this.exercise = exercise;
            }
        });
    }

    ngOnDestroy() {
        this.lessonService.setExercise({});
        this.exerciseSubscription.unsubscribe();
    }
}
