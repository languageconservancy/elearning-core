import { Component, OnInit } from "@angular/core";
import { Router } from "@angular/router";
import { Location } from "@angular/common";
import { CookieService } from "app/_services/cookie.service";

import { ClassroomService } from "app/_services/classroom.service";
import { TeacherService } from "app/_services/teacher.service";
import { Loader } from "app/_services/loader.service";

//this component navigates between teacher panels and selects the current school.
//Schools are only added from the backend

@Component({
    selector: "app-teacher-nav",
    templateUrl: "./teacher-nav.component.html",
    styleUrls: ["./teacher-nav.component.scss"],
})
export class TeacherNavComponent implements OnInit {
    public schools: any = [];
    public teacher: any = {};
    public tabName: string = "teacher-dashboard";
    public currentSchool: any = [];
    public currentSchoolIndex: number = -1;
    public currentClassroomIndex: number = -1;
    public isTrueVariable = true;

    constructor(
        private router: Router,
        private location: Location,
        private classroomService: ClassroomService,
        private teacherService: TeacherService,
        private cookieService: CookieService,
        private loader: Loader,
    ) {}
    ngOnInit() {
        this.cookieService
            .get("AuthUser")
            .then((value) => {
                if (value == "") {
                    throw value;
                }
                this.setTab(this.router.url.substring(1));
                this.teacher = JSON.parse(value);
                this.teacherService.setTeacher(this.teacher);
                this.loader.setLoader(true);
                this.classroomService
                    .getSchoolsAndRoles({ user_id: this.teacher.id })
                    .then((res) => {
                        this.loader.setLoader(false);
                        this.schools = res.data.results;
                        if (this.schools.length > 0 && this.currentSchoolIndex == -1) {
                            this.currentSchoolIndex = 0;
                            this.setCurrentSchool(this.currentSchoolIndex);
                        } else {
                            this.loader.setLoader(false);
                        }
                    })
                    .catch((err) => {
                        console.error(err);
                    })
                    .finally(() => {
                        this.loader.setLoader(false);
                    });
            })
            .catch(() => {
                void this.router.navigate([""]);
            });
    }
    setCurrentSchool(i) {
        this.currentSchool = this.schools[i];
        this.currentSchoolIndex = i;
        this.teacherService.setSchool(this.schools[i]);
        //this.getClassrooms();
    }

    setTab(tabName) {
        this.teacherService.setTab(tabName);
        this.tabName = tabName;
        this.location.go("/" + tabName);
    }
}
