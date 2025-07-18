/* eslint-disable @typescript-eslint/unbound-method */
import { Component, ElementRef, OnDestroy, OnInit, ViewChild } from "@angular/core";
import { Router } from "@angular/router";
import {
    UntypedFormArray,
    UntypedFormBuilder,
    UntypedFormControl,
    UntypedFormGroup,
    Validators,
} from "@angular/forms";
import { Subscription } from "rxjs";
import { CookieService } from "app/_services/cookie.service";

import { TeacherService } from "app/_services/teacher.service";
import { ClassroomService } from "app/_services/classroom.service";
import { Loader } from "app/_services/loader.service";
import { ExcelService } from "app/_services/excel.service";
import { RegistrationService } from "app/_services/registration.service";
import { ResetPasswordService } from "app/_services/reset-password.service";
import { RegexConsts } from "app/_constants/app.constants";
import { SnackbarService } from "app/_services/snackbar.service";
import { environment } from "environments/environment";
import { ErrorCode } from "app/shared/utils/error-code";

@Component({
    selector: "app-teacher-admin",
    templateUrl: "./teacher-admin.component.html",
    styleUrls: ["./teacher-admin.component.scss"],
})
export class TeacherAdminComponent implements OnInit, OnDestroy {
    private teacherSubscription: Subscription;
    private schoolSubscription: Subscription;
    @ViewChild("formModal") formModal;
    @ViewChild("passwordInput") passwordInput: ElementRef;
    public changeForm: UntypedFormGroup;
    public newLevelForm: UntypedFormGroup;
    public teacher: any = [];
    public school: any = [];
    public classrooms: any = [];
    public archivedClassrooms: any = [];
    public schoolStudents: any = [];
    public schoolTeachers: any = [];
    public activeEditUser: number = -1;
    public excelUsers: any = [];
    public modalType = "";
    public modalHeader = "";
    public registeredUserIds: any = [];
    public existingUserEmails: any = [];
    public wordlink = "";
    public newUsersForm: UntypedFormGroup;
    public getLinkForm: UntypedFormGroup;
    public newUsersUploadTemplateLink = `${environment.API}templates/download/teacher-portal-new-users-form.xlsx`;

    constructor(
        private classroomService: ClassroomService,
        private teacherService: TeacherService,
        public registrationService: RegistrationService,
        private resetPassService: ResetPasswordService,
        private router: Router,
        private loader: Loader,
        private cookieService: CookieService,
        private fb: UntypedFormBuilder,
        private excelSrv: ExcelService,
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

        this.teacherService.setTab("teacher-lessons");

        this.teacherSubscription = this.teacherService.teacherObj.subscribe((teacher) => {
            this.teacher = teacher;
        });

        this.schoolSubscription = this.teacherService.currentSchool.subscribe((school) => {
            this.school = school;
            this.getSchoolStudents();
        });
    }

    ngOnInit() {
        const emailRegex = RegexConsts.EMAIL_REGEX;

        this.newUsersForm = this.fb.group({
            classroom: -1,
            new_users: this.fb.array([
                this.fb.group({
                    studentid: new UntypedFormControl("", []),
                    fname: new UntypedFormControl("", [Validators.required, this.validateBlankValue.bind(this)]),
                    lname: new UntypedFormControl("", [Validators.required, this.validateBlankValue.bind(this)]),
                    email: new UntypedFormControl("", [
                        Validators.required,
                        Validators.pattern(emailRegex),
                        this.validateBlankValue.bind(this),
                    ]),
                    password: new UntypedFormControl("", [Validators.required, this.validateBlankValue.bind(this)]),
                    role: "3",
                    dob: new UntypedFormControl("", [Validators.required, this.validateBlankValue.bind(this)]),
                }),
            ]),
        });
        this.getLinkForm = this.fb.group({
            classroom: -1,
        });
        this.getSchoolStudents();
        this.getClassrooms();
    }

    ngOnDestroy(): void {
        this.teacherSubscription.unsubscribe();
        this.schoolSubscription.unsubscribe();
    }

    getClassrooms() {
        this.classroomService
            .getTeacherClassrooms({
                user_id: this.teacher.id,
                school_id: this.school.school.id,
            })
            .then((res) => {
                this.updateView(res.data.results);
            });
    }

    updateView(data) {
        const today = new Date();
        if (data.length === 0) {
            // Reset arrays
            this.classrooms = [];
            this.archivedClassrooms = [];
        }
        const localActive = [];
        const localArchived = [];
        data.forEach((classroom) => {
            const endingDate = new Date(classroom.end_date);
            if (endingDate >= today) {
                // Active
                localActive.push(classroom);
            } else {
                // Archived
                localArchived.push(classroom);
            }
        });
        this.archivedClassrooms = localArchived;
        this.classrooms = localActive;
    }

    deleteClassroom(id) {
        this.classroomService
            .deleteClassroom({
                user_id: this.teacher.id,
                school_id: this.school.school.id,
                classroom_id: id,
            })
            .then((res) => {
                this.updateView(res.data.results);
                this.snackbarService.showSnackbar({
                    status: true,
                    msg: "Classroom deleted.",
                });
            })
            .catch((e) => {
                console.error(e);
                console.error("Something went wrong while trying to delete classroom # " + id);
            });
    }

