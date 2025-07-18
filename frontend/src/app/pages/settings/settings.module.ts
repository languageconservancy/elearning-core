import { NgModule } from "@angular/core";
import { CommonModule } from "@angular/common";
import { FormsModule, ReactiveFormsModule } from "@angular/forms";
import { RouterModule } from "@angular/router";

import { AccessibilityComponent } from "./accessibility/accessibility.component";
import { AccountComponent } from "./account/account.component";
import { GalleryComponent } from "./_partials/gallery/gallery.component";
import { LearningComponent } from "./learning/learning.component";
import { NotificationsComponent } from "./notifications/notifications.component";
import { ParentalLockComponent } from "./_partials/parental-lock/parental-lock.component";
import { PrivacyComponent } from "./privacy/privacy.component";
import { ProfileComponent } from "./profile/profile.component";
import { SettingSidebarComponent } from "./_partials/setting-sidebar/setting-sidebar.component";
import { PipesModule } from "app/_pipes/pipes.module";
import { PartialsModule } from "app/_partials/partials.module";
import { SettingsComponent } from "./settings/settings.component";

@NgModule({
    imports: [
        CommonModule,
        FormsModule,
        ReactiveFormsModule,
        RouterModule,
        PipesModule,
        PartialsModule,
    ],
    declarations: [
        AccessibilityComponent,
        AccountComponent,
        GalleryComponent,
        LearningComponent,
        NotificationsComponent,
        ParentalLockComponent,
        PrivacyComponent,
        ProfileComponent,
        SettingSidebarComponent,
        SettingsComponent,
    ],
})
export class SettingsModule {}
