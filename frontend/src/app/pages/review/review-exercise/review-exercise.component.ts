import {
    ChangeDetectionStrategy,
    ChangeDetectorRef,
    Component,
    inject,
    OnDestroy,
    OnInit,
} from "@angular/core";
import { Subscription } from "rxjs";

import { AnagramComponent } from "@app/shared/exercises/anagram/anagram.component";
import { FillinComponent } from "@app/shared/exercises/fillin/fillin.component";
import { KeyboardShortcutsComponent } from "@app/shared/keyboard/keyboard-shortcuts/keyboard-shortcuts.component";
import { MatchPairComponent } from "@app/shared/exercises/match-pair/match-pair.component";
import { McqComponent } from "@app/shared/exercises/mcq/mcq.component";
import { RecordingComponent } from "@app/shared/exercises/recording/recording.component";
import { TrueFalseComponent } from "@app/shared/exercises/true-false/true-false.component";
import { ReviewService } from "@app/_services/review.service";
import { DeviceDetectorService } from "ngx-device-detector";

@Component({
    selector: "app-review-exercise",
    changeDetection: ChangeDetectionStrategy.Eager,
    imports: [
        AnagramComponent,
        FillinComponent,
        KeyboardShortcutsComponent,
        MatchPairComponent,
        McqComponent,
        RecordingComponent,
        TrueFalseComponent,
    ],
    templateUrl: "./review-exercise.component.html",
    styleUrls: ["./review-exercise.component.scss"],
    standalone: true,
})
export class ReviewExerciseComponent implements OnInit, OnDestroy {
    private reviewService = inject(ReviewService);
    private ref = inject(ChangeDetectorRef);
    private deviceDetector = inject(DeviceDetectorService);

    public exercise: any = {};
    public exerciseSubscription: Subscription;
    public popupSubscription: Subscription;
    public deviceSubscription: Subscription;
    public reviewCompletionPercentage: any;
    public reviewCompletionDataFetched: boolean = false;
    public isMobile: boolean = false;
    public isTablet: boolean = false;

    constructor() {
        this.exerciseSubscription = this.reviewService.currentExercise.subscribe((exercise) => {
            if (exercise && Object.keys(exercise).length > 0) {
                this.exercise = exercise;
                this.ref.detectChanges();
            }
        });
        this.popupSubscription = this.reviewService.reviewProgress.subscribe((res) => {
            const progressValue = res?.progressValue;

            if (progressValue?.showModal) {
                this.reviewCompletionDataFetched = !!progressValue?.review_counter;
                this.reviewCompletionPercentage =
                    this.reviewCompletionDataFetched &&
                    progressValue?.num_correct_review_answers_to_unlock_unit
                        ? (parseInt(progressValue?.review_counter) /
                              progressValue?.num_correct_review_answers_to_unlock_unit) *
                          100
                        : 100;
                this.reviewCompletionPercentage = Math.ceil(
                    Math.min(this.reviewCompletionPercentage, 100),
                );
            } else {
                this.reviewCompletionDataFetched = false;
            }
        });
    }

    ngOnInit() {
        this.isMobile = this.deviceDetector.isMobile();
        this.isTablet = this.deviceDetector.isTablet();
    }

    ngOnDestroy() {
        this.reviewService.setExercise({});
        this.exerciseSubscription.unsubscribe();
        this.popupSubscription.unsubscribe();
        this.reviewCompletionDataFetched = false;
    }
}
