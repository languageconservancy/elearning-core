import { Component, OnInit, OnDestroy } from "@angular/core";
import { Router } from "@angular/router";
import { Subscription } from "rxjs";
import { CookieService } from "app/_services/cookie.service";

import { LearningSpeedService } from "app/_services/learning-speed.service";
import { LocalStorageService } from "app/_services/local-storage.service";
import { RegistrationService } from "app/_services/registration.service";
import { environment } from "environments/environment";

@Component({
    selector: "app-learning-speed",
    templateUrl: "./learning-speed.component.html",
    styleUrls: ["./learning-speed.component.scss"],
})
export class LearningSpeedComponent implements OnInit, OnDestroy {
    public environment = environment;
    private userSubscription: Subscription;
    public userId: string;
    public speedModel = { selected: null };
    public learningSpeeds: any;

    constructor(
        private router: Router,
        private cookieService: CookieService,
        public learningSpeedService: LearningSpeedService,
        private registrationService: RegistrationService,
        private localStorage: LocalStorageService,
    ) {
        this.cookieService
            .get("AuthToken")
            .then((value) => {
                if (value == "") {
                    throw value;
                }
            })
            .catch(() => {
                void this.router.navigate([""]);
            });

        this.userSubscription = this.registrationService.currentUser.subscribe((userId) => (this.userId = userId.id));
    }

    ngOnInit() {
        this.cookieService
            .get("AuthUser")
            .then((value) => {
                if (value == "") {
                    throw value;
                }
                if (this.userId == "") {
                    const user = JSON.parse(value);
                    this.userId = user.id;
                }
            })
            .catch((err) => {
                console.warn("No AuthUser cookie", err);
            });

        this.learningSpeedService
            .getLearningSpeed()
            .then((res) => {
                this.learningSpeeds = res.data.results;
            })
            .catch((err) => {
                console.error(err);
            });
    }

    ngOnDestroy() {
        this.userSubscription.unsubscribe();
    }

    submit() {
        if (this.speedModel.selected) {
            const postData = {
                id: this.userId,
                learningspeed_id: this.speedModel.selected,
            };
            this.learningSpeedService
                .setLearningSpeed(postData)
                .then(() => {
                    void this.router.navigate(["spread-the-word"]);
                })
                .catch((err) => {
                    console.error(err);
                });
        }
    }
}
