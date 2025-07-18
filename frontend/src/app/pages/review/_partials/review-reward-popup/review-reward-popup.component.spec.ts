import { ComponentFixture, TestBed, waitForAsync } from "@angular/core/testing";

import { ReviewRewardPopupComponent } from "./review-reward-popup.component";

describe("ReviewRewardPopupComponent", () => {
    let component: ReviewRewardPopupComponent;
    let fixture: ComponentFixture<ReviewRewardPopupComponent>;

    beforeEach(waitForAsync(() => {
        void TestBed.configureTestingModule({
            declarations: [ReviewRewardPopupComponent],
        }).compileComponents();
    }));

    beforeEach(() => {
        fixture = TestBed.createComponent(ReviewRewardPopupComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it("should create", () => {
        expect(component).toBeTruthy();
    });
});
