import { Component, OnDestroy, ChangeDetectionStrategy } from "@angular/core";
import { Subscription } from "rxjs";

import { LessonsService } from "@app/_services/lessons.service";

@Component({
    selector: "app-exercise",
    templateUrl: "./exercise.component.html",
    styleUrls: ["./exercise.component.scss"],
    changeDetection: ChangeDetectionStrategy.Eager,
    standalone: false,
})
export class ExerciseComponent implements OnDestroy {
    public exercise: any = {};
    public exerciseSubscription: Subscription;

    constructor(private lessonService: LessonsService) {
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
