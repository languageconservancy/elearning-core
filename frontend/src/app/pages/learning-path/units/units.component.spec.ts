import { ComponentFixture, TestBed, waitForAsync } from "@angular/core/testing";

import { UnitsComponent } from "./units.component";

describe("UnitsComponent", () => {
    let component: UnitsComponent;
    let fixture: ComponentFixture<UnitsComponent>;

    beforeEach(waitForAsync(() => {
        void TestBed.configureTestingModule({
            declarations: [UnitsComponent],
        }).compileComponents();
    }));

    beforeEach(() => {
        fixture = TestBed.createComponent(UnitsComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it("should create", () => {
        expect(component).toBeTruthy();
    });
});
