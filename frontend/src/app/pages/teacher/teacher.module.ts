import { NgModule } from "@angular/core";
import { CommonModule } from "@angular/common";
import { FormsModule, ReactiveFormsModule } from "@angular/forms";
import { RouterModule } from "@angular/router";
import { MatTableModule } from "@angular/material/table";
import { MatSortModule } from "@angular/material/sort";
import { DragDropModule } from "@angular/cdk/drag-drop";
import { NgxWigModule } from "ngx-wig";

import { TeacherNavComponent } from "./teacher-nav/teacher-nav.component";
import { TeacherDashboardComponent } from "./teacher-dashboard/teacher-dashboard.component";
import { TeacherClassroomsComponent } from "./teacher-classrooms/teacher-classrooms.component";
import { TeacherLessonsComponent } from "./teacher-lessons/teacher-lessons.component";
import { ClassroomSelectComponent } from "./_partials/classroom-select/classroom-select.component";
import { TeacherAdminComponent } from "./teacher-admin/teacher-admin.component";
import { PartialsModule } from "app/_partials/partials.module";
import { PipesModule } from "app/_pipes/pipes.module";

@NgModule({
    imports: [
        CommonModule,
        FormsModule,
        ReactiveFormsModule,
        RouterModule,
        PipesModule,
        PartialsModule,
        MatTableModule,
        MatSortModule,
        DragDropModule,
        NgxWigModule,
    ],
    declarations: [
        TeacherNavComponent,
        TeacherDashboardComponent,
        TeacherClassroomsComponent,
        TeacherLessonsComponent,
        ClassroomSelectComponent,
        TeacherAdminComponent,
    ],
})
export class TeacherModule {}
