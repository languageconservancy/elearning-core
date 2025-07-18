import { Component, OnInit, OnDestroy } from "@angular/core";
import { CookieService } from "app/_services/cookie.service";
import { Router } from "@angular/router";

import { Loader } from "app/_services/loader.service";
import { LessonsService } from "app/_services/lessons.service";
import { LocalStorageService } from "app/_services/local-storage.service";
import { ReviewService } from "app/_services/review.service";
import { ForumService } from "app/_services/forum.service";
import { UnitsComponent } from "app/pages/learning-path/units/units.component";

@Component({
    selector: "app-classroom-units",
    templateUrl: "./classroom-units.component.html",
    styleUrls: ["./classroom-units.component.scss"],
})
export class ClassroomUnitsComponent extends UnitsComponent implements OnInit, OnDestroy {
    protected villageImageUrl: string;

    constructor(
        router: Router,
        cookieService: CookieService,
        loader: Loader,
        lessonService: LessonsService,
        reviewService: ReviewService,
        localStorage: LocalStorageService,
        forumService: ForumService,
    ) {
        super(router, cookieService, loader, lessonService, reviewService, localStorage, forumService);

        this.villageImageUrl = "./assets/images/lesson-icon.png";
        this.subscribeToCurrentLevel();

        this.subscribeToLastActiveUnitOrReview();

        this.subscribeToUnitReview();
    }

    override subscribeToCurrentLevel() {
        this.levelSubscription = this.lessonService.currentLevelDetails.subscribe((level) => {
            if (level && Object.keys(level).length > 0) {
                this.level = level;
                this.promoteNextActivity();
                if (level.id == this.localStorage.getItem("CurrentLevelID")) {
                    // this.scrollUnits(false);
                } else {
                    // this.scrollUnits(true);
                }
            }
        });
    }

    private isDateBeforeToday(date) {
        const scheduledDate = new Date(date.substring(0, 10));
        //scheduledDate.setDate(scheduledDate.getDate() + 1);
        const currentDate = new Date();
        const isBefore = scheduledDate < currentDate;
        return isBefore;
    }

    //this function serves to highlight needed review and should eventually contain other needed processing for flagging next activity.
    override promoteNextActivity() {
        let foundFirstActive = false;
        let foundFirstReview = false;
        let enableNext = false;
        let lockTheRest = false;

        this.level.units.forEach((unit, index) => {
            if (enableNext) {
                unit["enable"] = true;
                enableNext = false;
            }
            if (!!unit.classroomLevelUnits) {
                unit["flagIsScheduled"] = false;
                unit["flagInactive"] = false;
                //was scheduled and date has not come
                if (
                    !!unit.classroomLevelUnits.release_date &&
                    !this.isDateBeforeToday(unit.classroomLevelUnits.release_date)
                ) {
                    unit["flagIsScheduled"] = true;
                    unit["enable"] = false;
                }
                //was scheduled and date came
                if (
                    !!unit.classroomLevelUnits.release_date &&
                    this.isDateBeforeToday(unit.classroomLevelUnits.release_date)
                ) {
                    unit["enable"] = true;
                    if (!foundFirstActive) {
                        foundFirstActive = true;
                    }
                } else {
                    if (unit.classroomLevelUnits.active == false && unit["flagIsScheduled"] == false) {
                        unit["enable"] = false;
                        unit["flagInactive"] = true;
                    }
                    if (lockTheRest) {
                        unit["enable"] = false;
                    }
                }
                if (!foundFirstActive && unit["flagIsScheduled"] == false && unit["flagInactive"] == false) {
                    unit["enable"] = true;
                    foundFirstActive = true;
                }
                if (unit.classroomLevelUnits.optional == true && unit["enable"] == true) {
                    enableNext = true;
                }
                if (unit["enable"] == false && foundFirstActive == true) {
                    lockTheRest = true;
                }
            }
            if (
                !foundFirstReview &&
                foundFirstActive &&
                !unit["enable"] &&
                this.level.units[index - 1]["unitPercentage"] >= 100
            ) {
                this.level.units[index - 1]["flagNeedsReview"] = true;
                foundFirstReview = true;
            }
        });
    }
}
