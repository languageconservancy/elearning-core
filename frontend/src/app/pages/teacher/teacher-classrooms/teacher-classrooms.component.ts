import { Component, OnDestroy } from "@angular/core";
import { Router } from "@angular/router";
import { Subscription } from "rxjs";
import { CookieService } from "app/_services/cookie.service";
import { CdkDragDrop, moveItemInArray, transferArrayItem } from "@angular/cdk/drag-drop";
import Swal from "sweetalert2";

import { ClassroomService } from "app/_services/classroom.service";
import { TeacherService } from "app/_services/teacher.service";
import { Loader } from "app/_services/loader.service";
import { SnackbarService } from "app/_services/snackbar.service";

@Component({
    selector: "app-teacher-classrooms",
    templateUrl: "./teacher-classrooms.component.html",
    styleUrls: ["./teacher-classrooms.component.scss"],
})
export class TeacherClassroomsComponent implements OnDestroy {
    private schoolSubscription: Subscription;
    private teacherSubscription: Subscription;
    private classroomSubscription: Subscription;
    public errorMsg = "";
    public showError = false;
    public teacher: any = [];
    public classroom: any = {};
    public classroomStudents: any = [];
    public schoolStudents: any = [];
    public classroomUnits: any = [];
    public school: any = [];
    public activeEditUser: number = -1;
    public unsavedStudentChanges = false;

    constructor(
        private classroomService: ClassroomService,
        private teacherService: TeacherService,
        private loader: Loader,
        private router: Router,
        private cookieService: CookieService,
        private snackbarService: SnackbarService,
    ) {
        this.cookieService
            .get("AuthToken")
            .then((value) => {
                if (value == "") {
                    throw value;
                }
            })
            .catch(() => {
                void this.router.navigate([""]);
            });

        this.teacherService.setTab("teacher-classrooms");
        this.schoolSubscription = this.teacherService.currentSchool.subscribe((school) => {
            this.school = school;
        });
        this.teacherSubscription = this.teacherService.teacherObj.subscribe((teacher) => {
            this.teacher = teacher;
        });
        this.classroomSubscription = this.teacherService.currentClassroom.subscribe((classroom) => {
            if (!!classroom) {
                this.classroom = classroom;
                this.getUnitsAndStudents();
            }
        });
    }

    updateClassroom(classroom) {
        if (!!classroom) {
            this.loader.setLoader(true);
            const params = {
                user_id: this.teacher.id,
                type: "updateClassroom",
                params: {
                    id: classroom.id,
                    name: classroom.name,
                    start_date: classroom.start_date,
                    end_date: classroom.end_date,
                },
            };
            this.classroomService
                .updateClassroomData(params)
                .then(() => {})
                .catch(() => {
                    this.snackbarService.showSnackbar({
                        status: false,
                        msg: "Something went wrong. Please try again soon.",
                    });
                })
                .finally(() => {
                    this.loader.setLoader(false);
                });
        } else {
            this.snackbarService.showSnackbar({
                status: false,
                msg: "Something went wrong. Please try again soon.",
            });
        }
    }

    markStudentsUnsaved() {
        this.unsavedStudentChanges = true;
    }

    getEditUser(i: number) {
        if (this.activeEditUser == i) {
            this.activeEditUser = -1;
        } else {
            this.activeEditUser = i;
        }
    }
    checkDateAndUpdateClassroomUnit(classroomUnit) {
        if (!!classroomUnit.release_date) {
            const releaseDate = new Date(classroomUnit.release_date);
            const today = new Date();
            if (today < releaseDate) {
                classroomUnit.active = false;
            }
        }
        this.updateClassroomUnit(classroomUnit);
    }

