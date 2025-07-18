import { ComponentFixture, TestBed, waitForAsync } from "@angular/core/testing";

import { ReviewNextPopupComponent } from "./review-next-popup.component";

describe("ReviewNextPopupComponent", () => {
    let component: ReviewNextPopupComponent;
    let fixture: ComponentFixture<ReviewNextPopupComponent>;

    beforeEach(waitForAsync(() => {
        void TestBed.configureTestingModule({
            declarations: [ReviewNextPopupComponent],
        }).compileComponents();
    }));

    beforeEach(() => {
        fixture = TestBed.createComponent(ReviewNextPopupComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it("should create", () => {
        expect(component).toBeTruthy();
    });
});
