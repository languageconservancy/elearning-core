import { Component, inject, OnInit } from "@angular/core";
import { Router, ActivatedRoute } from "@angular/router";

import { PageTitleComponent } from "@app/_partials/page-title/page-title.component";
import { CapitalizeFirstLetterPipe } from "@app/_pipes/capitalize-first-letter.pipe";
import { SafeHtmlPipe } from "@app/_pipes/safe-html.pipe";
import { SafeUrlPipe } from "@app/_pipes/safe-url.pipe";
import { SettingsService } from "@app/_services/settings.service";
import { environment } from "@environments/environment";

@Component({
    selector: "app-about",
    imports: [PageTitleComponent, CapitalizeFirstLetterPipe, SafeHtmlPipe, SafeUrlPipe],
    templateUrl: "./about.component.html",
    styleUrls: ["./about.component.scss"],
    standalone: true,
})
export class AboutComponent implements OnInit {
    private router = inject(Router);
    private route = inject(ActivatedRoute);
    private settingservice = inject(SettingsService);

    public environment = environment;
    public content: any = [];
    public activeTab: any = [];

    constructor() {
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
                            // eslint-disable-next-line @typescript-eslint/no-array-delete
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

    getPrivacyUrl(): string {
        return `${environment.ROOT}/info/privacy.php`;
    }

    getTermsUrl(): string {
        return `${environment.ROOT}/info/terms.php`;
    }

    gotoUrl(url: any) {
        void this.router.navigate(["about/" + url]);
    }
}
