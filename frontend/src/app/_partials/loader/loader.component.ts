import { Component, inject, OnDestroy } from "@angular/core";
import { Subscription } from "rxjs";

import { Loader } from "@app/_services/loader.service";

@Component({
    selector: "app-loader",
    templateUrl: "./loader.component.html",
    styleUrls: ["./loader.component.scss"],
    standalone: true,
})
export class LoaderComponent implements OnDestroy {
    private loader = inject(Loader);
    private loaderSubscription: Subscription;
    public showLoader: boolean = false;

    constructor() {
        this.loaderSubscription = this.loader.loader.subscribe((val) => (this.showLoader = val));
    }

    ngOnDestroy() {
        this.loaderSubscription.unsubscribe();
    }
}
