import { Component, OnInit, OnDestroy, ViewChild } from "@angular/core";
import { CookieService } from "app/_services/cookie.service";
import { Router, ActivatedRoute } from "@angular/router";
import { Subscription } from "rxjs";
import Swal from "sweetalert2";

import { Loader } from "app/_services/loader.service";
import { LearningPathService } from "app/_services/learning-path.service";
import { LessonsService } from "app/_services/lessons.service";
import { LocalStorageService } from "app/_services/local-storage.service";
import { SettingsService } from "app/_services/settings.service";
import { ReviewService } from "app/_services/review.service";
import { ClassroomService } from "app/_services/classroom.service";
import { SnackbarService } from "app/_services/snackbar.service";

@Component({
    selector: "app-classroom-classes",
    templateUrl: "./classroom-classes.component.html",
    styleUrls: ["./classroom-classes.component.scss"],
})
export class ClassroomClassesComponent implements OnInit, OnDestroy {
    // Levels component properties duplicated here
    public user: any = {};
    public path: any = {};
    public allLevels: any = [];
    public currentLevel: any = {};
    public selectedLevel: number;
    public noLevel: boolean = false;
    public noPath: boolean = false;
    public timeZoneOffset: number = 0;
    public fireData: any = {};
    public noReviews: boolean = false;
    public fireImage: string = "dead";
    // Classroom-specific properties
    protected defaultLevelImageUrl: string = "./assets/images/menu-3.png";
    public activeLevels: any = [];
    public inactiveLevels: any = [];
    public classroomToken: string = "";
    public newClassroomDetails: any = {};
    @ViewChild("classModal") classModal;

    public levelSubscription: Subscription;

    constructor(
        private router: Router,
        private cookieService: CookieService,
        private loader: Loader,
        private lessonService: LessonsService,
        private settingsService: SettingsService,
        private localStorage: LocalStorageService,
        private reviewService: ReviewService,
        private learningPathService: LearningPathService,
        private snackbarService: SnackbarService,
        // Classroom-specific services
        private classroomService: ClassroomService,
        private route: ActivatedRoute,
    ) {
        this.getUser();

        this.levelSubscription = this.lessonService.currentLevel.subscribe(
            (level) => (this.selectedLevel = level),
        );

        if (this.localStorage.getItem("LevelID")) {
            this.selectedLevel = parseInt(this.localStorage.getItem("LevelID"));
        }
    }

    ngOnInit() {
        this.localStorage.setItem("isClassroom", 1);
    }

    ngOnDestroy() {
        this.levelSubscription.unsubscribe();
    }

    getUser() {
        this.cookieService
            .get("AuthUser")
            .then((value) => {
                if (value == "") {
                    throw value;
                }
                this.loader.setLoader(true);
                const loggedInUser = JSON.parse(value);
                this.settingsService
                    .getAllSettings(loggedInUser.id)
                    .then((res) => {
                        if (res.data.status) {
                            this.user = res.data.results[0];
                            this.checkQueryParamsForClassroomInvite();

                            this.getFireData();
                            if (this.user.learningpath_id) {
                                this.noPath = false;
                                this.getLearningPath();
                            } else {
                                this.loader.setLoader(false);
                                this.noPath = true;
                            }
                        } else {
                            console.error("[classroom-classes] alreadyDeleted");
                            this.alreadyDeleted();
                        }
                    })
                    .catch((err) => {
                        this.loader.setLoader(false);
                        if (!err.ok) {
                            console.error("[classroom-classes] alreadyDeleted err");
                            this.alreadyDeleted();
                        }
                    });
            })
            .catch(() => {
                void this.router.navigate([""]);
            });
    }