    changePasswordModal(i) {
        this.activeEditUser = i;
        this.modalType = "changePassword";
        this.modalHeader = "Reset Password for " + this.schoolStudents[i].user.name;
        this.changeForm = new UntypedFormGroup({
            password: new UntypedFormControl("", Validators.required),
            confirmpassword: new UntypedFormControl("", [
                Validators.required,
                this.validatePasswordConfirmation.bind(this),
            ]),
        });
        this.formModal.nativeElement.className = "modal fade show";
        // Set focus to first input for user-friendliness.
        // Use timeout to deal with delay in modal displaying.
        setTimeout(() => {
            this.passwordInput.nativeElement.focus();
        }, 100);
    }

    changePassword(form) {
        if (form.valid) {
            this.setLoader(true);
            const data = {
                teacher_id: this.teacher.id,
                student_user_id: this.schoolStudents[this.activeEditUser].user.id,
                new_password: form.value.password,
            };
            this.resetPassService
                .teacherChangePassword(data)
                .then(
                    (res: any) => {
                        this.setLoader(false);
                        if (res.data.status) {
                            this.closeModal();
                        } else {
                            this.snackbarService.showSnackbar({ status: false, msg: res.data.message });
                        }
                    },
                    (err) => {
                        this.setLoader(false);
                        this.snackbarService.showSnackbar({ status: false, msg: err.data.message });
                    },
                )
                .catch(() => {
                    this.setLoader(false);
                    this.snackbarService.showSnackbar({
                        status: false,
                        msg: "There has been an error. Please try again after some time while we fix it.",
                    });
                });
        } else {
            this.snackbarService.showSnackbar({
                status: false,
                msg: "Please fill in all fields with valid data before moving forward.",
            });
        }
    }

    private validatePasswordConfirmation(control: UntypedFormControl): any {
        if (this.changeForm) {
            return control.value === this.changeForm.get("password").value ? null : { notSame: true };
        }
    }

    get newUsers() {
        return this.newUsersForm.get("new_users") as UntypedFormArray;
    }

    addNewUserRow() {
        this.newUsers.push(
            this.fb.group({ studentid: "", fname: "", lname: "", email: "", password: "", role: "3", dob: "" }),
        );
    }

    deleteNewUserRow(index) {
        this.newUsers.removeAt(index);
    }

    private validateBlankValue(control: UntypedFormControl): any {
        if (this.newUsersForm) {
            return control.value.trim() === "" ? { emptyValue: true } : null;
        }
    }

    addNewUsers(form) {
        if (form.valid) {
            this.loader.setLoader(true);
            let classroomId = "";
            if (form.value.classroom != -1) {
                classroomId = this.classrooms[form.value.classroom].id;
            }
            for (let i = 0; i < form.value.new_users.length; i++) {
                setTimeout(() => {
                    let existingUserEmail = {};
                    let registeredUserId = {};
                    const data = {
                        name: form.value.new_users[i].fname + " " + form.value.new_users[i].lname[0],
                        dob: form.value.new_users[i].dob,
                        email: form.value.new_users[i].email,
                        password: form.value.new_users[i].password,
                        repassword: form.value.new_users[i].password,
                    };
                    this.setLoader(true);
                    this.registrationService
                        .teacherRegister(data)
                        .then((res: any) => {
                            if (res.data.status) {
                                registeredUserId = {
                                    id: res.data.results[0].id,
                                    role: form.value.new_users[i].role,
                                };
                                this.registeredUserIds.push(registeredUserId);
                            } else {
                                if (res.data.errorCode === ErrorCode.EMAIL_ALREADY_REGISTERED) {
                                    existingUserEmail = {
                                        email: data.email,
                                        role: form.value.new_users[i].role,
                                    };
                                    this.existingUserEmails.push(existingUserEmail);
                                }
                                this.setLoader(false);
                            }
                            const params = {
                                user_id: this.teacher.id,
                                type: "addNewSchoolUsers",
                                params: {
                                    school_id: this.school.school.id,
                                    student_id: form.value.new_users[i].studentid,
                                    f_name: form.value.new_users[i].fname,
                                    l_name: form.value.new_users[i].lname,
                                    classroom_id: classroomId,
                                    existing_user_email: existingUserEmail,
                                    registered_user_id: registeredUserId,
                                },
                            };
                            this.classroomService
                                .updateClassroomData(params)
                                .then((res) => {
                                    this.loader.setLoader(false);
                                    if (res.data.status) {
                                        //this.closeModal();
                                        if (i == form.value.new_users.length - 1) {
                                            this.closeModal();
                                            this.getSchoolStudents();
                                        }
                                    } else {
                                        this.snackbarService.showSnackbar({
                                            status: false,
                                            msg: res.data.message,
                                        });
                                    }
                                })
                                .catch((err) => {
                                    console.error(err);
                                });
                        })
                        .catch((err) => {
                            console.error(err);
                        })
                        .finally(() => {
                            this.setLoader(false);
                        });
                }, 1000);
            }
        } else {
            // loop through new users controls
            let errorMsg = "";
            this.newUsers.controls.forEach((group: UntypedFormGroup) => {
                if (group.status === "INVALID") {
                    // Loop through group controls to find invalid ones
                    Object.keys(group.controls).forEach((key) => {
                        if (group.controls[key].status === "INVALID") {
                            const control = group.controls;
                            errorMsg =
                                "New user #" +
                                (this.newUsers.controls.indexOf(group) + 1) +
                                "'s " +
                                key +
                                " is invalid: " +
                                `"${control[key].value}"`;
                        }
                    });
                }
            });
            this.snackbarService.showSnackbar({
                status: false,
                msg: errorMsg,
            });
        }
    }

