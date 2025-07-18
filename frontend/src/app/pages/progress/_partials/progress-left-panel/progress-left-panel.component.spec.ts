import { ComponentFixture, TestBed, waitForAsync } from "@angular/core/testing";

import { ProgressLeftPanelComponent } from "./progress-left-panel.component";

describe("ProgressLeftPanelComponent", () => {
    let component: ProgressLeftPanelComponent;
    let fixture: ComponentFixture<ProgressLeftPanelComponent>;

    beforeEach(waitForAsync(() => {
        void TestBed.configureTestingModule({
            declarations: [ProgressLeftPanelComponent],
        }).compileComponents();
    }));

    beforeEach(() => {
        fixture = TestBed.createComponent(ProgressLeftPanelComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it("should create", () => {
        expect(component).toBeTruthy();
    });
});
