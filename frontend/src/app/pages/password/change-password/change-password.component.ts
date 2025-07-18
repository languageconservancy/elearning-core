/* eslint-disable @typescript-eslint/unbound-method */
import { Component, OnInit, OnDestroy } from "@angular/core";
import { UntypedFormGroup, UntypedFormControl, Validators } from "@angular/forms";
import { ActivatedRoute } from "@angular/router";
import { Subscription } from "rxjs";

import { Loader } from "app/_services/loader.service";
import { ResetPasswordService } from "app/_services/reset-password.service";
import { SnackbarService } from "app/_services/snackbar.service";

@Component({
    selector: "app-change-password",
    templateUrl: "./change-password.component.html",
    styleUrls: ["./change-password.component.scss"],
})
export class ChangePasswordComponent implements OnInit, OnDestroy {
    private tokenSubscription: Subscription;
    public changeForm: UntypedFormGroup;
    public passwordReset: boolean = false;
    public passMsg: string = "";
    public passwordToken: string = "";

    constructor(
        private loader: Loader,
        private route: ActivatedRoute,
        private resetPassService: ResetPasswordService,
        private snackbarService: SnackbarService,
    ) {
        this.tokenSubscription = this.route.params.subscribe((params) => {
            this.passwordToken = params.token;
        });
    }

    ngOnInit() {
        this.changeForm = new UntypedFormGroup({
            password: new UntypedFormControl("", Validators.required),
            confirmpassword: new UntypedFormControl("", [
                Validators.required,
                this.validatePasswordConfirmation.bind(this),
            ]),
        });

        //hide the mobile overlay and always allow change password functionality
        this.setOverlay("none");
    }

    ngOnDestroy() {
        this.tokenSubscription.unsubscribe();

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

    private validatePasswordConfirmation(control: UntypedFormControl): any {
        if (this.changeForm) {
            return control.value === this.changeForm.get("password").value ? null : { notSame: true };
        }
    }

    changePassword(form) {
        if (form.valid) {
            this.setLoader(true);
            const data = {
                token: this.passwordToken,
                new_password: form.value.password,
            };
            this.resetPassService
                .changePassword(data)
                .then((res: any) => {
                    if (res.data.status) {
                        this.passwordReset = true;
                        this.passMsg = res.data.message;
                    } else {
                        this.snackbarService.showSnackbar({ status: false, msg: res.data.message });
                    }
                })
                .catch(() => {
                    this.snackbarService.showSnackbar({
                        status: false,
                        msg: "There has been an error. Please try again after some time while we fix it.",
                    });
                })
                .finally(() => {
                    this.setLoader(false);
                });
        } else {
            this.snackbarService.showSnackbar({
                status: false,
                msg: "Please fill in all fields with valid data before moving forward.",
            });
        }
    }
}
