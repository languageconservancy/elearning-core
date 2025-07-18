import { Component, OnInit } from "@angular/core";
import { SettingsService } from "app/_services/settings.service";
import { environment } from "environments/environment";
@Component({
    selector: "app-site-image",
    templateUrl: "./site-image.component.html",
    styleUrls: ["./site-image.component.scss"],
})
export class SiteImageComponent implements OnInit {
    public environment = environment;
    public imagePath: any;

    constructor(private settingsService: SettingsService) {}

    ngOnInit() {
        void this.settingsService.getImage().then((res) => {
            this.imagePath = res.data.results;
        });
    }
}
