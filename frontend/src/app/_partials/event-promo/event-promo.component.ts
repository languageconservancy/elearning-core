import { Component, OnInit, OnDestroy, ViewChild } from "@angular/core";
import { Subscription } from "rxjs";

import { SettingsService } from "app/_services/settings.service";

@Component({
    selector: "app-event-promo",
    templateUrl: "./event-promo.component.html",
    styleUrls: ["./event-promo.component.scss"],
})
export class EventPromoComponent implements OnInit, OnDestroy {
    public eventPromo: any = {};
    public promoSeen: boolean = false;
    @ViewChild("promoModal") promoModal;

    public promoSeenSubscription: Subscription;

    constructor(private settingservice: SettingsService) {
        this.promoSeenSubscription = this.settingservice.promoSeen.subscribe((seen) => {
            this.promoSeen = seen;
        });
    }

    ngOnInit() {
        if (!this.promoSeen) {
            this.settingservice
                .getCMS()
                .then((res) => {
                    if (res.data.status && res.data.results.length > 0) {
                        this.eventPromo = res.data.results.find((i) => i.Id === "event");
                    }
                    if (this.eventPromo === undefined) {
                        this.settingservice.setPromoSeen(true);
                    } else {
                        if (!!this.eventPromo.content) {
                            this.openModal();
                        }
                    }
                })
                .catch((err) => {
                    console.error(err);
                });
        } else {
            this.settingservice.setPromoSeen(true);
        }
    }

    ngOnDestroy() {
        this.promoSeenSubscription.unsubscribe();
    }

    openModal() {
        this.promoModal.nativeElement.className = "modal fade show";
    }
    closeModal() {
        this.promoModal.nativeElement.className = "modal hide";
        this.settingservice.setPromoSeen(true);
        this.eventPromo.content = "";
    }
}
