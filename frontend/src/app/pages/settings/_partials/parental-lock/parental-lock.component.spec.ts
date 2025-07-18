import { ComponentFixture, TestBed, waitForAsync } from "@angular/core/testing";

import { ParentalLockComponent } from "./parental-lock.component";

describe("ParentalLockComponent", () => {
    let component: ParentalLockComponent;
    let fixture: ComponentFixture<ParentalLockComponent>;

    beforeEach(waitForAsync(() => {
        void TestBed.configureTestingModule({
            declarations: [ParentalLockComponent],
        }).compileComponents();
    }));

    beforeEach(() => {
        fixture = TestBed.createComponent(ParentalLockComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it("should create", () => {
        expect(component).toBeTruthy();
    });
});
