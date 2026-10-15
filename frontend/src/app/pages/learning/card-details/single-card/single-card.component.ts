import { Component, inject, OnDestroy } from "@angular/core";
import { Subscription } from "rxjs";
import { DeviceDetectorService } from "ngx-device-detector";

import { ExerciseComponent } from "../exercises/exercise/exercise.component";
import { KeyboardShortcutsComponent } from "@app/shared/keyboard/keyboard-shortcuts/keyboard-shortcuts.component";
import { LessonsComponent } from "@app/pages/learning/card-details/lessons/lessons.component";
import { LessonsService } from "@app/_services/lessons.service";

@Component({
    selector: "app-single-card",
    imports: [ExerciseComponent, KeyboardShortcutsComponent, LessonsComponent],
    templateUrl: "./single-card.component.html",
    styleUrls: ["./single-card.component.scss"],
    standalone: true,
})
export class SingleCardComponent implements OnDestroy {
    private lessonService = inject(LessonsService);
    private deviceDetector = inject(DeviceDetectorService);

    public type: any = "";
    public typeSubscription: Subscription;
    public isMobile: boolean = false;
    public isTablet: boolean = false;

    constructor() {
        this.getDeviceInfo();

        this.typeSubscription = this.lessonService.currentType.subscribe((type) => {
            this.type = type;
        });
    }

    private getDeviceInfo() {
        this.isMobile = this.deviceDetector.isMobile();
        this.isTablet = this.deviceDetector.isTablet();
    }

    ngOnDestroy() {
        this.typeSubscription.unsubscribe();
    }
}
