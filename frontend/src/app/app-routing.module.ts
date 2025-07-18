import { NgModule } from "@angular/core";
import { RouterModule, Routes } from "@angular/router";

import { MaintenanceModeCheckGuard, MaintenanceModeGuard } from "app/_guards/maintenance-mode-check.guard";
import { AuthGuard, RegProgGuard } from "app/_guards/auth.guard";
import { FeatureToggleGuard } from "app/_guards/feature-toggle.guard";
// import { CardsComponent } from 'app/pages/learning/cards/cards.component';
import { UnitComponent } from "./pages/learning/unit/unit.component";
import { ChangePasswordComponent } from "app/pages/password/change-password/change-password.component";
import { DashboardComponent } from "app/pages/dashboard/dashboard/dashboard.component";
import { SpreadTheWordComponent } from "app/pages/signup/spread-the-word/spread-the-word.component";
import { FindFriendsComponent } from "app/pages/signup/find-friends/find-friends.component";
import { LearningSpeedComponent } from "app/pages/signup/learning-speed/learning-speed.component";
import { LearningPathComponent } from "app/pages/signup/learning-path/learning-path.component";
import { LoginComponent } from "app/pages/login/login/login.component";
import { MaintenanceModeComponent } from "app/_plugins/maintenance-mode/maintenance-mode.component";
import { NotFoundComponent } from "app/_plugins/not-found/not-found.component";
import { RegistrationComponent } from "app/pages/signup/registration/registration.component";
import { ForgotPasswordComponent } from "app/pages/password/forgot-password/forgot-password.component";
import { ReviewCardsComponent } from "app/pages/review/review-cards/review-cards.component";
import { LevelsComponent } from "app/pages/learning-path/levels/levels.component";
import { ForumListComponent } from "app/pages/forum/forum-list/forum-list.component";
import { PostDetailsComponent } from "app/pages/forum/post-details/post-details.component";
import { PublicProfileComponent } from "app/pages/public-profile/public-profile/public-profile.component";
import { AddFriendsComponent } from "app/pages/forum/add-friends/add-friends.component";
import { ForumGeneralDiscussionComponent } from "app/pages/forum/forum-general-discussion/forum-general-discussion.component";
import { LeaderBoardComponent } from "app/pages/progress/leader-board/leader-board.component";
import { YourProgressComponent } from "app/pages/progress/your-progress/your-progress.component";
import { AboutComponent } from "app/pages/about/about/about.component";
import { ContactUsComponent } from "app/pages/contact-us/contact-us/contact-us.component";
import { TeacherNavComponent } from "app/pages/teacher/teacher-nav/teacher-nav.component";
import { ClassroomClassesComponent } from "app/pages/learning-path/classroom/classroom-classes/classroom-classes.component";
import { SettingsComponent } from "app/pages/settings/settings/settings.component";
import { Routes as AppRoutes } from "app/shared/utils/elearning-types";

