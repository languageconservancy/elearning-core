// Angular libraries
import { BrowserModule } from "@angular/platform-browser";
import { BrowserAnimationsModule } from "@angular/platform-browser/animations";
import { FormsModule, ReactiveFormsModule } from "@angular/forms";
import { HttpClientModule } from "@angular/common/http";
import { APP_INITIALIZER, NgModule } from "@angular/core";

// Third Party Addons
import { CookieService as NgCookieService } from "ngx-cookie-service";
import { DragDropModule } from "@angular/cdk/drag-drop";
import { NgxWigModule } from "ngx-wig";
import { MatTableModule } from "@angular/material/table";
import { MatSortModule } from "@angular/material/sort";

// Routing
import { AppRoutingModule } from "./app-routing.module";

// Guards
import {
    MaintenanceModeCheckGuard,
    MaintenanceModeGuard,
} from "./_guards/maintenance-mode-check.guard";
import { AuthGuard, RegProgGuard } from "./_guards/auth.guard";

// Components
import { AppComponent } from "./app.component";
import { FooterComponent } from "./_partials/footer/footer.component";
import { InfoSpaceComponent } from "./_partials/info-space/info-space.component";
import { LoaderComponent } from "./_partials/loader/loader.component";
import { MaintenanceModeComponent } from "./_plugins/maintenance-mode/maintenance-mode.component";
import { NavbarComponent } from "./_partials/navbar/navbar.component";
import { NotFoundComponent } from "./_plugins/not-found/not-found.component";
import { SnackbarComponent } from "./_partials/snackbar/snackbar.component";

// Modules
// page modules
import { AboutModule } from "./pages/about/about.module";
import { ContactUsModule } from "./pages/contact-us/contact-us.module";
import { DashboardModule } from "./pages/dashboard/dashboard.module";
import { ForumModule } from "./pages/forum/forum.module";
import { LearningModule } from "./pages/learning/learning.module";
import { LearningPathModule } from "./pages/learning-path/learning-path.module";
import { LoginModule } from "./pages/login/login.module";
import { PasswordModule } from "./pages/password/password.module";
import { ProgressModule } from "./pages/progress/progress.module";
import { ReviewModule } from "./pages/review/review.module";
import { SettingsModule } from "./pages/settings/settings.module";
import { SignupModule } from "./pages/signup/signup.module";
import { TeacherModule } from "./pages/teacher/teacher.module";
// other modules
import { PartialsModule } from "./_partials/partials.module";
import { PipesModule } from "./_pipes/pipes.module";
// shared modules
import { ExercisesModule } from "./shared/exercises/exercises.module";
import { PublicProfileModule } from "./pages/public-profile/public-profile.module";

// Services
import { BaseService } from "./_services/base.service";
import { FindFriendsService } from "./_services/find-friends.service";
import { LearningSpeedService } from "./_services/learning-speed.service";
import { LearningPathService } from "./_services/learning-path.service";
import { LessonsService } from "./_services/lessons.service";
import { Loader } from "./_services/loader.service";
import { LocalStorageService } from "./_services/local-storage.service";
import { LoginService } from "./_services/login.service";
import { RegistrationService } from "./_services/registration.service";
import { ResetPasswordService } from "./_services/reset-password.service";
import { ReviewService } from "./_services/review.service";
import { SettingsService } from "./_services/settings.service";
import { ForumService } from "./_services/forum.service";
import { ProgressService } from "./_services/progress.service";
import { BadgeService } from "./_services/badge.service";
import { TeacherService } from "./_services/teacher.service";
import { ClassroomService } from "./_services/classroom.service";
import { AudioService } from "./_services/audio.service";
import { CookieService } from "./_services/cookie.service";
import { VirtualKeyboardService } from "./_services/virtual-keyboard.service";
import { AppAutoFocusDirective } from "./_directives/app-auto-focus.directive";
import { SiteSettingsService } from "./_services/site-settings.service";
import { PlatformRolesService } from "./_services/platform-roles.service";

export function initializeApp(
    siteSettingsService: SiteSettingsService,
    platformRolesService: PlatformRolesService,
) {
    return async (): Promise<any> => {
        try {
            await Promise.all([
                siteSettingsService.fetchSettings(),
                siteSettingsService.fetchFeatures(),
                platformRolesService.fetchPlatformRoles(),
            ]);
            console.log("App initialization complete");
        } catch (error) {
            console.error("App initialization error", error);
        }
    };
}

@NgModule({
    declarations: [
        AppComponent,
        LoaderComponent,
        SnackbarComponent,
        MaintenanceModeComponent,
        NavbarComponent,
        NotFoundComponent,
        FooterComponent,
        InfoSpaceComponent,
        AppAutoFocusDirective,
    ],
    imports: [
        AppRoutingModule,
        BrowserModule,
        BrowserAnimationsModule,
        FormsModule,
        ReactiveFormsModule,
        HttpClientModule,
        DragDropModule,
        MatTableModule,
        MatSortModule,
        NgxWigModule,
        AboutModule,
        ContactUsModule,
        DashboardModule,
        ForumModule,
        LearningModule,
        LearningPathModule,
        LoginModule,
        PasswordModule,
        ProgressModule,
        ReviewModule,
        SettingsModule,
        SignupModule,
        TeacherModule,
        PartialsModule,
        PipesModule,
        ExercisesModule,
        PublicProfileModule,
    ],
    providers: [
        {
            provide: APP_INITIALIZER,
            useFactory: initializeApp,
            deps: [SiteSettingsService, PlatformRolesService],
            multi: true,
        },
        AuthGuard,
        RegProgGuard,
        MaintenanceModeGuard,
        MaintenanceModeCheckGuard,
        BaseService,
        NgCookieService,
        CookieService,
        FindFriendsService,
        LearningPathService,
        LearningSpeedService,
        LessonsService,
        TeacherService,
        ClassroomService,
        Loader,
        LocalStorageService,
        LoginService,
        RegistrationService,
        ResetPasswordService,
        ReviewService,
        SettingsService,
        ForumService,
        ProgressService,
        BadgeService,
        AudioService,
        VirtualKeyboardService,
    ],
    bootstrap: [AppComponent],
})
export class AppModule {}
