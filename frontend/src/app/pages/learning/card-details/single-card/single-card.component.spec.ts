import { ComponentFixture, TestBed, waitForAsync } from "@angular/core/testing";

import { SingleCardComponent } from "./single-card.component";

describe("SingleCardComponent", () => {
    let component: SingleCardComponent;
    let fixture: ComponentFixture<SingleCardComponent>;

    beforeEach(waitForAsync(() => {
        void TestBed.configureTestingModule({
            declarations: [SingleCardComponent],
        }).compileComponents();
    }));

    beforeEach(() => {
        fixture = TestBed.createComponent(SingleCardComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it("should create", () => {
        expect(component).toBeTruthy();
    });
});
