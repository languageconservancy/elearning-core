import { ComponentFixture, TestBed, waitForAsync } from "@angular/core/testing";

import { LearningSpeedComponent } from "./learning-speed.component";

describe("LearningSpeedComponent", () => {
    let component: LearningSpeedComponent;
    let fixture: ComponentFixture<LearningSpeedComponent>;

    beforeEach(waitForAsync(() => {
        void TestBed.configureTestingModule({
            declarations: [LearningSpeedComponent],
        }).compileComponents();
    }));

    beforeEach(() => {
        fixture = TestBed.createComponent(LearningSpeedComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it("should create", () => {
        expect(component).toBeTruthy();
    });
});
