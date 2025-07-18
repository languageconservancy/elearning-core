import { Component } from "@angular/core";
import { Router } from "@angular/router";
import { environment } from "environments/environment";

@Component({
    selector: "app-info-space",
    templateUrl: "./info-space.component.html",
    styleUrls: ["./info-space.component.scss"],
})
export class InfoSpaceComponent {
    public environment = environment;
    constructor(private router: Router) {}

    gotoUrl(url: any) {
        void this.router.navigate(["about/" + url]);
    }
}