const routes: Routes = [
    // home page
    { path: AppRoutes.Login, component: LoginComponent, canActivate: [MaintenanceModeCheckGuard, AuthGuard] },

    // signup pages
    { path: AppRoutes.Register, component: RegistrationComponent, canActivate: [MaintenanceModeCheckGuard, AuthGuard] },
    {
        path: AppRoutes.LearningPath,
        component: LearningPathComponent,
        canActivate: [MaintenanceModeCheckGuard, RegProgGuard],
    },
    {
        path: AppRoutes.SpreadTheWord,
        component: SpreadTheWordComponent,
        canActivate: [MaintenanceModeCheckGuard, RegProgGuard],
    },
    {
        path: AppRoutes.FindFriends,
        component: FindFriendsComponent,
        canActivate: [MaintenanceModeCheckGuard, RegProgGuard],
    },
    {
        path: AppRoutes.LearningSpeed,
        component: LearningSpeedComponent,
        canActivate: [MaintenanceModeCheckGuard, RegProgGuard],
    },

    // dashboard page
    { path: AppRoutes.Dashboard, component: DashboardComponent, canActivate: [MaintenanceModeCheckGuard, AuthGuard] },

    // learning path page
    { path: AppRoutes.StartLearning, component: LevelsComponent, canActivate: [MaintenanceModeCheckGuard, AuthGuard] },

    // classroom learning path page
    {
        path: AppRoutes.Classroom,
        component: ClassroomClassesComponent,
        canActivate: [MaintenanceModeCheckGuard, AuthGuard],
    },
    {
        path: AppRoutes.Classroom + "/:token",
        component: ClassroomClassesComponent,
        canActivate: [MaintenanceModeCheckGuard, AuthGuard],
    },

    // learning pages
    {
        path: AppRoutes.LessonsAndExercises,
        component: UnitComponent,
        canActivate: [MaintenanceModeCheckGuard, AuthGuard],
    },
    // { path: 'lessons-and-exercises', component: CardsComponent, canActivate: [MaintenanceModeCheckGuard, AuthGuard] },

    // review pages
    { path: AppRoutes.Review, component: ReviewCardsComponent, canActivate: [MaintenanceModeCheckGuard, AuthGuard] },

    // village pages
    {
        path: AppRoutes.Village,
        component: ForumListComponent,
        canActivate: [MaintenanceModeCheckGuard, AuthGuard, FeatureToggleGuard("feature_village")],
    },
    {
        path: AppRoutes.ForumPostDetails,
        component: PostDetailsComponent,
        canActivate: [MaintenanceModeCheckGuard, AuthGuard, FeatureToggleGuard("feature_village")],
    },
    {
        path: AppRoutes.PostsByUser,
        component: ForumGeneralDiscussionComponent,
        canActivate: [MaintenanceModeCheckGuard, AuthGuard, FeatureToggleGuard("feature_village")],
    },

    { path: AppRoutes.AddFriends, component: AddFriendsComponent, canActivate: [MaintenanceModeCheckGuard, AuthGuard] },

    {
        path: AppRoutes.PublicProfile,
        component: PublicProfileComponent,
        canActivate: [MaintenanceModeCheckGuard, AuthGuard],
    },

    // progress pages
    {
        path: AppRoutes.Leaderboard,
        component: LeaderBoardComponent,
        canActivate: [MaintenanceModeCheckGuard, AuthGuard],
    },
    { path: AppRoutes.Progress, component: YourProgressComponent, canActivate: [MaintenanceModeCheckGuard, AuthGuard] },

    // teacher portal
    {
        path: AppRoutes.TeacherDashboard,
        component: TeacherNavComponent,
        canActivate: [MaintenanceModeCheckGuard, AuthGuard],
    },
    {
        path: AppRoutes.TeacherClassrooms,
        component: TeacherNavComponent,
        canActivate: [MaintenanceModeCheckGuard, AuthGuard],
    },
    {
        path: AppRoutes.TeacherLessons,
        component: TeacherNavComponent,
        canActivate: [MaintenanceModeCheckGuard, AuthGuard],
    },
    {
        path: AppRoutes.TeacherAdmin,
        component: TeacherNavComponent,
        canActivate: [MaintenanceModeCheckGuard, AuthGuard],
    },

    // settings pages
    {
        path: AppRoutes.LearningSettings,
        component: SettingsComponent,
        data: { activeTab: "learning" },
        canActivate: [MaintenanceModeCheckGuard, AuthGuard],
    },
    {
        path: AppRoutes.AccessibilitySettings,
        component: SettingsComponent,
        data: { activeTab: "accessibility" },
        canActivate: [MaintenanceModeCheckGuard, AuthGuard],
    },
    {
        path: AppRoutes.AccountSettings,
        component: SettingsComponent,
        data: { activeTab: "account" },
        canActivate: [MaintenanceModeCheckGuard, AuthGuard],
    },
    {
        path: AppRoutes.PrivacySettings,
        component: SettingsComponent,
        data: { activeTab: "privacy" },
        canActivate: [MaintenanceModeCheckGuard, AuthGuard],
    },
    {
        path: AppRoutes.NotificationsSettings,
        component: SettingsComponent,
        data: { activeTab: "notifications" },
        canActivate: [MaintenanceModeCheckGuard, AuthGuard],
    },
    {
        path: AppRoutes.ProfileSettings,
        component: SettingsComponent,
        data: { activeTab: "profile" },
        canActivate: [MaintenanceModeCheckGuard, AuthGuard],
    },

    // contact us page
    { path: AppRoutes.ContactUs, component: ContactUsComponent },

    // about page
    { path: AppRoutes.About, component: AboutComponent },
    { path: AppRoutes.About + "/:tabid", component: AboutComponent },

    // password pages
    { path: AppRoutes.ForgotPassword, component: ForgotPasswordComponent, canActivate: [MaintenanceModeCheckGuard] },
    {
        path: AppRoutes.ChangePassword + "/:token",
        component: ChangePasswordComponent,
        canActivate: [MaintenanceModeCheckGuard],
    },

    { path: AppRoutes.PageNotFound, component: NotFoundComponent, canActivate: [MaintenanceModeCheckGuard] },
    { path: AppRoutes.UnderConstruction, component: MaintenanceModeComponent, canActivate: [MaintenanceModeGuard] },
    { path: "**", redirectTo: AppRoutes.PageNotFound },
];

@NgModule({
    imports: [
        RouterModule.forRoot(routes, {
            useHash: false,
        }),
    ],
    exports: [RouterModule],
})
export class AppRoutingModule {}
