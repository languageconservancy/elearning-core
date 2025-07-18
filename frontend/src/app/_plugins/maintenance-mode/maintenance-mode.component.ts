import { Component, OnInit } from "@angular/core";
import { DomSanitizer } from "@angular/platform-browser";
import { Router } from "@angular/router";

import { Loader } from "app/_services/loader.service";
import { SettingsService } from "app/_services/settings.service";

@Component({
    selector: "app-maintenance-mode",
    templateUrl: "./maintenance-mode.component.html",
    styleUrls: ["./maintenance-mode.component.scss"],
})
export class MaintenanceModeComponent implements OnInit {
    public maintenance: any;

    constructor(
        private settingsService: SettingsService,
        private loader: Loader,
        private router: Router,
        private sanitizer: DomSanitizer,
    ) {}

    ngOnInit() {
        this.setLoader(true);
        this.settingsService
            .getMaintenanceMode()
            .then((res) => {
                this.setLoader(false);
                if (res.data.status && res.data.results.is_under_construction == "Y") {
                    this.maintenance = res.data.results;
                    this.maintenance.under_construction_html = this.sanitizer.bypassSecurityTrustHtml(
                        this.maintenance.under_construction_html,
                    );
                    this.settingsService.setMaintenanceMode(true);
                } else {
                    // this.router.navigate(['']);
                }
            })
            .catch((err) => {
                console.error(err);
                // this.router.navigate(['']);
            });
    }

    private setLoader(val: boolean) {
        this.loader.setLoader(val);
    }
}
