import { ComponentFixture, TestBed, waitForAsync } from "@angular/core/testing";

import { ClassroomClassesComponent } from "./classroom-classes.component";

describe("ClassroomClassesComponent", () => {
    let component: ClassroomClassesComponent;
    let fixture: ComponentFixture<ClassroomClassesComponent>;

    beforeEach(waitForAsync(() => {
        void TestBed.configureTestingModule({
            declarations: [ClassroomClassesComponent],
        }).compileComponents();
    }));

    beforeEach(() => {
        fixture = TestBed.createComponent(ClassroomClassesComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it("should create", () => {
        expect(component).toBeTruthy();
    });
});
