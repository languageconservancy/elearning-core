import { ComponentFixture, TestBed, waitForAsync } from "@angular/core/testing";

import { ReportPostModalComponent } from "./report-post-modal.component";

describe("ReportModalComponent", () => {
    let component: ReportPostModalComponent;
    let fixture: ComponentFixture<ReportPostModalComponent>;

    beforeEach(waitForAsync(() => {
        void TestBed.configureTestingModule({
            declarations: [ReportPostModalComponent],
        }).compileComponents();
    }));

    beforeEach(() => {
        fixture = TestBed.createComponent(ReportPostModalComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it("should create", () => {
        expect(component).toBeTruthy();
    });
});
