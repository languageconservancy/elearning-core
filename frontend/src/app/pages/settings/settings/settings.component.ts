import { Component, OnDestroy, OnInit } from "@angular/core";
import { Subscription } from "rxjs";
import { SettingsService } from "app/_services/settings.service";
import { ActivatedRoute } from "@angular/router";

@Component({
    selector: "app-settings",
    templateUrl: "./settings.component.html",
    styleUrls: ["./settings.component.scss"],
})
export class SettingsComponent implements OnInit, OnDestroy {
    private tabSubscription: Subscription;
    public currentTabName: string = "Profile";
    public pages = [
        { title: "Profile", route: "/profile" },
        { title: "Learning", route: "/learning" },
    ];

    constructor(
        private settingsService: SettingsService,
        private activatedRoute: ActivatedRoute,
    ) {
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
