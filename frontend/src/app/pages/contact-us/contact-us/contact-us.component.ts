/* eslint-disable @typescript-eslint/unbound-method */
import { Component, OnInit, ViewChild } from "@angular/core";
import { UntypedFormGroup, UntypedFormControl, Validators } from "@angular/forms";
import { Loader } from "app/_services/loader.service";
import { RegistrationService } from "app/_services/registration.service";
import { Router } from "@angular/router";
import { SnackbarService } from "app/_services/snackbar.service";
import { environment } from "environments/environment";
import { VirtualKeyboardComponent } from "app/_partials/virtual-keyboard/virtual-keyboard.component";

@Component({
    selector: "app-contact-us",
    templateUrl: "./contact-us.component.html",
    styleUrls: ["./contact-us.component.scss"],
})
export class ContactUsComponent implements OnInit {
    public contactUsForm: any;
    public issueList: any = [
        { name: "Bug Report" },
        { name: "Feature Suggestion/Feedback" },
        { name: "Translation Errors/Course Mistakes" },
        { name: "Lost Data" },
        { name: "Other Support Request" },
        { name: "Data and Privacy" },
        { name: "Billings & Payment" },
    ];
    public passwordReset: boolean = false;
    public errorMsg: string = "";
    public passMsg: string = "";
    public environment = environment;
    public inputs: any = {
        problemdetails: "",
    };
    @ViewChild("virtualKeyboard") virtualKeyboard: VirtualKeyboardComponent;

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

        this.contactUsForm = new UntypedFormGroup({
            name: new UntypedFormControl("", [
                Validators.required,
                this.validateBlankValue.bind(this),
            ]),
            email: new UntypedFormControl("", [
                Validators.required,
                Validators.pattern(emailRegex),
                this.validateBlankValue.bind(this),
            ]),
            problemdetails: new UntypedFormControl("", [
                Validators.required,
                this.validateBlankValue.bind(this),
            ]),
            issue: new UntypedFormControl("", [
                Validators.required,
                this.validateBlankValue.bind(this),
            ]),
        });

        this.contactUsForm.controls["problemdetails"].valueChanges.subscribe((value) => {
            this.inputs["problemdetails"] = value;
        });
    }
    private validateBlankValue(control: UntypedFormControl): any {
        if (this.contactUsForm) {
            return control.value.trim() === "" ? { emptyValue: true } : null;
        }
    }
    contactUsSend(form) {
        if (form.valid) {
            const data = {
                name: form.value.name,
                email: form.value.email,
                problemdetails: form.value.problemdetails,
                issue: form.value.issue,
            };
            this.setLoader(true);
            this.doRegister(data);
        } else {
            this.snackbarService.showSnackbar({
                status: false,
                msg: "Please fill in all the fields with valid details before clicking the send button",
            });
        }
    }
    urlLink(url: any) {
        void this.router.navigate(["about/" + url]);
    }

    formReset() {
        this.contactUsForm.patchValue({
            name: "",
            email: "",
            problemdetails: "",
            issue: "",
        });
    }

    doRegister(data) {
        this.registrationService
            .sendContactUs(data)
            .then((res: any) => {
                this.snackbarService.showSnackbar({
                    status: res.data.status,
                    msg: res.data.message,
                });
                this.setLoader(false);
                if (res.data.status) {
                    this.formReset();
                }
            })
            .catch(() => {
                this.snackbarService.showSnackbar({
                    status: false,
                    msg: "Server error.",
                });
                this.setLoader(false);
            });
    }

    showVirtualKeyboard() {
        if (this.virtualKeyboard) {
            this.virtualKeyboard.show();
        }
    }

    hideVirtualKeyboard() {
        if (this.virtualKeyboard) {
            this.virtualKeyboard.hide();
        }
    }

    /**
     * Updates the active input that the virtual keyboard is focused on and updating.
     * @param event - Event on input focus.
     */
    setActiveInput(event: Event): void {
        if (this.virtualKeyboard) {
            this.virtualKeyboard.onInputFocus(event);
        }
    }

    /**
     * Updates the form control value when the virtual keyboard emits an input value change event.
     * This creates two-way binding between the virtual keyboard and the form control.
     * This method is used since form controls are one-way and ngModel is not intended to be used in conjunction
     * with reactive forms (form control).
     * @param event - Event emitted from virtual keyboard component containing the input name and value.
     */
    onInputValueChange(event: { inputName: string; value: string }): void {
        this.contactUsForm.get(event.inputName)?.setValue(event.value, { emitEvent: false });
    }

    virtualKeyPressed(key: string) {
        if (key === "{enter}") {
            this.contactUsSend(this.contactUsForm);
        }
    }

    physicalKeyPressed(key: string) {
        if (key === "Enter") {
            this.contactUsSend(this.contactUsForm);
        }
    }
}
