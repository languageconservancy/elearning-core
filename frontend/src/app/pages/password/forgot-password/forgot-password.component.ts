/* eslint-disable @typescript-eslint/unbound-method */
import { Component, OnDestroy, OnInit } from "@angular/core";
import { UntypedFormGroup, UntypedFormControl, Validators } from "@angular/forms";

import { ResetPasswordService } from "app/_services/reset-password.service";
import { Loader } from "app/_services/loader.service";
import { SnackbarService } from "app/_services/snackbar.service";

@Component({
    selector: "app-forgot-password",
    templateUrl: "./forgot-password.component.html",
    styleUrls: ["./forgot-password.component.scss"],
})
export class ForgotPasswordComponent implements OnInit, OnDestroy {
    public resetForm: UntypedFormGroup;
    public passwordReset: boolean = false;
    public passMsg: string = "";

    constructor(
        private resetPasswordService: ResetPasswordService,
        private loader: Loader,
        private snackbarService: SnackbarService,
    ) {}

    ngOnInit() {
        const emailRegex = `[A-Za-z0-9_]+([\.-]?[A-Za-z0-9_]+)*@[A-Za-z0-9_]+([\.-]?[A-Za-z0-9_]+)*(\.[A-Za-z_]{2,3})+`;
        this.resetForm = new UntypedFormGroup({
            email: new UntypedFormControl("", [Validators.required, Validators.pattern(emailRegex)]),
        });

        //hide the mobile overlay and always allow reset password functionality
        this.setOverlay("none");
    }

    ngOnDestroy() {
        //when finished, restore the mobile overlay
        this.setOverlay("block");
    }

    public setOverlay(val) {
        const overlay = document.getElementById("mobile_overlay");
        if (overlay) {
            overlay.style.display = val;
        }
    }

    private setLoader(val: boolean) {
        this.loader.setLoader(val);
    }

    reset(form) {
        if (form.valid) {
            this.setLoader(true);
            this.resetPasswordService
                .submitForgotPassword({ email: form.value.email })
                .then((res) => {
                    this.setLoader(false);
                    if (res.data.status) {
                        this.passwordReset = true;
                        this.passMsg = res.data.message;
                    } else {
                        this.snackbarService.showSnackbar({ status: false, msg: res.data.message });
                    }
                })
                .catch((err) => {
                    this.setLoader(false);
                    this.snackbarService.showSnackbar({ status: false, msg: err.data.message });
                });
        } else {
            this.snackbarService.showSnackbar({
                status: false,
                msg: "Please enter your email before moving forward.",
            });
        }
    }
}
