import { NgModule } from "@angular/core";
import { CommonModule } from "@angular/common";
import { FormsModule, ReactiveFormsModule } from "@angular/forms";
import { RouterModule } from "@angular/router";

import { ContactUsComponent } from "./contact-us/contact-us.component";
import { PartialsModule } from "app/_partials/partials.module";
import { DirectivesModule } from "app/_directives/directives.module";

@NgModule({
    declarations: [ContactUsComponent],
    imports: [CommonModule, FormsModule, ReactiveFormsModule, RouterModule, PartialsModule, DirectivesModule],
})
export class ContactUsModule {}
