import { ChangeDetectionStrategy, Component } from "@angular/core";

@Component({
    selector: "app-not-found",
    changeDetection: ChangeDetectionStrategy.Eager,
    templateUrl: "./not-found.component.html",
    styleUrls: ["./not-found.component.scss"],
    standalone: true,
})
export class NotFoundComponent {}
