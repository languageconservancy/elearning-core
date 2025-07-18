import { ComponentFixture, TestBed, waitForAsync } from "@angular/core/testing";

import { TeacherNavComponent } from "./teacher-nav.component";

describe("TeacherNavComponent", () => {
    let component: TeacherNavComponent;
    let fixture: ComponentFixture<TeacherNavComponent>;

    beforeEach(waitForAsync(() => {
        void TestBed.configureTestingModule({
            declarations: [TeacherNavComponent],
        }).compileComponents();
    }));

    beforeEach(() => {
        fixture = TestBed.createComponent(TeacherNavComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it("should create", () => {
        expect(component).toBeTruthy();
    });
});
