import { NgModule } from "@angular/core";
import { CommonModule } from "@angular/common";
import { RouterModule } from "@angular/router";

import { ClassroomClassesComponent } from "./classroom/classroom-classes/classroom-classes.component";
import { ClassroomUnitsComponent } from "./classroom/classroom-units/classroom-units.component";
import { LevelsComponent } from "./levels/levels.component";
import { UnitsComponent } from "./units/units.component";
import { PartialsModule } from "app/_partials/partials.module";

@NgModule({
    declarations: [ClassroomClassesComponent, ClassroomUnitsComponent, LevelsComponent, UnitsComponent],
    imports: [CommonModule, RouterModule, PartialsModule],
})
export class LearningPathModule {}
