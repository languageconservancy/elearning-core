import { Component, inject, input } from "@angular/core";
import { Location } from "@angular/common";

@Component({
    selector: "app-page-title",
    templateUrl: "./page-title.component.html",
    styleUrls: ["./page-title.component.scss"],
    standalone: true,
})
export class PageTitleComponent {
    private location = inject(Location);
    pageTitle = input<string>();

    historyBack() {
        this.location.back();
    }
}
