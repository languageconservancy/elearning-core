import { Component, Input } from "@angular/core";
import { Location } from "@angular/common";

@Component({
    selector: "app-page-title",
    templateUrl: "./page-title.component.html",
    styleUrls: ["./page-title.component.scss"],
    standalone: false,
})
export class PageTitleComponent {
    @Input() pageTitle: string;

    constructor(private location: Location) {}

    historyBack() {
        this.location.back();
    }
}
