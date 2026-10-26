import { NgModule } from "@angular/core";
import { CommonModule } from "@angular/common";
import { FormsModule } from "@angular/forms";
import { RouterModule } from "@angular/router";

import { LeaderBoardComponent } from "@app/pages/progress/leader-board/leader-board.component";
import { YourProgressComponent } from "@app/pages/progress/your-progress/your-progress.component";
import { ProgressLeftPanelComponent } from "@app/pages/progress/_partials/progress-left-panel/progress-left-panel.component";
import { PartialsModule } from "@app/_partials/partials.module";

@NgModule({
    imports: [
        CommonModule,
        FormsModule,
        LeaderBoardComponent,
        RouterModule,
        PartialsModule,
        ProgressLeftPanelComponent,
        YourProgressComponent,
    ],
})
export class ProgressModule {}
