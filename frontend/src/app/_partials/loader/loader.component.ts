import { Component, OnDestroy, ChangeDetectionStrategy } from "@angular/core";
import { Subscription } from "rxjs";

import { Loader } from "@app/_services/loader.service";

@Component({
    selector: "app-loader",
    templateUrl: "./loader.component.html",
    styleUrls: ["./loader.component.scss"],
    changeDetection: ChangeDetectionStrategy.Eager,
    standalone: false,
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
