import { Component, OnInit, ViewChild, ElementRef, OnDestroy } from "@angular/core";
import { Router } from "@angular/router";
import { MatSort } from "@angular/material/sort";
import { MatTableDataSource } from "@angular/material/table";
import { Subscription } from "rxjs";
import { CookieService } from "app/_services/cookie.service";
import * as xlsx from "xlsx";

import { TeacherService } from "app/_services/teacher.service";
import { ClassroomService } from "app/_services/classroom.service";
import { Loader } from "app/_services/loader.service";
import { SnackbarService } from "app/_services/snackbar.service";
import { environment } from "environments/environment";

export interface StudentActivitiesTable {
    id: number;
    studentImg: string;
    student: string;
    card: string;
    response: string;
    exercise: string;
    time: Date;
}
const ELEMENT_DATA: StudentActivitiesTable[] = [];

@Component({
    selector: "app-teacher-dashboard",
    templateUrl: "./teacher-dashboard.component.html",
    styleUrls: ["./teacher-dashboard.component.scss"],
})
export class TeacherDashboardComponent implements OnInit, OnDestroy {
    @ViewChild("table", { static: false }) private table: any;
    @ViewChild("progresstable", { static: false }) progressTable: ElementRef;
    private schoolSubscription: Subscription;
    private classroomSubscription: Subscription;
    private teacherSubscription: Subscription;
    public school: any = [];
    public classroom: any = {};
    public classroomStudents: any = [];
    public classroomUnits: any = [];
    public noClassrooms = false;
    public messageIsDirty = false;
    public teacher: any = [];
    public activities: any = [];
    public progresses: any = [];
    public initialStudentProgresses: any = [];
    public studentProgresses: any = [];
    public classOverview: any = [];
    public continuousActivityUpdate = false;
    public initialStudentActivities: any = [];
    public studentActivities: any = [];
    public activitiesItems: any = [];
    public retrievedInitialActivities = false;
    public currentStudentActivity: any = {};
    public currentStudentActivityIndex: number = -1;
    public lastModified;
    public environment = environment;
    displayedColumns: string[] = ["student", "card", "response", "exercise", "time"];
    studentActivitiesItems = new MatTableDataSource(ELEMENT_DATA);

    @ViewChild(MatSort, { static: true }) sort: MatSort;

    constructor(
        private teacherService: TeacherService,
        private classroomService: ClassroomService,
        private router: Router,
        private cookieService: CookieService,
        private loader: Loader,
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

        this.teacherService.setTab("teacher-dashboard");
        this.teacherSubscription = this.teacherService.teacherObj.subscribe((teacher) => {
            this.teacher = teacher;
        });
        this.schoolSubscription = this.teacherService.currentSchool.subscribe((school) => {
            this.school = school;
        });
        this.classroomSubscription = this.teacherService.currentClassroom.subscribe((classroom) => {
            if (!!classroom.id) {
                this.classroom = classroom;
                this.retrievedInitialActivities = false;
                this.getUnitsAndStudentsThenUpdateActivities();
                this.noClassrooms = false;
                this.messageIsDirty = false;
            } else {
                this.noClassrooms = true;
            }
        });
        //const ActivitySource = interval(10000);
        //this.activitySubscription = ActivitySource.subscribe(val => updateActivities());
    }

    applyFilter(event: KeyboardEvent) {
        const filterValue: string = (event.target as HTMLInputElement).value;
        this.studentActivitiesItems.filter = filterValue.trim().toLowerCase();
    }

    alphabetizeStudents(unsortedStudents: Array<any>) {
        const lNameMissing = [];
        const lNamePresent = [];
        let sortedStudents = [];

        unsortedStudents.forEach((student) => {
            if (!student.l_name || student.l_name.trim() === "") {
                lNameMissing.push(student);
            } else {
                lNamePresent.push(student);
            }
        });

        // eslint-disable-next-line unused-imports/no-unused-vars, @typescript-eslint/no-unused-vars
        return (sortedStudents = [
            ...lNameMissing.sort((a, b) => a.user.name.localeCompare(b.user.name)),
            ...lNamePresent.sort((a, b) => a.l_name.localeCompare(b.l_name)),
        ]);
    }

    ngOnInit() {
        this.studentActivitiesItems.sort = this.sort;
    }
    getUnitsAndStudentsThenUpdateActivities() {
        if (!!this.teacher.id && !!this.classroom.id) {
            this.classroomService
                .getTeacherClassroomUnitsAndStudents({
                    user_id: this.teacher.id,
                    classroom_id: this.classroom.id,
                })
                .then((res) => {
                    this.classroomStudents = res.data.results.students;
                    this.classroomUnits = res.data.results.units;
                    if (!this.retrievedInitialActivities) {
                        this.updateStudentActivities(this.teacher.id, this.classroom.id, null);
                        this.retrievedInitialActivities = true;
                    }
                });
        }
    }

    exportProgressToExcel() {
        const ws: xlsx.WorkSheet = xlsx.utils.table_to_sheet(this.progressTable.nativeElement);
        const wb: xlsx.WorkBook = xlsx.utils.book_new();
        xlsx.utils.book_append_sheet(wb, ws, "StudentProgress");
        xlsx.writeFile(wb, "StudentProgress - " + this.classroom.name + ".xlsx");
    }

