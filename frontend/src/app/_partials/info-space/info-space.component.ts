import { Component, inject } from "@angular/core";
import { Router } from "@angular/router";
import { environment } from "@environments/environment";

@Component({
    selector: "app-info-space",
    templateUrl: "./info-space.component.html",
    styleUrls: ["./info-space.component.scss"],
    standalone: true,
})
export class InfoSpaceComponent {
    private router = inject(Router);
    public environment = environment;

    gotoUrl(url: any) {
        void this.router.navigate(["about/" + url]);
    }
}