    updateClassroomUnit(classroomUnit: { id: any; optional: any; active: any; no_repeat: any; release_date: any }) {
        if (!!classroomUnit) {
            this.loader.setLoader(true);
            const params = {
                user_id: this.teacher.id,
                type: "updateClassroomUnit",
                params: {
                    id: classroomUnit.id,
                    optional: classroomUnit.optional,
                    active: classroomUnit.active,
                    no_repeat: classroomUnit.no_repeat,
                    release_date: classroomUnit.release_date,
                },
            };
            this.classroomService
                .updateClassroomData(params)
                .then(() => {})
                .catch(() => {
                    this.snackbarService.showSnackbar({
                        status: false,
                        msg: "Something went wrong. Please try again soon.",
                    });
                })
                .finally(() => {
                    this.loader.setLoader(false);
                });
        } else {
            this.snackbarService.showSnackbar({
                status: false,
                msg: "Something went wrong. Please try again soon.",
            });
        }
    }
    //this receives calls from the bulk change buttons.  While only 2 switches are implemented, this function can handle date/norepeat as well.
    updateAllClassroomUnits(field, value) {
        if (!!field && value != null) {
            //get confirmation
            void Swal.fire({
                title: "Bulk Change",
                text: 'Are you sure to change "' + field + '" to "' + value + '" for all units?',
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, change it!",
            }).then((result) => {
                if (result.isConfirmed || result.value) {
                    let updateCount = 0;
                    if (this.classroomUnits.length) {
                        this.loader.setLoader(true);
                    }
                    this.classroomUnits.forEach((classroomUnit) => {
                        //set that field for each unit as it loops
                        //it might be better to do this as one api call in the future.
                        switch (field) {
                            case "optional": {
                                classroomUnit.optional = value;
                                break;
                            }
                            case "active": {
                                classroomUnit.active = value;
                                break;
                            }
                            case "no_repeat": {
                                classroomUnit.no_repeat = value;
                                break;
                            }
                            case "release_date": {
                                classroomUnit.release_date = value;
                                break;
                            }
                            default: {
                                break;
                            }
                        }
                        const params = {
                            user_id: this.teacher.id,
                            type: "updateClassroomUnit",
                            params: {
                                id: classroomUnit.id,
                                optional: classroomUnit.optional,
                                active: classroomUnit.active,
                                no_repeat: classroomUnit.no_repeat,
                                release_date: classroomUnit.release_date,
                            },
                        };
                        updateCount++;
                        this.classroomService
                            .updateClassroomData(params)
                            .then(() => {
                                updateCount--;
                                //if all api calls are received, end loader
                                if (updateCount == 0) {
                                    this.loader.setLoader(false);
                                }
                            })
                            .catch(() => {
                                updateCount--;
                                this.snackbarService.showSnackbar({
                                    status: false,
                                    msg: "Something went wrong. Please try again soon.",
                                });
                                if (updateCount == 0) {
                                    this.loader.setLoader(false);
                                }
                            });
                    });
                    //if api fails after 25 seconds, kill loader and show notice
                    setTimeout(() => {
                        if (updateCount != 0) {
                            this.snackbarService.showSnackbar({
                                status: false,
                                msg: "A problem may have occurred. Please try again if there is any issue",
                            });
                            this.loader.setLoader(false);
                        }
                    }, 25000);
                }
            });
        }
    }

    updateClassroomStudents() {
        if (!!this.classroomStudents) {
            const listClassroomStudents = [];
            //clean data for api call
            this.classroomStudents.forEach((classroomStudent) => {
                if (!!classroomStudent.school_id) {
                    listClassroomStudents.push({
                        id: null,
                        role_id: 3,
                        classroom_id: this.classroom.id,
                        user_id: classroomStudent.user_id,
                    });
                } else {
                    listClassroomStudents.push({
                        id: classroomStudent.id,
                        role_id: classroomStudent.role_id,
                        classroom_id: classroomStudent.classroom_id,
                        user_id: classroomStudent.user_id,
                    });
                }
            });
            this.loader.setLoader(true);
            const params = {
                user_id: this.teacher.id,
                type: "updateClassroomStudents",
                params: listClassroomStudents,
            };
            this.classroomService
                .updateClassroomData(params)
                .then(() => {
                    this.unsavedStudentChanges = false;
                })
                .catch(() => {
                    this.snackbarService.showSnackbar({
                        status: false,
                        msg: "Something went wrong. Please try again soon.",
                    });
                })
                .finally(() => {
                    this.loader.setLoader(false);
                });
        } else {
            this.snackbarService.showSnackbar({
                status: false,
                msg: "Something went wrong. Please try again soon.",
            });
        }
    }

    drop(event: CdkDragDrop<string[]>) {
        if (event.previousContainer === event.container) {
            moveItemInArray(event.container.data, event.previousIndex, event.currentIndex);
        } else {
            this.unsavedStudentChanges = true;
            transferArrayItem(
                event.previousContainer.data,
                event.container.data,
                event.previousIndex,
                event.currentIndex,
            );
        }
    }

    moveStudentToClassroom(i) {
        this.classroomStudents.push(this.schoolStudents[i]);
        this.schoolStudents.splice(i, 1);
        this.unsavedStudentChanges = true;
    }
    moveStudentToSchool(i) {
        this.schoolStudents.push(this.classroomStudents[i]);
        this.classroomStudents.splice(i, 1);
        this.unsavedStudentChanges = true;
    }

    getUnitsAndStudents() {
        if (!!this.teacher && !!this.classroom) {
            const params = {
                user_id: this.teacher.id,
                classroom_id: this.classroom.id,
                school_id: this.school.school_id,
            };
            this.classroomService.getTeacherClassroomUnitsAndStudents(params).then((res) => {
                this.classroomStudents = res.data.results.students;
                this.schoolStudents = res.data.results.schoolStudents;
                this.classroomUnits = res.data.results.units;
                if (!!this.classroomUnits) {
                    this.classroomUnits.forEach((classroomUnit) => {
                        if (!!classroomUnit.release_date) {
                            classroomUnit.release_date = classroomUnit.release_date.substring(0, 10);
                        }
                    });
                }
            });
        }
    }
    ngOnDestroy() {
        this.schoolSubscription.unsubscribe();
        this.classroomSubscription.unsubscribe();
        this.teacherSubscription.unsubscribe();
    }
}
