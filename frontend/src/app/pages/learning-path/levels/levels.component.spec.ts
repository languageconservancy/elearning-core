import { ComponentFixture, TestBed, waitForAsync } from "@angular/core/testing";

import { LevelsComponent } from "./levels.component";

describe("LevelsComponent", () => {
    let component: LevelsComponent;
    let fixture: ComponentFixture<LevelsComponent>;

    beforeEach(waitForAsync(() => {
        void TestBed.configureTestingModule({
            declarations: [LevelsComponent],
        }).compileComponents();
    }));

    beforeEach(() => {
        fixture = TestBed.createComponent(LevelsComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it("should create", () => {
        expect(component).toBeTruthy();
    });
});
