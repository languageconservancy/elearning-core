import { NgModule } from "@angular/core";
import { CommonModule } from "@angular/common";
import { FormsModule, ReactiveFormsModule } from "@angular/forms";
import { SocialLoginModule } from "@abacritt/angularx-social-login";
import { RecaptchaModule } from "ng-recaptcha";
import { RouterModule } from "@angular/router";

import { FindFriendsComponent } from "./find-friends/find-friends.component";
import { LearningPathComponent } from "./learning-path/learning-path.component";
import { LearningSpeedComponent } from "./learning-speed/learning-speed.component";
import { RegistrationComponent } from "./registration/registration.component";
import { SpreadTheWordComponent } from "./spread-the-word/spread-the-word.component";
import { PartialsModule } from "app/_partials/partials.module";
import { PipesModule } from "app/_pipes/pipes.module";

@NgModule({
    declarations: [
        FindFriendsComponent,
        LearningPathComponent,
        LearningSpeedComponent,
        RegistrationComponent,
        SpreadTheWordComponent,
    ],
    imports: [
        CommonModule,
        FormsModule,
        ReactiveFormsModule,
        SocialLoginModule,
        RecaptchaModule,
        RouterModule,
        PartialsModule,
        PipesModule,
    ],
})
export class SignupModule {}
