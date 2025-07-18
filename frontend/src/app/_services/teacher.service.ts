import { BehaviorSubject } from "rxjs";
import { Injectable } from "@angular/core";

import { BaseService } from "./base.service";
import * as API from "app/_constants/api.constants";

@Injectable()
export class TeacherService extends BaseService {
    private tabSubject = new BehaviorSubject<any>("");
    private schoolSubject = new BehaviorSubject<any>([]);
    private classroomSubject = new BehaviorSubject<any>([]);
    private teacherSubject = new BehaviorSubject<any>({});
    private currentRouteSubject = new BehaviorSubject<any>("");

    public currentTab = this.tabSubject.asObservable();
    public currentSchool = this.schoolSubject.asObservable();
    public currentClassroom = this.classroomSubject.asObservable();
    public teacherObj = this.teacherSubject.asObservable();
    public currentRoute = this.currentRouteSubject.asObservable();

    setTab(tab: string) {
        this.tabSubject.next(tab);
    }
    setSchool(school: any) {
        this.schoolSubject.next(school);
    }
    setClassroom(classroom: any) {
        this.classroomSubject.next(classroom);
    }

    setTeacher(teacher: any) {
        this.teacherSubject.next(teacher);
    }

    setCurrentRoute(route: string) {
        this.currentRouteSubject.next(route);
    }

    getActivitiesData(params): any {
        return this.callApi(API.Teacher.GET_ACTIVITIES, "POST", params, {}, "site", true);
    }
}
