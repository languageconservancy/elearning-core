import { ComponentFixture, TestBed, waitForAsync } from "@angular/core/testing";

import { YourProgressComponent } from "./your-progress.component";

describe("YourProgressComponent", () => {
    let component: YourProgressComponent;
    let fixture: ComponentFixture<YourProgressComponent>;

    beforeEach(waitForAsync(() => {
        void TestBed.configureTestingModule({
            declarations: [YourProgressComponent],
        }).compileComponents();
    }));

    beforeEach(() => {
        fixture = TestBed.createComponent(YourProgressComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it("should create", () => {
        expect(component).toBeTruthy();
    });
});
