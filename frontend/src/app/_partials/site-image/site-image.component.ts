import { ChangeDetectionStrategy, Component, inject, OnInit } from "@angular/core";
import { SettingsService } from "@app/_services/settings.service";
import { environment } from "@environments/environment";
@Component({
    selector: "app-site-image",
    changeDetection: ChangeDetectionStrategy.Eager,
    templateUrl: "./site-image.component.html",
    styleUrls: ["./site-image.component.scss"],
    standalone: true,
})
export class SiteImageComponent implements OnInit {
    private settingsService = inject(SettingsService);

    public environment = environment;
    public imagePath: any;

    ngOnInit() {
        void this.settingsService.getImage().then((res) => {
            this.imagePath = res.data.results;
        });
    }
}
