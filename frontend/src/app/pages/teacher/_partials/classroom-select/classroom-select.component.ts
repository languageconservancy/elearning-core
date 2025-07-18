import { Component, OnInit, OnDestroy, ViewChild } from "@angular/core";
import { Subscription } from "rxjs";
import { UntypedFormControl, UntypedFormGroup, Validators } from "@angular/forms";

import { ClassroomService } from "app/_services/classroom.service";
import { TeacherService } from "app/_services/teacher.service";
import { Loader } from "app/_services/loader.service";
import { SnackbarService } from "app/_services/snackbar.service";

@Component({
    selector: "app-classroom-select",
    templateUrl: "./classroom-select.component.html",
    styleUrls: ["./classroom-select.component.scss"],
})
export class ClassroomSelectComponent implements OnInit, OnDestroy {
    private schoolSubscription: Subscription;
    private teacherSubscription: Subscription;
    private classroomSubscription: Subscription;
    @ViewChild("formModal") formModal;
    public ClassroomForm: UntypedFormGroup;
    public ClassroomEditForm: UntypedFormGroup;
    public modalType = "new";
    public teacher: any = {};
    public listTeacherLevels: any = [];
    public classrooms: any;
    public school: any = [];
    public currentClassroom: any = [];
    public currentClassroomIndex: number = -1;

    constructor(
        private classroomService: ClassroomService,
        private loader: Loader,
        private teacherService: TeacherService,
        private snackbarService: SnackbarService,
    ) {
        this.teacherSubscription = this.teacherService.teacherObj.subscribe((teacher) => {
            this.teacher = teacher;
        });
        this.schoolSubscription = this.teacherService.currentSchool.subscribe((school) => {
            if (!!school && this.school.length != 0 && this.school.school_id != school.school_id) {
                this.school = school;
                this.getClassrooms();
            } else {
                this.school = school;
            }
        });
        this.classroomSubscription = this.teacherService.currentClassroom.subscribe((classroom) => {
            if (!!classroom) {
                this.currentClassroom = classroom;
            }
        });
    }

    ngOnInit() {
        if (this.currentClassroom && Object.keys(this.currentClassroom).length !== 0) {
            this.getClassrooms(this.currentClassroom.id);
        } else {
            this.getClassrooms();
        }
        this.getTeacherLevels();
        this.ClassroomForm = new UntypedFormGroup({
            // eslint-disable-next-line @typescript-eslint/unbound-method
            name: new UntypedFormControl("", [Validators.required, this.validateBlankValue.bind(this)]),
            // eslint-disable-next-line @typescript-eslint/unbound-method
            startDate: new UntypedFormControl("", [Validators.required, this.validateBlankValue.bind(this)]),
            level: new UntypedFormControl("0"),
            endDate: new UntypedFormControl("0"),
        });
    }

    ngOnDestroy() {
        this.schoolSubscription.unsubscribe();
        this.classroomSubscription.unsubscribe();
        this.teacherSubscription.unsubscribe();
    }

    setCurrentClassroom(i = 0) {
        if (this.classrooms.length > 0) {
            this.currentClassroomIndex = i;
            this.currentClassroom = this.classrooms[i];
            this.teacherService.setClassroom(this.classrooms[i]);
        } else {
            this.currentClassroomIndex = -1;
            this.teacherService.setClassroom({});
        }
    }

    getClassrooms(classroomId = 0) {
        this.classroomService
            .getTeacherClassrooms({
                user_id: this.teacher.id,
                school_id: this.school.school.id,
            })
            .then((res) => {
                if (res.data.results.length !== 0) {
                    const today = new Date();
                    const localActive = [];
                    res.data.results.forEach((classroom) => {
                        const endingDate = new Date(classroom.end_date);
                        if (endingDate > today) {
                            // Archived
                            localActive.push(classroom);
                        }
                    });
                    this.classrooms = localActive;
                    let i = 0;
                    if (classroomId != 0) {
                        i = this.classrooms.findIndex(({ id }) => id === classroomId);
                        if (i < 0) {
                            i = 0;
                        }
                    }
                    this.setCurrentClassroom(i);
                }
            });
    }

