import { NgModule } from "@angular/core";
import { CommonModule } from "@angular/common";
import { RouterModule } from "@angular/router";
import { AboutComponent } from "./about/about.component";
import { PipesModule } from "app/_pipes/pipes.module";
import { PartialsModule } from "app/_partials/partials.module";

@NgModule({
    declarations: [AboutComponent],
    imports: [CommonModule, RouterModule, PipesModule, PartialsModule],
})
export class AboutModule {}