    checkQueryParamsForClassroomInvite() {
        this.route.params.subscribe((params) => {
            if (!!params.token) {
                this.classroomToken = params.token;
                const classroomParams = {
                    user_id: this.user.id,
                    type: "checkWordlink",
                    params: {
                        wordlink: this.classroomToken,
                    },
                };
                this.classroomService.getClassroomData(classroomParams).then(async (res) => {
                    if (!!res) {
                        if (res.data.status) {
                            this.newClassroomDetails = res.data.results;
                            let classroomInviteMessage = "";
                            let classroomInviteTitle = "";
                            if (!!this.newClassroomDetails.classroom.name) {
                                classroomInviteTitle = "Join New Classroom?";
                                classroomInviteMessage = this.newClassroomDetails.classroom.name;
                            } else if (!!this.newClassroomDetails.school.name) {
                                classroomInviteTitle = "Join School?";
                                classroomInviteMessage = this.newClassroomDetails.school.name;
                            }
                            await Swal.fire({
                                title: classroomInviteTitle,
                                text: "Would you like to be added to " + classroomInviteMessage + "?",
                                icon: "warning",
                                showCancelButton: true,
                                confirmButtonColor: "#3085d6",
                                cancelButtonColor: "#d33",
                                confirmButtonText: "Yes, join!",
                            }).then(async (result) => {
                                if (result.isConfirmed || result.value) {
                                    this.acceptClassroom();
                                    await this.promptResetUserField(
                                        "name",
                                        "Let's check your name",
                                        "Make sure this is your real name. You can't change this later.",
                                        "Your Real Name",
                                        "text",
                                    );
                                    await this.promptResetUserField(
                                        "email",
                                        "Let's check your email",
                                        "Make sure this is your School email. You can't change this later. This will be your login email.",
                                        "School Email",
                                        "email",
                                    );
                                }
                            });
                        } else {
                            this.closeModal();
                        }
                    } else {
                        this.closeModal();
                    }
                });
            }
        });
    }

    async promptResetUserField(
        field: "name" | "email",
        title: string,
        text: string,
        placeholder: string,
        inputType: "text" | "email",
    ) {
        const currentValue = this.user[field];

        await Swal.fire({
            title: title,
            text: text,
            icon: "question",
            showCancelButton: false,
            allowEscapeKey: false,
            allowOutsideClick: false,
            focusConfirm: false,
            confirmButtonColor: "#3085d6",
            confirmButtonText: `Submit`,
            input: inputType,
            inputPlaceholder: placeholder,
            inputValue: currentValue,
            preConfirm: (value: string) => {
                if (!value) {
                    void Swal.showValidationMessage(`Please enter your ${field}`);
                    return false;
                }
                return value;
            },
        }).then(async (result) => {
            if (result.isConfirmed) {
                const newValue = result.value;
                if (newValue !== currentValue) {
                    await this.updateUserData({ id: this.user.id, [field]: newValue });
                }
            }
        });
    }

    async updateUserData(data: any) {
        try {
            this.loader.setLoader(true);
            const res = await this.settingsService.updateUserData(data);
            if (!res.data.status) {
                throw new Error(res.data.message || "Error updating user data");
            }
        } catch (err) {
            this.snackbarService.handleError(err, "Error updating user data");
        } finally {
            this.loader.setLoader(false);
        }
    }

    updateUserNameAndEmail(name: string, email: string) {
        const params = {
            id: this.user.id,
            name: name,
            email: email,
        };

        this.classroomService
            .updateClassroomData(params)
            .then((res) => {
                if (res.data.status) {
                    this.closeModal();
                } else {
                }
            })
            .catch((err) => {
                console.error(err);
            })
            .finally(() => {
                this.loader.setLoader(false);
            });
    }

    closeModal() {
        this.classroomToken = "";
        void this.router.navigate(["/classroom"]);
    }

