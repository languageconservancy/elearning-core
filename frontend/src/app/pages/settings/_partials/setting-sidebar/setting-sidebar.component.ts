import { Component, OnDestroy } from "@angular/core";
import { Subscription } from "rxjs";

import { SettingsService } from "app/_services/settings.service";

@Component({
    selector: "app-setting-sidebar",
    templateUrl: "./setting-sidebar.component.html",
    styleUrls: ["./setting-sidebar.component.scss"],
})
export class SettingSidebarComponent implements OnDestroy {
    private tabSubscription: Subscription;
    public tabName: string = "profile";

    constructor(private settingsService: SettingsService) {
        this.tabSubscription = this.settingsService.currentTab.subscribe((tab) => {
            this.tabName = tab;
        });
    }

    ngOnDestroy() {
        this.tabSubscription.unsubscribe();
    }
}
