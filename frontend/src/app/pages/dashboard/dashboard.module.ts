import { NgModule } from "@angular/core";
import { CommonModule } from "@angular/common";
import { RouterModule } from "@angular/router";

import { DashboardComponent } from "./dashboard/dashboard.component";
import { PartialsModule } from "app/_partials/partials.module";

@NgModule({
    declarations: [DashboardComponent],
    imports: [CommonModule, RouterModule, PartialsModule],
})
export class DashboardModule {}
