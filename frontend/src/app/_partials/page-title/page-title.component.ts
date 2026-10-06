import { Component, Input, ChangeDetectionStrategy } from "@angular/core";
import { Location } from "@angular/common";

@Component({
    selector: "app-page-title",
    templateUrl: "./page-title.component.html",
    styleUrls: ["./page-title.component.scss"],
    changeDetection: ChangeDetectionStrategy.Eager,
    standalone: false,
})
export class PageTitleComponent {
    @Input() pageTitle: string;

    constructor(private location: Location) {}

    historyBack() {
        this.location.back();
    }
}
