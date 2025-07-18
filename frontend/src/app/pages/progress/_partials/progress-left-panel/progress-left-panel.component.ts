import { Component, OnInit, Input } from "@angular/core";
import { Router } from "@angular/router";

interface DropdownItem {
    title: string;
    route: string;
}

@Component({
    selector: "app-progress-left-panel",
    templateUrl: "./progress-left-panel.component.html",
    styleUrls: ["./progress-left-panel.component.scss"],
})
export class ProgressLeftPanelComponent implements OnInit {
    @Input() canAccessLeaderboard: boolean = false;
    public activeDropdownItemTitle;
    public dropdownItems: DropdownItem[] = [
        {
            title: "Your Progress",
            route: "/progress",
        },
    ];

    constructor(public router: Router) {
        if (this.canAccessLeaderboard) {
            this.dropdownItems.push({
                title: "Leaderboard",
                route: "/leader-board",
            });
        }
    }

    ngOnInit() {
        for (const item of this.dropdownItems) {
            if (this.router.url === item.route) {
                this.activeDropdownItemTitle = item.title;
            }
        }
    }

    setActiveDropdownItem(item: DropdownItem) {
        void this.router.navigate([item.route]);
    }
}
