import { Component, OnDestroy } from "@angular/core";
import { Subscription } from "rxjs";

import { Loader } from "app/_services/loader.service";

@Component({
    selector: "app-loader",
    templateUrl: "./loader.component.html",
    styleUrls: ["./loader.component.scss"],
})
export class LoaderComponent implements OnDestroy {
    private loaderSubscription: Subscription;
    public showLoader: boolean = false;

    constructor(private loader: Loader) {
        this.loaderSubscription = this.loader.loader.subscribe((val) => (this.showLoader = val));
    }

    ngOnDestroy() {
        this.loaderSubscription.unsubscribe();
    }
}
