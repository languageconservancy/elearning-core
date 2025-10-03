import { Component, OnDestroy } from "@angular/core";
import { Subscription } from "rxjs";
import { NavigationEnd, Router } from "@angular/router";

import { Routes } from "app/shared/utils/elearning-types";
import { BreadcrumbsService, Breadcrumb } from "app/_services/breadcrumbs.service";

@Component({
    selector: "app-breadcrumbs",
    templateUrl: "./breadcrumbs.component.html",
    styleUrls: ["./breadcrumbs.component.scss"],
})
export class BreadcrumbsComponent implements OnDestroy {
    public breadcrumbs: Breadcrumb[] = [];
    public breadcrumbsSubscription: Subscription;
    private routerSubscription: Subscription;
    private baseRoute: string = "";
    private allowedRoutes: string[] = [
        Routes.Dashboard,
        Routes.StartLearning,
        Routes.Classroom,
        Routes.LessonsAndExercises,
        Routes.Review,
    ];
    public showBreadcrumb: boolean = false;

    constructor(
        private breadcrumbsService: BreadcrumbsService,
        public router: Router,
    ) {
        this.subscribeToBreadcrumbs();
        this.subscribeToRouter();
    }

    private subscribeToBreadcrumbs() {
        this.breadcrumbsSubscription = this.breadcrumbsService.breadcrumbs$.subscribe(
            (breadcrumbs) => {
                this.breadcrumbs = breadcrumbs;
            },
        );
    }

    private subscribeToRouter() {
        this.routerSubscription = this.router.events.subscribe((event) => {
            if (event instanceof NavigationEnd) {
                // Get the current route suffix (removing leading slash)
                this.baseRoute = event.urlAfterRedirects.startsWith("/")
                    ? event.urlAfterRedirects.substring(1)
                    : event.urlAfterRedirects;

                // Check if breadcrumb should be shown for this route
                this.showBreadcrumb = this.checkIfBreadcrumbIsAllowed(this.baseRoute);
            }
        });
    }

    private checkIfBreadcrumbIsAllowed(baseRoute: string) {
        return this.allowedRoutes.includes(baseRoute);
    }

    onBreadcrumbClick(breadcrumb: Breadcrumb) {
        const url = breadcrumb.url;
        const urlWithoutSlash = url.substring(1);
        void this.router.navigate([urlWithoutSlash]);
        // Get index of clicked breadcrumb
        const index = this.breadcrumbs.indexOf(breadcrumb);
        // Now remove all breadcrumbs after the clicked breadcrumb
        this.breadcrumbs = this.breadcrumbs.slice(0, index + 1);
        this.breadcrumbsService.setBreadcrumbs(this.breadcrumbs);
    }

    ngOnDestroy() {
        this.breadcrumbsSubscription.unsubscribe();
        if (this.routerSubscription) {
            this.routerSubscription.unsubscribe();
        }
    }
}
