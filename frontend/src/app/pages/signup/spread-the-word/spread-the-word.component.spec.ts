import { ComponentFixture, TestBed, waitForAsync } from "@angular/core/testing";

import { SpreadTheWordComponent } from "./spread-the-word.component";

describe("SpreadTheWordComponent", () => {
    let component: SpreadTheWordComponent;
    let fixture: ComponentFixture<SpreadTheWordComponent>;

    beforeEach(waitForAsync(() => {
        void TestBed.configureTestingModule({
            declarations: [SpreadTheWordComponent],
        }).compileComponents();
    }));

    beforeEach(() => {
        fixture = TestBed.createComponent(SpreadTheWordComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it("should create", () => {
        expect(component).toBeTruthy();
    });
});
