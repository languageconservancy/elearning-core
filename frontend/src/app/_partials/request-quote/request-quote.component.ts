/* eslint-disable @typescript-eslint/unbound-method */
import { Component, OnInit } from "@angular/core";
import { UntypedFormGroup, UntypedFormControl, Validators } from "@angular/forms";
import { Router } from "@angular/router";

import { Loader } from "app/_services/loader.service";
import { RegistrationService } from "app/_services/registration.service";
import { SnackbarService } from "app/_services/snackbar.service";
import { environment } from "environments/environment";

@Component({
    selector: "app-request-quote",
    templateUrl: "./request-quote.component.html",
    styleUrls: ["./request-quote.component.scss"],
})
export class RequestQuoteComponent implements OnInit {
    public environment = environment;
    public quoteForm: any;
    public techList: any = [
        {
            name: "MacOS",
            value: "MacOS",
        },
        {
            name: "Windows",
            value: "Windows",
        },
        {
            name: "iOS",
            value: "iOS",
        },
        {
            name: "Android",
            value: "Android",
        },
        {
            name: "Chrome Book",
            value: "ChromeBook",
        },
        {
            name: "Other",
            value: "Other",
        },
    ];

    constructor(
        public registrationService: RegistrationService,
        private loader: Loader,
        private router: Router,
        private snackbarService: SnackbarService,
    ) {
        window.scroll(0, 0);
    }

    private setLoader(val: boolean) {
        this.loader.setLoader(val);
    }
    ngOnInit() {
        const emailRegex = `[A-Za-z0-9_]+([\.-]?[A-Za-z0-9_]+)*@[A-Za-z0-9_]+([\.-]?[A-Za-z0-9_]+)*(\.[A-Za-z_]{2,3})+`;
        const phoneRegex = `^\D?(\d{3})\D?\D?(\d{3})\D?(\d{4})$`;

        this.quoteForm = new UntypedFormGroup({
            adminName: new UntypedFormControl("", [Validators.required, this.validateBlankValue.bind(this)]),
            adminPhone: new UntypedFormControl("", [
                Validators.required,
                Validators.pattern(phoneRegex),
                this.validateBlankValue.bind(this),
            ]),
            adminEmail: new UntypedFormControl("", [
                Validators.required,
                Validators.pattern(emailRegex),
                this.validateBlankValue.bind(this),
            ]),
            techName: new UntypedFormControl("", [Validators.required, this.validateBlankValue.bind(this)]),
            techPhone: new UntypedFormControl("", [
                Validators.required,
                Validators.pattern(phoneRegex),
                this.validateBlankValue.bind(this),
            ]),
            techEmail: new UntypedFormControl("", [
                Validators.required,
                Validators.pattern(emailRegex),
                this.validateBlankValue.bind(this),
            ]),
            schoolName: new UntypedFormControl("", [Validators.required, this.validateBlankValue.bind(this)]),
            schoolPhone: new UntypedFormControl("", [
                Validators.required,
                Validators.pattern(phoneRegex),
                this.validateBlankValue.bind(this),
            ]),
            schoolEmail: new UntypedFormControl("", [
                Validators.required,
                Validators.pattern(emailRegex),
                this.validateBlankValue.bind(this),
            ]),
            schoolAddress: new UntypedFormControl("", [Validators.required, this.validateBlankValue.bind(this)]),
            schoolCity: new UntypedFormControl("", [Validators.required, this.validateBlankValue.bind(this)]),
            schoolState: new UntypedFormControl("", [Validators.required, this.validateBlankValue.bind(this)]),
            numSchools: new UntypedFormControl("", [Validators.required, this.validateBlankValue.bind(this)]),
            numStudents: new UntypedFormControl("", [Validators.required, this.validateBlankValue.bind(this)]),
            numTeachers: new UntypedFormControl("", [Validators.required, this.validateBlankValue.bind(this)]),
            startDate: new UntypedFormControl("", [Validators.required, this.validateBlankValue.bind(this)]),
        });
    }
    private validateBlankValue(control: UntypedFormControl): any {
        if (this.quoteForm) {
            return control.value.trim() === "" ? { emptyValue: true } : null;
        }
    }
    quoteSend(form) {
        if (form.valid) {
            const data = {
                adminName: form.value.adminName,
                adminEmail: form.value.adminEmail,
                adminPhone: form.value.adminPhone,
                techName: form.value.techName,
                techEmail: form.value.techEmail,
                techPhone: form.value.techPhone,
                schoolName: form.value.schoolName,
                schoolWebsite: form.value.schoolEmail,
                schoolPhone: form.value.schoolPhone,
                schoolAddress: form.value.schoolAddress,
                schoolCity: form.value.schoolCity,
                schoolState: form.value.schoolState,
                schoolZip: form.value.schoolZip,
                numSchools: form.value.numSchools,
                numStudents: form.value.numStudents,
                numTeachers: form.value.numTeachers,
                startDate: form.value.startDate,
                studentLocation: form.value.studentLocation,
                studentTechNotes: form.value.studentTechNotes,
                MacOS: form.value.MacOS,
                Windows: form.value.Windows,
                Android: form.value.Android,
                iOS: form.value.iOS,
                ChromeBook: form.value.ChromeBook,
                Other: form.value.Other,
            };
            this.setLoader(true);
            this.doRequest(data);
        } else {
            this.snackbarService.showSnackbar({
                status: false,
                msg: "Please fill in all the fields with valid details before clicking the send button.",
            });
        }
    }
    rullink(url: any) {
        void this.router.navigate(["about/" + url]);
    }

    formreset() {
        this.quoteForm.patchValue({
            adminName: "",
            adminEmail: "",
            adminPhone: "",
            techName: "",
            techEmail: "",
            techPhone: "",
            schoolName: "",
            schoolEmail: "",
            schoolPhone: "",
            schoolAddress: "",
            numSchools: "",
            numStudents: "",
            numTeachers: "",
            startDate: "",
            studentTech: "",
            studentLocation: "",
            studentTechNotes: "",
            additionalNotes: "",
        });
    }

    doRequest(data) {
        this.registrationService
            .sendContactUs(data)
            .then((res: any) => {
                this.setLoader(false);
                this.snackbarService.showSnackbar({
                    status: res.data.status,
                    msg: res.data.message,
                });
                if (res.data.status) {
                    this.formreset();
                }
            })
            .catch(() => {
                this.snackbarService.showSnackbar({ status: false, msg: "Server error" });
                this.setLoader(false);
            });
    }
}