    private setLoader(val: boolean) {
        this.loader.setLoader(val);
    }

    importExcelUsers(evt: any) {
        const target: DataTransfer = <DataTransfer>evt.target;
        if (target.files.length !== 1) throw new Error("Cannot use multiple files");

        const reader: FileReader = new FileReader();
        reader.onload = (e: any) => {
            const bstr: string = e.target.result;
            const data = <any[]>this.excelSrv.importFromFile(bstr);

            //first 2 lines should be headers info
            for (let i = 2; i < data.length; i++) {
                if (
                    !data[i][0] &&
                    !data[i][1] &&
                    !data[i][2] &&
                    !data[i][3] &&
                    !data[i][4] &&
                    !data[i][5] &&
                    !data[i][6]
                ) {
                    continue;
                }
                if (data[i][5] == "Teacher" || data[i][5] == "teacher") {
                    data[i][5] = 2;
                } else {
                    data[i][5] = 3;
                }
                data[i][7] = this.excelDatetoAge(data[i][6]);
                data[i][6] = this.excelDateToJSDate(data[i][6]);
                this.newUsers.push(
                    this.fb.group({
                        studentid: data[i][0],
                        fname: data[i][1],
                        lname: data[i][2],
                        email: data[i][3],
                        password: data[i][4],
                        role: data[i][5],
                        dob: data[i][6].toISOString().substring(0, 10),
                    }),
                );
            }
        };
        reader.readAsBinaryString(target.files[0]);
    }

    excelDateToJSDate(serial) {
        const utc_days = Math.floor(serial - 25569) + 1;
        const utc_value = utc_days * 86400;
        const date_info = new Date(utc_value * 1000);
        const fractional_day = serial - Math.floor(serial) + 0.0000001;
        let total_seconds = Math.floor(86400 * fractional_day);
        const seconds = total_seconds % 60;
        total_seconds -= seconds;
        const hours = Math.floor(total_seconds / (60 * 60));
        const minutes = Math.floor(total_seconds / 60) % 60;
        return new Date(date_info.getFullYear(), date_info.getMonth(), date_info.getDate(), hours, minutes, seconds);
    }

    excelDatetoAge(serial) {
        const utc_days = Math.floor(serial - 25569) + 1;
        const utc_value = utc_days * 86400;
        const birthday = new Date(utc_value * 1000);
        const ageDate = new Date(Date.now() - birthday.getTime());
        return Math.abs(ageDate.getUTCFullYear() - 1970);
    }

    getSchoolStudents() {
        if (!!this.teacher && !!this.school) {
            this.classroomService
                .getTeacherClassroomUnitsAndStudents({ user_id: this.teacher.id, school_id: this.school.school_id })
                .then((res) => {
                    this.schoolStudents = res.data.results.schoolStudents;
                    this.schoolTeachers = res.data.results.schoolTeachers;
                });
        }
    }

    openModal(modalType) {
        if (modalType === "getLink") {
            this.generateLink(this.getLinkForm);
        }
        this.modalHeader = "Add Students";
        this.modalType = modalType;
        this.classroomService
            .getTeacherClassrooms({ user_id: this.teacher.id, school_id: this.school.school.id })
            .then((res) => {
                this.classrooms = res.data.results;
                this.formModal.nativeElement.className = "modal fade show";
            });
    }

    closeModal() {
        this.formModal.nativeElement.className = "modal hide";
    }

    generateLink(form) {
        let classroomId = "";
        if (form.value.classroom != -1) {
            classroomId = this.classrooms[form.value.classroom].id;
        }

        const params = {
            user_id: this.teacher.id,
            type: "generateWordlink",
            params: {
                school_id: this.school.school.id,
                classroom_id: classroomId,
            },
        };
        this.classroomService
            .updateClassroomData(params)
            .then((res) => {
                if (res.data.status) {
                    this.wordlink = environment.ROOT + "classroom/" + res.data.results.wordlink;
                } else {
                    this.snackbarService.showSnackbar({ status: false, msg: res.data.message });
                }
            })
            .catch((err) => {
                console.error(err);
            })
            .finally(() => {
                this.setLoader(false);
            });
    }

    copyInputMessage() {
        //half a second allows selection to occur prior to copy command.
        setTimeout(() => {
            document.execCommand("copy");
        }, 500);
    }
}
