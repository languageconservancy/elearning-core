import { ComponentFixture, TestBed, waitForAsync } from "@angular/core/testing";

import { ReviewTimerComponent } from "./review-timer.component";

describe("ReviewTimerComponent", () => {
    let component: ReviewTimerComponent;
    let fixture: ComponentFixture<ReviewTimerComponent>;

    beforeEach(waitForAsync(() => {
        void TestBed.configureTestingModule({
            declarations: [ReviewTimerComponent],
        }).compileComponents();
    }));

    beforeEach(() => {
        fixture = TestBed.createComponent(ReviewTimerComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it("should create", () => {
        expect(component).toBeTruthy();
    });
});
