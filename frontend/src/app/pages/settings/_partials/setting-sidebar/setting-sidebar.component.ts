import { Component, inject, OnDestroy } from "@angular/core";
import { Subscription } from "rxjs";

import { CapitalizeFirstLetterPipe } from "@app/_pipes/capitalize-first-letter.pipe";
import { SettingsService } from "@app/_services/settings.service";

@Component({
    selector: "app-setting-sidebar",
    imports: [CapitalizeFirstLetterPipe],
    templateUrl: "./setting-sidebar.component.html",
    styleUrls: ["./setting-sidebar.component.scss"],
    standalone: true,
})
export class SettingSidebarComponent implements OnDestroy {
    private settingsService = inject(SettingsService);

    private tabSubscription: Subscription;
    public tabName: string = "profile";

    constructor() {
        this.tabSubscription = this.settingsService.currentTab.subscribe((tab) => {
            this.tabName = tab;
        });
    }

    ngOnDestroy() {
        this.tabSubscription.unsubscribe();
    }
}
