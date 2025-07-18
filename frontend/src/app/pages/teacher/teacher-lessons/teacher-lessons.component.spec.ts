import { ComponentFixture, TestBed, waitForAsync } from "@angular/core/testing";

import { TeacherLessonsComponent } from "./teacher-lessons.component";

describe("TeacherLessonsComponent", () => {
    let component: TeacherLessonsComponent;
    let fixture: ComponentFixture<TeacherLessonsComponent>;

    beforeEach(waitForAsync(() => {
        void TestBed.configureTestingModule({
            declarations: [TeacherLessonsComponent],
        }).compileComponents();
    }));

    beforeEach(() => {
        fixture = TestBed.createComponent(TeacherLessonsComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it("should create", () => {
        expect(component).toBeTruthy();
    });
});
