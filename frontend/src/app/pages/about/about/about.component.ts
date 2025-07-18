import { Component, OnInit } from "@angular/core";
import { Router, ActivatedRoute } from "@angular/router";

import { SettingsService } from "app/_services/settings.service";
import { environment } from "environments/environment";

@Component({
    selector: "app-about",
    templateUrl: "./about.component.html",
    styleUrls: ["./about.component.scss"],
})
export class AboutComponent implements OnInit {
    public environment = environment;
    public content: any = [];
    public activeTab: any = [];

    constructor(
        private router: Router,
        private route: ActivatedRoute,
        private settingservice: SettingsService,
    ) {
        this.route.params.subscribe((params) => {
            this.activeTab = params["tabid"];
            window.scroll(0, 0);
        });
    }

    ngOnInit() {
        this.content = [];
        let localContent = [];
        this.settingservice
            .getCMS()
            .then((res) => {
                if (res.data.status) {
                    localContent = res.data.results;
                    for (const [i, value] of localContent.entries()) {
                        if (value.Id == "privacy" || value.Id == "terms") {
                            delete localContent[i];
                        }
                    }
                    this.content = localContent.filter((el) => el != null);
                }
            })
            .catch((err) => {
                console.error(err);
            });

        if (typeof this.activeTab == "undefined") {
            this.activeTab = "project";
        }
    }

    gotoUrl(url: any) {
        void this.router.navigate(["about/" + url]);
    }
}