    acceptClassroom() {
        let params = {};
        if (!!this.newClassroomDetails.classroom.id) {
            params = {
                user_id: this.user.id,
                type: "addNewSchoolUsers",
                params: {
                    school_id: this.newClassroomDetails.school.id,
                    classroom_id: this.newClassroomDetails.classroom.id,
                    existing_user_email: "",
                    registered_user_id: { id: this.user.id, role: "3" },
                },
            };
        } else {
            params = {
                user_id: this.user.id,
                type: "addNewSchoolUsers",
                params: {
                    school_id: this.newClassroomDetails.school.id,
                    classroom_id: "",
                    existing_user_email: "",
                    registered_user_id: { id: this.user.id, role: "3" },
                },
            };
        }

        this.classroomService
            .updateClassroomData(params)
            .then((res) => {
                if (res.data.status) {
                    this.closeModal();
                } else {
                }
            })
            .catch((err) => {
                console.error(err);
            })
            .finally(() => {
                this.loader.setLoader(false);
            });
    }

    private alreadyDeleted() {
        void this.cookieService.deleteAll();
        this.localStorage.clear();
        setTimeout(() => {
            void this.router.navigate([""]);
        }, 1000);
    }

    private getFireData() {
        this.reviewService
            .getFire({ user_id: this.user.id })
            .then((res) => {
                if (res.data.status) {
                    this.fireData = res.data.results;
                    this.lessonService.setUnitReview(this.fireData);
                    if (this.fireData.haveReviewExercise) {
                        this.reviewService.setReviewMenu(true);
                        this.setFireImage();
                    } else {
                        this.noReviews = true;
                    }
                }
            })
            .catch((err) => {
                console.error(err);
            })
            .finally(() => {
                this.loader.setLoader(false);
            });
    }

    private setFireImage() {
        this.fireImage = this.learningPathService.getFireTypeFromStreak(this.fireData.FireData.fire_days);
    }

    getReviewImageUrl(fireImage: string, noReviews: boolean = false) {
        return this.learningPathService.getReviewImageUrl(fireImage, noReviews);
    }

    updateLevelImageUrl(event, levelId) {
        for (let i = 0; i < this.allLevels.length; ++i) {
            if (this.allLevels[i].id == levelId) {
                this.allLevels[i].image.ResizeImageUrl = this.defaultLevelImageUrl;
            }
        }
    }

    getLearningPath() {
        const params = {
            user_id: this.user.id,
            type: "classroom",
        };

        this.lessonService
            .getLearningPathDetails(params)
            .then((response: any) => {
                this.loader.setLoader(false);
                if (response.data.status) {
                    this.path = response.data.results;
                    if (this.path.levels.length > 0) {
                        this.path.levels.forEach((level) => {
                            if (!this.isDateBeforeToday(level.end_date)) {
                                this.activeLevels.push(level);
                            } else {
                                this.inactiveLevels.push(level);
                            }
                        });
                        this.allLevels = this.activeLevels.concat(this.inactiveLevels);
                        this.noLevel = false;
                        if (!this.localStorage.getItem("LevelID") || !this.setLevelFromLocalStorage()) {
                            this.setActiveLevel(!!this.activeLevels[0] ? this.activeLevels[0] : this.path.levels[0]);
                        }
                    } else {
                        this.noLevel = true;
                    }
                }
            })
            .catch((err) => {
                this.loader.setLoader(false);
                console.error(err);
            });
    }

    private isDateBeforeToday(date) {
        const scheduledDate = new Date(date.substring(0, 10));
        //scheduledDate.setDate(scheduledDate.getDate() + 1);
        const currentDate = new Date();
        const isBefore = scheduledDate < currentDate;
        return isBefore;
    }

    setActiveLevel(level) {
        /*** breadcrumb code star***/
        const breadcrumb = [
            {
                Name: "Classroom",
                URL: "/classroom",
            },
            {
                ID: level.id,
                Name: level.name,
                URL: "/classroom",
            },
        ];
        // this.localStorage.setItem('breadcrumb', JSON.stringify(breadcrumb));
        this.reviewService.setBreadcrumb(breadcrumb);
        /*** breadcrumb code end***/

        this.localStorage.setItem("LevelID", level.id);
        this.localStorage.removeItem("unitID");
        this.currentLevel = level;
        this.lessonService.setLevel(level);
    }

