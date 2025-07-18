import { Component, OnDestroy } from "@angular/core";
import { Subscription } from "rxjs";
import { Router } from "@angular/router";

import { ReviewService } from "app/_services/review.service";

@Component({
    selector: "app-breadcrumb",
    templateUrl: "./breadcrumb.component.html",
    styleUrls: ["./breadcrumb.component.scss"],
})
export class BreadcrumbComponent implements OnDestroy {
    public breadcrumb: any = [];
    public breadcrumbSubscription: Subscription;
    constructor(
        private reviewService: ReviewService,
        public router: Router,
    ) {
        this.breadcrumbSubscription = this.reviewService.breadcrumb.subscribe((data) => {
            if (data && Object.keys(data).length > 0) {
                this.breadcrumb = data;
                localStorage.setItem("breadcrumb", JSON.stringify(data));
            } else {
                const getbreadcrumb = localStorage.getItem("breadcrumb");
                if (getbreadcrumb) {
                    this.breadcrumb = JSON.parse(getbreadcrumb);
                }
            }
        });
    }

    goToURL(url: any) {
        void this.router.navigate([url.substr(1)]);
    }
    ngOnDestroy() {
        // alert('ngOnDestroy work');
        this.reviewService.setBreadcrumb([]);
        this.breadcrumbSubscription.unsubscribe();
    }
}
