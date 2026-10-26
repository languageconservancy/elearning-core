import { ChangeDetectionStrategy, Component, inject, OnInit, OnDestroy } from "@angular/core";
import { FormsModule } from "@angular/forms";
import { Router } from "@angular/router";
import { Subscription } from "rxjs";
import { CookieService } from "@app/_services/cookie.service";

import { LearningPathService } from "@app/_services/learning-path.service";
import { LocalStorageService } from "@app/_services/local-storage.service";
import { RegistrationService } from "@app/_services/registration.service";
import { environment } from "@environments/environment";

@Component({
    selector: "app-learning-path",
    changeDetection: ChangeDetectionStrategy.Eager,
    imports: [FormsModule],
    templateUrl: "./learning-path.component.html",
    styleUrls: ["./learning-path.component.scss"],
    standalone: true,
})
export class LearningPathComponent implements OnInit, OnDestroy {
    private router = inject(Router);
    private cookieService = inject(CookieService);
    private learningPathService = inject(LearningPathService);
    private registrationService = inject(RegistrationService);
    private localStorage = inject(LocalStorageService);

    public environment = environment;
    private userSubscription: Subscription;
    public userId: string = "";
    public pathModel = { selected: null };
    private token: string;
    public learningPaths: any;

    constructor() {
        this.cookieService
            .get("AuthToken")
            .then((value) => {
                if (value == "") {
                    throw new Error(value);
                }
                this.token = value;
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
                    this.token = value;
                    const user = JSON.parse(value);
                    this.userId = user.id;
                }
            })
            .catch((err) => {
                console.warn("No AuthUser cookie", err);
            });

        this.learningPathService
            .getLearningPaths({ user_id: this.userId })
            .then((userLearningPaths: any) => {
                this.learningPaths = userLearningPaths;
            })
            .catch((err) => {
                console.error("Error getting learning paths", err);
            });
    }

    ngOnDestroy() {
        this.userSubscription.unsubscribe();
    }

    submit() {
        if (this.pathModel.selected) {
            const postData = {
                id: this.userId,
                learningpath_id: this.pathModel.selected,
            };
            this.learningPathService
                .setLearningPath(postData)
                .then(() => {
                    void this.router.navigate(["learning-speed"]);
                })
                .catch((err) => {
                    console.error("Error setting learning path", err);
                });
        }
    }

    startLearning() {
        this.localStorage.removeItem("regProg");
        void this.router.navigate(["start-learning"]);
    }
}
