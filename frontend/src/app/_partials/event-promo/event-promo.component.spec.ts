import { ComponentFixture, TestBed, waitForAsync } from "@angular/core/testing";

import { EventPromoComponent } from "./event-promo.component";

describe("EventPromoComponent", () => {
    let component: EventPromoComponent;
    let fixture: ComponentFixture<EventPromoComponent>;

    beforeEach(waitForAsync(() => {
        void TestBed.configureTestingModule({
            declarations: [EventPromoComponent],
        }).compileComponents();
    }));

    beforeEach(() => {
        fixture = TestBed.createComponent(EventPromoComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it("should create", () => {
        expect(component).toBeTruthy();
    });
});
