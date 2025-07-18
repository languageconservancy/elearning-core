import { ComponentFixture, TestBed, waitForAsync } from "@angular/core/testing";

import { TeacherClassroomsComponent } from "./teacher-classrooms.component";

describe("TeacherClassroomsComponent", () => {
    let component: TeacherClassroomsComponent;
    let fixture: ComponentFixture<TeacherClassroomsComponent>;

    beforeEach(waitForAsync(() => {
        void TestBed.configureTestingModule({
            declarations: [TeacherClassroomsComponent],
        }).compileComponents();
    }));

    beforeEach(() => {
        fixture = TestBed.createComponent(TeacherClassroomsComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it("should create", () => {
        expect(component).toBeTruthy();
    });
});
