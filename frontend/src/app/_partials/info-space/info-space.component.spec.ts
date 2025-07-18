import { ComponentFixture, TestBed, waitForAsync } from "@angular/core/testing";

import { InfoSpaceComponent } from "./info-space.component";

describe("InfoSpaceComponent", () => {
    let component: InfoSpaceComponent;
    let fixture: ComponentFixture<InfoSpaceComponent>;

    beforeEach(waitForAsync(() => {
        void TestBed.configureTestingModule({
            declarations: [InfoSpaceComponent],
        }).compileComponents();
    }));

    beforeEach(() => {
        fixture = TestBed.createComponent(InfoSpaceComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it("should create", () => {
        expect(component).toBeTruthy();
    });
});
