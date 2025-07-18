import { ComponentFixture, TestBed, waitForAsync } from "@angular/core/testing";

import { ReviewCardsComponent } from "./review-cards.component";

describe("ReviewCardsComponent", () => {
    let component: ReviewCardsComponent;
    let fixture: ComponentFixture<ReviewCardsComponent>;

    beforeEach(waitForAsync(() => {
        void TestBed.configureTestingModule({
            declarations: [ReviewCardsComponent],
        }).compileComponents();
    }));

    beforeEach(() => {
        fixture = TestBed.createComponent(ReviewCardsComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it("should create", () => {
        expect(component).toBeTruthy();
    });
});
