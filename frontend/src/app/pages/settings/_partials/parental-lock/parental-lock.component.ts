/* eslint-disable @typescript-eslint/unbound-method */
import { ChangeDetectionStrategy, Component, inject, OnInit } from "@angular/core";
import {
    UntypedFormGroup,
    UntypedFormControl,
    ReactiveFormsModule,
    Validators,
} from "@angular/forms";
import { Router } from "@angular/router";
import { CookieService } from "@app/_services/cookie.service";
import { ModalService } from "@app/_services/modal.service";

import { SettingsService } from "@app/_services/settings.service";
import { LocalStorageService } from "@app/_services/local-storage.service";

@Component({
    selector: "app-parental-lock",
    changeDetection: ChangeDetectionStrategy.Eager,
    templateUrl: "./parental-lock.component.html",
    styleUrls: ["./parental-lock.component.scss"],
    imports: [ReactiveFormsModule],
    standalone: true,
})
export class ParentalLockComponent implements OnInit {
    private modalService = inject(ModalService);
    private router = inject(Router);
    private cookieService = inject(CookieService);
    private localStorage = inject(LocalStorageService);
    private settingsService = inject(SettingsService);

    public parentalLockInputForm: UntypedFormGroup;
    public user: any;

    constructor() {
        this.cookieService
            .get("AuthUser")
            .then((value) => {
                if (value == "") {
                    throw new Error(value);
                }
                this.user = JSON.parse(value);
            })
            .catch((err) => {
                console.warn("No AuthUser cookie", err);
            });
    }

    ngOnInit() {
        this.parentalLockInputForm = new UntypedFormGroup({
            parentalLock: new UntypedFormControl("", [
                Validators.required,
                this.validateParentalCode.bind(this),
            ]),
        });
        this.modalService.openModal("parentalLockInput");
    }

    private validateParentalCode(control: UntypedFormControl): any {
        if (this.parentalLockInputForm) {
            return control.value === this.user.usersetting.parental_lock
                ? null
                : { wrongCode: true };
        }
    }

    backToDashboard() {
        this.modalService.closeModal("parentalLockInput");
        void this.router.navigate(["dashboard"]);
    }

    checkParentalLock(form) {
        if (form.valid) {
            this.localStorage.setItem("parentalLockCode", form.value.parentalLock);
            this.settingsService.parentalLockInput(form.value.parentalLock);
            this.modalService.closeModal("parentalLockInput");
        }
    }
}
