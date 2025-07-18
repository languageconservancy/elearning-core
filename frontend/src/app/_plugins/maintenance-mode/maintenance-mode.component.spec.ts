import { ComponentFixture, TestBed, waitForAsync } from "@angular/core/testing";

import { MaintenanceModeComponent } from "./maintenance-mode.component";

describe("MaintenanceModeComponent", () => {
    let component: MaintenanceModeComponent;
    let fixture: ComponentFixture<MaintenanceModeComponent>;

    beforeEach(waitForAsync(() => {
        void TestBed.configureTestingModule({
            declarations: [MaintenanceModeComponent],
        }).compileComponents();
    }));

    beforeEach(() => {
        fixture = TestBed.createComponent(MaintenanceModeComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it("should create", () => {
        expect(component).toBeTruthy();
    });
});
