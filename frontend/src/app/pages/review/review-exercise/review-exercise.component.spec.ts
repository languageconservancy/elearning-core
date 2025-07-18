import { ComponentFixture, TestBed, waitForAsync } from "@angular/core/testing";

import { ReviewExerciseComponent } from "./review-exercise.component";

describe("ReviewExerciseComponent", () => {
    let component: ReviewExerciseComponent;
    let fixture: ComponentFixture<ReviewExerciseComponent>;

    beforeEach(waitForAsync(() => {
        void TestBed.configureTestingModule({
            declarations: [ReviewExerciseComponent],
        }).compileComponents();
    }));

    beforeEach(() => {
        fixture = TestBed.createComponent(ReviewExerciseComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it("should create", () => {
        expect(component).toBeTruthy();
    });
});