    archiveClassroom() {
        this.classroomService
            .archiveClassroom({
                user_id: this.teacher.id,
                school_id: this.school.school.id,
                classroom_id: this.currentClassroom.id,
            })
            .then((res) => {
                if (!res.data.status) {
                    this.snackbarService.showSnackbar({
                        status: false,
                        msg: res.data.message,
                    });
                    return;
                }
                if (this.currentClassroomIndex >= 0) {
                    // update current classroom data
                    this.classrooms[this.currentClassroomIndex] = res.data.results;
                    this.ClassroomEditForm.get("endDate").patchValue(res.data.results.end_date);
                }
                this.snackbarService.showSnackbar({
                    status: true,
                    msg: "Classroom archived.",
                });
            });
    }

    getTeacherLevels() {
        this.classroomService
            .getTeacherLevels({
                user_id: this.teacher.id,
                school_id: this.school.school_id,
            })
            .then(
                (res) => {
                    this.listTeacherLevels = res.data.results["teacherLevels"].reverse();
                },
                (err) => {
                    console.error("[classroom-select] Error getting teacher levels. ", err);
                },
            )
            .catch((err) => {
                console.error("[classroom-select] Error getting teacher levels. ", err);
            });
    }

    openModal(type) {
        this.modalType = type;
        if (type == "update") {
            this.ClassroomEditForm = new UntypedFormGroup({
                name: new UntypedFormControl(this.currentClassroom.name, [
                    // eslint-disable-next-line @typescript-eslint/unbound-method
                    Validators.required,
                    this.validateBlankValue.bind(this),
                ]),
                startDate: new UntypedFormControl(this.currentClassroom.start_date.substring(0, 10), [
                    // eslint-disable-next-line @typescript-eslint/unbound-method
                    Validators.required,
                    this.validateBlankValue.bind(this),
                ]),
                endDate: new UntypedFormControl(this.currentClassroom.end_date.substring(0, 10)),
            });
        }
        this.formModal.nativeElement.className = "modal fade show";
    }

    closeModal() {
        this.formModal.nativeElement.className = "modal hide";
    }

    updateClassroom(form) {
        if (form.valid) {
            this.loader.setLoader(true);
            const params = {
                user_id: this.teacher.id,
                type: "updateClassroom",
                params: {
                    id: this.currentClassroom.id,
                    name: form.value.name,
                    start_date: form.value.startDate,
                    end_date: form.value.endDate,
                },
            };
            this.classroomService
                .updateClassroomData(params)
                .then((res) => {
                    if (res.data.status) {
                        this.closeModal();
                        this.getClassrooms(res.data.results.id);
                    } else {
                        this.snackbarService.showSnackbar({
                            status: false,
                            msg: "Something went wrong. Please try again soon.",
                        });
                    }
                })
                .catch((err) => {
                    console.error("[classroom-select] Error updating classroom. ", err);
                })
                .finally(() => {
                    this.loader.setLoader(false);
                });
        } else {
            this.snackbarService.showSnackbar({
                status: false,
                msg: "Please fill in all fields with valid data before moving forward.",
            });
        }
    }

    createNewClassroom(form) {
        if (form.valid) {
            this.loader.setLoader(true);
            const params = {
                user_id: this.teacher.id,
                type: "newClassroom",
                params: {
                    name: form.value.name,
                    start_date: form.value.startDate,
                    end_date: form.value.endDate,
                    school_id: this.school.school_id,
                    created_by: this.teacher.id,
                    level_id: this.listTeacherLevels[form.value.level].level.id,
                },
            };
            this.classroomService
                .updateClassroomData(params)
                .then((res) => {
                    if (res.data.status) {
                        this.closeModal();
                        this.getClassrooms(res.data.results.id);
                    } else {
                        this.snackbarService.showSnackbar({
                            status: false,
                            msg: "Something went wrong. Please try again soon.",
                        });
                    }
                })
                .catch((err) => {
                    console.error("[classroom-select] Error creating classroom. ", err);
                })
                .finally(() => {
                    this.loader.setLoader(false);
                });
        } else {
            this.snackbarService.showSnackbar({
                status: false,
                msg: "Please fill in all fields with valid data before moving forward.",
            });
        }
    }

    private validateBlankValue(control: UntypedFormControl): any {
        if (this.ClassroomForm) {
            return control.value.trim() === "" ? { emptyValue: true } : null;
        }
        if (this.ClassroomEditForm) {
            return control.value.trim() === "" ? { emptyValue: true } : null;
        }
    }
}
