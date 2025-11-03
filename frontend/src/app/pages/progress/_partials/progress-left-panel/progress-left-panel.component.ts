import {
    Component,
    OnInit,
    Input,
    SimpleChanges,
    ChangeDetectionStrategy,
    OnChanges,
} from "@angular/core";
import { Router } from "@angular/router";
import { Routes } from "app/shared/utils/elearning-types";

interface DropdownItem {
    title: string;
    route: string;
}

@Component({
    selector: "app-progress-left-panel",
    templateUrl: "./progress-left-panel.component.html",
    styleUrls: ["./progress-left-panel.component.scss"],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ProgressLeftPanelComponent implements OnInit, OnChanges {
    @Input() userCanAccessLeaderboard: boolean = false;
    public activeDropdownItemTitle: string = "";
    public dropdownItems: DropdownItem[] = [
        {
            title: "Your Progress",
            route: Routes.Progress,
        },
    ];

    constructor(public router: Router) {}

    /**
     * Called when the input properties change. Used to update the dropdown items based
     * on the user's ability to access the leaderboard.
     * @param changes - The changes to the component.
     */
    ngOnChanges(changes: SimpleChanges) {
        if (changes["userCanAccessLeaderboard"]) {
            this.userCanAccessLeaderboard = changes["userCanAccessLeaderboard"].currentValue;
            if (this.userCanAccessLeaderboard) {
                this.dropdownItems.push({
                    title: "Leaderboard",
                    route: Routes.Leaderboard,
                });
            }
        }
    }

    ngOnInit() {
        for (const item of this.dropdownItems) {
            if (this.router.url === `/${item.route}`) {
                this.activeDropdownItemTitle = item.title;
            }
        }
    }

    setActiveDropdownItem(item: DropdownItem) {
        this.activeDropdownItemTitle = item.title;
        void this.router.navigate([item.route]);
    }
}
