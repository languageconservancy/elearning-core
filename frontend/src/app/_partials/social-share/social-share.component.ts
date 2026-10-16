import { ChangeDetectionStrategy, Component } from "@angular/core";

@Component({
    selector: "app-social-share",
    changeDetection: ChangeDetectionStrategy.Eager,
    templateUrl: "./social-share.component.html",
    styleUrls: ["./social-share.component.scss"],
    standalone: true,
})
export class SocialShareComponent {}
