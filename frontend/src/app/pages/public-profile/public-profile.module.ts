import { NgModule } from "@angular/core";
import { CommonModule } from "@angular/common";

import { PublicProfileComponent } from "./public-profile/public-profile.component";
import { PipesModule } from "app/_pipes/pipes.module";
import { PartialsModule } from "app/_partials/partials.module";

@NgModule({
    declarations: [PublicProfileComponent],
    imports: [CommonModule, PipesModule, PartialsModule],
})
export class PublicProfileModule {}
