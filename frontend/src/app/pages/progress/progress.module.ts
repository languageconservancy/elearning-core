import { NgModule } from "@angular/core";
import { CommonModule } from "@angular/common";
import { FormsModule } from "@angular/forms";
import { RouterModule } from "@angular/router";

import { LeaderBoardComponent } from "./leader-board/leader-board.component";
import { YourProgressComponent } from "./your-progress/your-progress.component";
import { ProgressLeftPanelComponent } from "./_partials/progress-left-panel/progress-left-panel.component";
import { PartialsModule } from "app/_partials/partials.module";

@NgModule({
    declarations: [LeaderBoardComponent, YourProgressComponent, ProgressLeftPanelComponent],
    imports: [CommonModule, FormsModule, RouterModule, PartialsModule],
})
export class ProgressModule {}
