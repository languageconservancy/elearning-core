import { ComponentFixture, TestBed, waitForAsync } from "@angular/core/testing";

import { ClassroomUnitsComponent } from "./classroom-units.component";

describe("ClassroomUnitsComponent", () => {
    let component: ClassroomUnitsComponent;
    let fixture: ComponentFixture<ClassroomUnitsComponent>;

    beforeEach(waitForAsync(() => {
        void TestBed.configureTestingModule({
            declarations: [ClassroomUnitsComponent],
        }).compileComponents();
    }));

    beforeEach(() => {
        fixture = TestBed.createComponent(ClassroomUnitsComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it("should create", () => {
        expect(component).toBeTruthy();
    });
});
