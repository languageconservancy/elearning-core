import { Component, ChangeDetectionStrategy } from "@angular/core";

@Component({
    selector: "app-social-share",
    templateUrl: "./social-share.component.html",
    styleUrls: ["./social-share.component.scss"],
    changeDetection: ChangeDetectionStrategy.Eager,
    standalone: false,
})
export class SocialShareComponent {
    constructor() {}
}