    exportActivitiesToExcel() {
        const ws: xlsx.WorkSheet = xlsx.utils.table_to_sheet(this.table._elementRef.nativeElement);
        const wb: xlsx.WorkBook = xlsx.utils.book_new();
        xlsx.utils.book_append_sheet(wb, ws, "StudentActivities");
        xlsx.writeFile(
            wb,
            "StudentActivities - " +
                this.studentActivities[this.currentStudentActivityIndex].name +
                ".xlsx",
        );
    }
    ngOnDestroy() {
        this.continuousActivityUpdate = false;
        this.schoolSubscription.unsubscribe();
        this.classroomSubscription.unsubscribe();
        this.teacherSubscription.unsubscribe();
    }

    updateClassroomMessage(classroom) {
        if (!!classroom) {
            this.loader.setLoader(true);
            const params = {
                user_id: this.teacher.id,
                type: "updateClassroom",
                params: {
                    id: classroom.id,
                    teacher_message: classroom.teacher_message,
                },
            };
            this.classroomService
                .updateClassroomData(params)
                .then(() => {
                    this.messageIsDirty = false;
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

    markMessageDirty() {
        this.messageIsDirty = true;
    }

    getDisplayName(student: any): string {
        const lName = student?.l_name ?? student?.user?.l_name;
        const fName = student?.f_name ?? student?.user?.f_name;

        if (!!lName && !!fName) {
            return `${lName}, ${fName}`;
        }

        return student?.name ?? "";
    }

    repopulateStudentActivities() {
        this.currentStudentActivity = this.studentActivities[this.currentStudentActivityIndex];
        this.studentActivitiesItems.data = [];
        this.currentStudentActivity.user_activities.forEach((studentActivity) => {
            if (!this.currentStudentActivity.usersetting) {
                this.currentStudentActivity.usersetting = { aws_profile_link: "" };
            }
            this.studentActivitiesItems.data.unshift({
                id: studentActivity.id,
                studentImg: this.currentStudentActivity.usersetting.aws_profile_link,
                student: this.getDisplayName(this.currentStudentActivity),
                card: studentActivity.card.lakota,
                response: studentActivity.type,
                exercise: studentActivity.exercise_type,
                time: studentActivity.modified,
            });
        });
        this.studentActivitiesItems.sort = this.sort;
        if (!!this.currentStudentActivity.user_activities[0]) {
            this.lastModified = this.currentStudentActivity.user_activities[0].modified;
        }

        this.studentActivitiesItems._updateChangeSubscription();
    }

    updateStudentActivities(userId, classroomId, lastModified) {
        this.loader.setLoader(true);
        this.lastModified = lastModified;
        this.classroomService
            .getStudentActivities({
                user_id: userId,
                classroom_id: classroomId,
                last_modified: this.lastModified,
            })
            .then((res) => {
                this.initialStudentActivities = (res.data.results["studentActivities"] || []).map(
                    (activity) => ({
                        ...activity,
                        f_name: activity.f_name ?? activity.user?.f_name ?? null,
                        l_name: activity.l_name ?? activity.user?.l_name ?? null,
                    }),
                );
                this.initialStudentProgresses = res.data.results["studentProgress"];
                for (
                    let studentIndex = 0;
                    studentIndex < this.initialStudentProgresses.length;
                    studentIndex++
                ) {
                    if (this.lastModified == null) {
                        this.initialStudentProgresses[studentIndex].percents = [];
                    }
                    for (let unitIndex = 0; unitIndex < this.classroomUnits.length; unitIndex++) {
                        let currentUnitPercent = 0;
                        for (
                            let progressIndex = 0;
                            progressIndex <
                            this.initialStudentProgresses[studentIndex].user_unit_activities.length;
                            progressIndex++
                        ) {
                            if (
                                // eslint-disable-next-line prettier/prettier
                                this.initialStudentProgresses[studentIndex].user_unit_activities[progressIndex].unit_id == this.classroomUnits[unitIndex].level_unit.unit.id &&
                                // eslint-disable-next-line prettier/prettier
                                !!this.initialStudentProgresses[studentIndex].user_unit_activities[progressIndex].percent
                            ) {
                                currentUnitPercent =
                                    // eslint-disable-next-line prettier/prettier
                                    this.initialStudentProgresses[studentIndex].user_unit_activities[progressIndex].percent;
                            }
                        }
                        if (this.lastModified == null) {
                            this.initialStudentProgresses[studentIndex].percents.push(
                                currentUnitPercent,
                            );
                        } else {
                            this.initialStudentProgresses[studentIndex].percents[unitIndex] =
                                currentUnitPercent;
                        }
                    }
                }
                this.studentProgresses = this.alphabetizeStudents(this.initialStudentProgresses);

                //save most recent modified for next query
                //construct or update activities table
                this.studentActivities = this.alphabetizeStudents(this.initialStudentActivities);

                if (this.studentActivities.length > 0) {
                    if (this.currentStudentActivityIndex == -1) {
                        this.currentStudentActivityIndex = 0;
                    }

                    this.repopulateStudentActivities();
                }
            })
            .catch((err) => {
                console.error(err);
            })
            .finally(() => {
                this.loader.setLoader(false);
            });
        if (this.continuousActivityUpdate) {
            setTimeout(() => {
                this.updateStudentActivities(userId, this.classroom.id, this.lastModified);
            }, 10000);
        }
    }
}
