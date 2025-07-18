/* eslint-disable @typescript-eslint/unbound-method */
import { Component, OnInit } from "@angular/core";
import { UntypedFormGroup, UntypedFormControl, Validators } from "@angular/forms";
import { Router } from "@angular/router";
import { CookieService } from "app/_services/cookie.service";

import { SettingsService } from "app/_services/settings.service";
import { LocalStorageService } from "app/_services/local-storage.service";

declare let jQuery: any;

@Component({
    selector: "app-parental-lock",
    templateUrl: "./parental-lock.component.html",
    styleUrls: ["./parental-lock.component.scss"],
})
export class ParentalLockComponent implements OnInit {
    public parentalLockInputForm: UntypedFormGroup;
    public user: any;

    constructor(
        private settingsService: SettingsService,
        private router: Router,
        private cookieService: CookieService,
        private localStorage: LocalStorageService,
    ) {
        this.cookieService
            .get("AuthUser")
            .then((value) => {
                if (value == "") {
                    throw value;
                }
                this.user = JSON.parse(value);
            })
            .catch((err) => {
                console.warn("No AuthUser cookie", err);
            });
    }

    ngOnInit() {
        this.parentalLockInputForm = new UntypedFormGroup({
            parentalLock: new UntypedFormControl("", [Validators.required, this.validateParentalCode.bind(this)]),
        });
        jQuery("#parentalLockInput").modal("show");
    }

    private validateParentalCode(control: UntypedFormControl): any {
        if (this.parentalLockInputForm) {
            return control.value === this.user.usersetting.parental_lock ? null : { wrongCode: true };
        }
    }

    backToDashboard() {
        jQuery("#parentalLockInput").modal("hide");
        void this.router.navigate(["dashboard"]);
    }

    checkParentalLock(form) {
        if (form.valid) {
            this.localStorage.setItem("parentalLockCode", form.value.parentalLock);
            this.settingsService.parentalLockInput(form.value.parentalLock);
            jQuery("#parentalLockInput").modal("hide");
        }
    }
}
