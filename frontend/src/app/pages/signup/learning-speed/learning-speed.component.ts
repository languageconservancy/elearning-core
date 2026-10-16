import { Component, inject, OnInit, OnDestroy } from "@angular/core";
import { FormsModule } from "@angular/forms";
import { Router } from "@angular/router";
import { Subscription } from "rxjs";
import { CookieService } from "@app/_services/cookie.service";

import { LearningSpeedService } from "@app/_services/learning-speed.service";
import { RegistrationService } from "@app/_services/registration.service";
import { environment } from "@environments/environment";

@Component({
    selector: "app-learning-speed",
    imports: [FormsModule],
    templateUrl: "./learning-speed.component.html",
    styleUrls: ["./learning-speed.component.scss"],
    standalone: true,
})
export class LearningSpeedComponent implements OnInit, OnDestroy {
    private router = inject(Router);
    private cookieService = inject(CookieService);
    public learningSpeedService = inject(LearningSpeedService);
    private registrationService = inject(RegistrationService);

    public environment = environment;
    private userSubscription: Subscription;
    public userId: string;
    public speedModel = { selected: null };
    public learningSpeeds: any;

    constructor() {
        this.cookieService
            .get("AuthToken")
            .then((value) => {
                if (value == "") {
                    throw new Error(value);
                }
            })
            .catch(() => {
                void this.router.navigate([""]);
            });

        this.userSubscription = this.registrationService.currentUser.subscribe(
            (userId) => (this.userId = userId.id),
        );
    }

    ngOnInit() {
        this.cookieService
            .get("AuthUser")
            .then((value) => {
                if (value == "") {
                    throw new Error(value);
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
