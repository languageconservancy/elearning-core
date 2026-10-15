import { NgModule } from "@angular/core";
import { CommonModule } from "@angular/common";

import { PublicProfileComponent } from "./public-profile/public-profile.component";
import { PipesModule } from "@app/_pipes/pipes.module";
import { PartialsModule } from "@app/_partials/partials.module";

@NgModule({
    imports: [CommonModule, PipesModule, PartialsModule, PublicProfileComponent],
})
export class PublicProfileModule {}
