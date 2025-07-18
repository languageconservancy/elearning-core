import { Injectable } from "@angular/core";
import { BaseService } from "./base.service";
import * as API from "app/_constants/api.constants";

@Injectable()
export class ClassroomService extends BaseService {
    getSchoolsAndRoles(params: any) {
        return this.callApi(API.Classrooms.GET_SCHOOLS_AND_ROLES, "POST", params, {}, "site", true);
    }
    getTeacherLevelUnits(params: any) {
        return this.callApi(API.Classrooms.GET_TEACHER_LEVEL_UNITS, "POST", params, {}, "site", true);
    }
    getAvailablePaths(params: any) {
        return this.callApi(API.Classrooms.GET_AVAILABLE_PATHS, "POST", params, {}, "site", true);
    }
    getUnitCards(params: any) {
        return this.callApi(API.Classrooms.GET_UNIT_CARDS, "POST", params, {}, "site", true);
    }

    getTeacherLevels(params): any {
        return this.callApi(API.Classrooms.GET_TEACHER_LEVELS, "POST", params, {}, "site", true);
    }

    getTeacherClassrooms(params): any {
        return this.callApi(API.Classrooms.GET_TEACHER_CLASSROOMS, "POST", params, {}, "site", true);
    }

    getTeacherClassroomUnitsAndStudents(params): any {
        return this.callApi(API.Classrooms.GET_TEACHER_CLASSROOM_UNITS_AND_STUDENTS, "POST", params, {}, "site", true);
    }

    getStudentActivities(params): any {
        return this.callApi(API.Classrooms.GET_STUDENT_ACTIVITIES, "POST", params, {}, "site", true);
    }

    updateClassroomData(params): any {
        return this.callApi(API.Classrooms.UPDATE_CLASSROOM_DATA, "POST", params, {}, "site", true);
    }

    deleteClassroom(params): any {
        return this.callApi(API.Classrooms.DELETE_CLASSROOM, "POST", params, {}, "site", true);
    }

    archiveClassroom(params): any {
        return this.callApi(API.Classrooms.ARCHIVE_CLASSROOM, "POST", params, {}, "site", true);
    }

    getClassroomData(params): any {
        return this.callApi(API.Classrooms.GET_CLASSROOM_DATA, "POST", params, {}, "site", true);
    }
}