    setLevelFromLocalStorage() {
        const levelID: number = parseInt(this.localStorage.getItem("LevelID"));
        for (let i = 0; i < this.path.levels.length; i++) {
            const level = this.path.levels[i];
            if (level.id == levelID) {
                this.setActiveLevel(level);
                return true;
            }
        }

        return false;
    }

    /**
     * Finds where the user left off.
     * First find the furthest enabled unit. If it has been started then go to it,
     * if it has be completed then go to its review session, if it hasn't been
     * started, search backwards to see if there are optional units immediately preceeding
     * this unit that the user started. If so, if the unit isn't complete, then go to it.
     * If the unit is complete, since we don't have review completion info yet, just go
     * to the next unit.
     */
    goToWhereverUserLeftOff() {
        // Active level is set already in this class
        // Unit can be determined from path JSON
        // Review completion can be determined by looking at unit_N and unit_N+1 enable flags
        const units = this.currentLevel.units; // convenience variable
        let lastEnabledUnitIndex = -1; // index of last enabled unit
        let done = false; // double for loop break helper variable

        // Find latest unlocked unit
        for (const unit of units) {
            if (!unit.enable) {
                lastEnabledUnitIndex = Math.max(lastEnabledUnitIndex, 0);
                break;
            }
            ++lastEnabledUnitIndex;
        }

        // Figure out if user is in the lessons, the review or
        // started an optional unit before this unit
        for (let i = lastEnabledUnitIndex; i >= 0; --i) {
            const unit = units[i];
            if (unit.unitPercentage >= 100) {
                // review
                this.setLastUnitOrReview("review", unit);
                break;
            } else if (unit.unitPercentage > 0) {
                // latest obligatory unit
                this.setLastUnitOrReview("unit", unit);
                break;
            } else {
                // unit.unitPercentage <= 0
                // check for progress in optional units if
                // there are any directly preceeding the last enabled unit
                for (let j = i - 1; j >= 0; --j) {
                    if (j < 0) {
                        done = this.setLastUnitOrReview("unit", unit);
                        break;
                    }
                    const pastUnit = units[j];
                    if (pastUnit.optional) {
                        if (pastUnit.unitPercentage > 0 && pastUnit.unitPercentage < 100) {
                            // user started an optional unit and hasn't started an obligatory unit after it,
                            // so put them back into the optional unit that they didn't finish
                            done = this.setLastUnitOrReview("unit", pastUnit);
                            break;
                        } else if (pastUnit.unitPercentage >= 100) {
                            // either user needs to do optional unit review, or it's finished and
                            // they can continue to next unit. But we currently don't supply (FIXME)
                            // review complete info, so I will assume it's completed and we will
                            // throw them into the last enabled obligatory unit
                            done = this.setLastUnitOrReview("unit", unit);
                            break;
                        } else {
                            // then, optional unit wasn't attempted, so check the previous unit to
                            // see if it's optional.
                        }
                    } else {
                        // user at least completed one unit past any optional ones so throw them
                        // into the latest obligatory unit
                        done = this.setLastUnitOrReview("unit", unit);
                        break;
                    }
                }
            }

            if (done) {
                break;
            }
        }
    }

    /**
     * Set next last unit or review so unit.component goes there
     */
    setLastUnitOrReview(type, unit) {
        if (type != "unit" && type != "review") {
            return true;
        }
        const params: any = {
            type: type,
            unit: unit,
        };
        this.lessonService.setLastActiveUnitOrReview(params);
        return true;
    }

    goToVillage() {
        this.learningPathService.goToVillage(this.user.learningpath_id, this.user.id, this.currentLevel.id);
    }
}
