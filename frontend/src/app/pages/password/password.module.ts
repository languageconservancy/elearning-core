import { NgModule } from "@angular/core";
import { CommonModule } from "@angular/common";
import { FormsModule, ReactiveFormsModule } from "@angular/forms";
import { RouterModule } from "@angular/router";

import { ChangePasswordComponent } from "./change-password/change-password.component";
import { ForgotPasswordComponent } from "./forgot-password/forgot-password.component";
import { PartialsModule } from "app/_partials/partials.module";

@NgModule({
    declarations: [ChangePasswordComponent, ForgotPasswordComponent],
    imports: [CommonModule, RouterModule, FormsModule, ReactiveFormsModule, PartialsModule],
})
export class PasswordModule {}
