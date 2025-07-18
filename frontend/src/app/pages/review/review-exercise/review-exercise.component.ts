import { Component, OnInit, OnDestroy, ChangeDetectorRef } from "@angular/core";
import { Subscription } from "rxjs";

import { ReviewService } from "app/_services/review.service";
import { DeviceDetectorService } from "ngx-device-detector";

@Component({
    selector: "app-review-exercise",
    templateUrl: "./review-exercise.component.html",
    styleUrls: ["./review-exercise.component.scss"],
})
export class ReviewExerciseComponent implements OnInit, OnDestroy {
    public exercise: any = {};
    public exerciseSubscription: Subscription;
    public popupSubscription: Subscription;
    public deviceSubscription: Subscription;
    public reviewCompletionPercentage: any;
    public reviewCompletionDataFetched: boolean = false;
    public isMobile: boolean = false;
    public isTablet: boolean = false;

    constructor(
        private reviewService: ReviewService,
        private ref: ChangeDetectorRef,
        private deviceDetector: DeviceDetectorService,
    ) {
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
                    this.reviewCompletionDataFetched && progressValue?.num_correct_review_answers_to_unlock_unit
                        ? (parseInt(progressValue?.review_counter) /
                              progressValue?.num_correct_review_answers_to_unlock_unit) *
                          100
                        : 100;
                this.reviewCompletionPercentage = Math.ceil(Math.min(this.reviewCompletionPercentage, 100));
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
