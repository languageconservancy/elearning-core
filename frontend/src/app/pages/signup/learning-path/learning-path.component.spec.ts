import { ComponentFixture, TestBed, waitForAsync } from "@angular/core/testing";

import { LearningPathComponent } from "./learning-path.component";

describe("LearningPathComponent", () => {
    let component: LearningPathComponent;
    let fixture: ComponentFixture<LearningPathComponent>;

    beforeEach(waitForAsync(() => {
        void TestBed.configureTestingModule({
            declarations: [LearningPathComponent],
        }).compileComponents();
    }));

    beforeEach(() => {
        fixture = TestBed.createComponent(LearningPathComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it("should create", () => {
        expect(component).toBeTruthy();
    });
});
