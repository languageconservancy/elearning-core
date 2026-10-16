import { ChangeDetectionStrategy, Component, inject, OnDestroy, OnInit } from "@angular/core";
import { Subscription } from "rxjs";
import { AccountComponent } from "../account/account.component";
import { LearningComponent } from "@app/pages/settings/learning/learning.component";
import { NotificationsComponent } from "@app/pages/settings/notifications/notifications.component";
import { PageTitleComponent } from "@app/_partials/page-title/page-title.component";
import { PrivacyComponent } from "@app/pages/settings/privacy/privacy.component";
import { ProfileComponent } from "@app/pages/settings/profile/profile.component";
import { SettingSidebarComponent } from "@app/pages/settings/_partials/setting-sidebar/setting-sidebar.component";
import { CapitalizeFirstLetterPipe } from "@app/_pipes/capitalize-first-letter.pipe";
import { SettingsService } from "@app/_services/settings.service";
import { ActivatedRoute } from "@angular/router";

@Component({
    selector: "app-settings",
    changeDetection: ChangeDetectionStrategy.Eager,
    imports: [
        AccountComponent,
        LearningComponent,
        NotificationsComponent,
        PageTitleComponent,
        PrivacyComponent,
        ProfileComponent,
        SettingSidebarComponent,
        CapitalizeFirstLetterPipe,
    ],
    templateUrl: "./settings.component.html",
    styleUrls: ["./settings.component.scss"],
    standalone: true,
})
export class SettingsComponent implements OnInit, OnDestroy {
    private settingsService = inject(SettingsService);
    private activatedRoute = inject(ActivatedRoute);

    private tabSubscription: Subscription;
    public currentTabName: string = "Profile";
    public pages = [
        { title: "Profile", route: "/profile" },
        { title: "Learning", route: "/learning" },
    ];

    constructor() {
        this.tabSubscription = this.settingsService.currentTab.subscribe((tab) => {
            this.currentTabName = tab.toLowerCase();
        });
    }

    ngOnInit() {
        this.activatedRoute.data.subscribe((data) => {
            this.currentTabName = data.activeTab.toLowerCase();
        });
    }

    ngOnDestroy() {
        this.tabSubscription.unsubscribe();
    }
}
