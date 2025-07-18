/* eslint-disable @typescript-eslint/unbound-method */
import { Component, OnInit, OnDestroy, ViewChild } from "@angular/core";
import { Router } from "@angular/router";
import { UntypedFormGroup, UntypedFormControl, Validators } from "@angular/forms";
import { CdkDragDrop, moveItemInArray, transferArrayItem } from "@angular/cdk/drag-drop";
import { Subscription } from "rxjs";
import { CookieService } from "app/_services/cookie.service";

import { TeacherService } from "app/_services/teacher.service";
import { ClassroomService } from "app/_services/classroom.service";
import { Loader } from "app/_services/loader.service";
import { SnackbarService } from "app/_services/snackbar.service";
import { environment } from "environments/environment";

type Unit = {
    id: number;
    name: string;
    description: string;
    sequence?: number;
};

type LevelUnit = {
    id: number;
    learningpath_id: number;
    level_id: number;
    sequence: number;
    unit_id: number;
    optional: number;
    unit: Unit;
};

type TeacherLevel = {
    id: number;
    level_id: number;
    owner_id: number;
    school_id: number;
    level: {
        id: number;
        name: string;
        description: string;
        image_id: number;
        classrooms: any;
        image: any;
    };
};

type Level = {
    id: number;
    image_id: number;
    name: string;
    units: Array<Unit>;
};

type Path = {
    id: number;
    label: string;
    FullImageUrl: string;
    description: string;
    levels: Array<Level>;
};

@Component({
    selector: "app-teacher-lessons",
    templateUrl: "./teacher-lessons.component.html",
    styleUrls: ["./teacher-lessons.component.scss"],
})
export class TeacherLessonsComponent implements OnInit, OnDestroy {
    private teacherSubscription: Subscription;
    private schoolSubscription: Subscription;
    @ViewChild("formModal") formModal;
    public newLevelForm: UntypedFormGroup;

    public allAvailablePaths: Array<Path> = [];
    public availablePaths: Array<Path> = [];

    public currentAvailablePathIndex: number = -1;
    public currentAvailableLevelIndex: number = -1;

    public debug: boolean = !environment.production;
    public selectedLevelId = 7;
    public school: any = [];
    public teacher: any = [];
    public activeUnitId: number = 0;
    public activeUnitDetails: any = [];
    public listTeacherLevels: Array<TeacherLevel> = [];
    public activeLevelIndex = -1;
    public listLevelUnits: Array<LevelUnit> = [];
    public unsavedUnitChanges: boolean = false;

    constructor(
        private classroomService: ClassroomService,
        private teacherService: TeacherService,
        private router: Router,
        private loader: Loader,
        private cookieService: CookieService,
        private snackbarService: SnackbarService,
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

        this.teacherService.setTab("teacher-lessons");
        this.teacherSubscription = this.teacherService.teacherObj.subscribe((teacher) => {
            this.teacher = teacher;
        });
        this.schoolSubscription = this.teacherService.currentSchool.subscribe((school) => {
            void (async () => {
                this.school = school;
                await this.getAvailableUnits();
                await this.getTeacherLevels();
            })();
        });
    }

    async ngOnInit() {
        //setup forms
        this.newLevelForm = new UntypedFormGroup({
            name: new UntypedFormControl("", [Validators.required, this.validateBlankValue.bind(this)]),
            description: new UntypedFormControl(""),
            newLevelContent: new UntypedFormControl("-2"),
        });

        await this.getAvailableUnits();
        await this.getTeacherLevels();
    }

    ngOnDestroy() {
        this.teacherSubscription.unsubscribe();
        this.schoolSubscription.unsubscribe();
    }

    async getAvailableUnits() {
        try {
            const res: any = await this.classroomService.getAvailablePaths({ user_id: this.teacher.id });
            this.allAvailablePaths = res.data.results["availablePaths"];
            this.availablePaths = res.data.results["availablePaths"];
            if (this.debug) console.debug("Available paths", this.availablePaths);
            this.setCurrentAvailablePath(0);
        } catch (err) {
            console.error(err);
            this.snackbarService.showSnackbar({
                status: false,
                msg: "Something went wrong. Please try again soon. " + err.message,
            });
        }
    }

    setCurrentAvailableLevel(idx: number) {
        this.currentAvailableLevelIndex = idx;
    }

    setCurrentAvailablePath(idx: number) {
        this.currentAvailablePathIndex = idx;
        this.setCurrentAvailableLevel(0);
    }

    async getTeacherLevels(idx = 0) {
        try {
            const res = await this.classroomService.getTeacherLevels({
                user_id: this.teacher.id,
                school_id: this.school.school_id,
            });
            this.listTeacherLevels = res.data.results["teacherLevels"].reverse();
            if (this.listTeacherLevels.length > 0) {
                await this.setActiveTeacherLessonPlan(idx);
            }
        } catch (err) {
            console.error(err);
            this.snackbarService.showSnackbar({
                status: false,
                msg: "Something went wrong. Please try again soon. " + err.message,
            });
        }
    }

    async setActiveTeacherLessonPlan(idx: number) {
        this.activeLevelIndex = idx;
        await this.getLevelEditor(this.listTeacherLevels[idx].level.id);
        this.resetPaths();
        this.removeLessonUnitsFromAvailable();
    }

    async getLevelEditor(level_id: number) {
        try {
            const res = await this.classroomService.getTeacherLevelUnits({
                user_id: this.teacher.id,
                level_id: level_id,
            });
            this.listLevelUnits = res.data.results["teacherLevelUnits"] ?? [];
            if (this.debug) console.debug("Teacher level units", this.listLevelUnits);
        } catch (err) {
            console.error(err);
            this.snackbarService.showSnackbar({
                status: false,
                msg: "Something went wrong. Please try again soon. " + err.message,
            });
        }
    }

    resetPaths() {
        if (this.allAvailablePaths.length === 0) {
            console.warn("No available paths to reset to");
            return;
        }
        // Restore all available paths
        this.availablePaths = JSON.parse(JSON.stringify(this.allAvailablePaths));
        this.setCurrentAvailablePath(0);
    }

    /**
     * Remove units from available paths that are already in the teacher's lesson plan
     */
    removeLessonUnitsFromAvailable() {
        this.listLevelUnits.forEach((levelUnit: LevelUnit) => {
            this.availablePaths.forEach((path: Path) => {
                path.levels.forEach((level: Level) => {
                    level.units = level.units.filter((u) => u.id != levelUnit.unit.id);
                });
            });
        });
    }

    drop(event: CdkDragDrop<LevelUnit[]>) {
        if (event.previousContainer === event.container) {
            moveItemInArray(event.container.data, event.previousIndex, event.currentIndex);
            if (event.previousIndex !== event.currentIndex) {
                this.unsavedUnitChanges = true;
            }
        } else {
            this.unsavedUnitChanges = true;
            transferArrayItem(
                event.previousContainer.data,
                event.container.data,
                event.previousIndex,
                event.currentIndex,
            );
        }
    }

    sortUnits(pathIdx: number, levelIdx: number) {
        this.availablePaths[pathIdx].levels[levelIdx].units.sort((a, b) => {
            return a.sequence - b.sequence;
        });
    }

    removeUnitFromLesson(idx: number) {
        // Unit to remove
        const unit = this.listLevelUnits[idx].unit;

        // Add unit back into each available path/level that normally contains it and re-sort
        this.allAvailablePaths.forEach((path, pathIdx: number) => {
            path.levels.forEach((level, levelIdx: number) => {
                const availableUnitIdx = this.allAvailablePaths[pathIdx].levels[levelIdx].units.findIndex(
                    (u) => u.id === unit.id,
                );
                if (
                    availableUnitIdx !== -1 &&
                    this.availablePaths[pathIdx].levels[levelIdx].units.findIndex((u) => u.id == unit.id) === -1
                ) {
                    // Add unit back into available path/level using unit from
                    // allAvailablePaths to include all fields (sequence) for sorting
                    if (this.debug) {
                        console.debug(
                            `Adding unit "${unit.name}" back into path "${path.label}", level "${level.name}"`,
                        );
                    }
                    this.availablePaths[pathIdx].levels[levelIdx].units.push(
                        this.allAvailablePaths[pathIdx].levels[levelIdx].units[availableUnitIdx],
                    );
                    this.sortUnits(pathIdx, levelIdx);
                }
            });
        });

        // Remove level unit from teacher lesson
        this.listLevelUnits.splice(idx, 1);

        this.unsavedUnitChanges = true;
    }

    createLevelUnitFromAvailableUnit(unitIdx: number): LevelUnit {
        const unit =
            this.availablePaths[this.currentAvailablePathIndex].levels[this.currentAvailableLevelIndex].units[unitIdx];
        const levelUnit = {
            id: null,
            learningpath_id: null,
            level_id: this.listTeacherLevels[this.activeLevelIndex].level.id,
            sequence: this.listLevelUnits.length + 1,
            unit_id: unit.id,
            optional: 0,
            unit: {
                id: unit.id,
                name: unit.name,
                description: unit.description,
            },
        };
        return levelUnit;
    }

    addUnitToLesson(idx: number) {
        const levelUnit = this.createLevelUnitFromAvailableUnit(idx);
        this.listLevelUnits.push(levelUnit);

        this.availablePaths[this.currentAvailablePathIndex].levels[this.currentAvailableLevelIndex].units.splice(
            idx,
            1,
        );
        this.unsavedUnitChanges = true;
    }

    openModal() {
        this.formModal.nativeElement.className = "modal fade show";
    }
    closeModal() {
        this.formModal.nativeElement.className = "modal hide";
    }

    saveTeacherLevelUnits() {
        if (!!this.listLevelUnits) {

            // Clean data for api call
            for (let i = 0; i < this.listLevelUnits.length; i++) {
                this.listLevelUnits[i].sequence = i + 1;
            }

            this.loader.setLoader(true);
            const params = {
                level_units: this.listLevelUnits,
                classrooms: this.listTeacherLevels[this.activeLevelIndex].level.classrooms,
            };

            const updateParams = {
                user_id: this.teacher.id,
                type: "updateTeacherLevelUnits",
                level_id: this.listTeacherLevels[this.activeLevelIndex].level.id,
                params: params,
            };

            this.classroomService
                .updateClassroomData(updateParams)
                .then((res) => {
                    if (!res.data.status) {
                        throw res.data;
                    }
                    this.unsavedUnitChanges = false;
                })
                .catch((err) => {
                    console.error(err);
                    this.snackbarService.showSnackbar({ status: false, msg: err.message });
                })
                .finally(() => {
                    this.loader.setLoader(false);
                });
        } else {
            this.snackbarService.showSnackbar({ status: false, msg: "No teacher level units to save." });
        }
    }

    createNewLevel(form) {
        if (form.valid) {
            this.loader.setLoader(true);
            let levelId = null;
            let isAllUnits = 0;
            if (form.value.newLevelContent >= 0) {
                levelId = this.listTeacherLevels[form.value.newLevelContent].level_id;
            }
            if (form.value.newLevelContent == -2) {
                isAllUnits = 1;
            }
            const levelParams = {
                name: form.value.name,
                description: form.value.description,
                image_id: null, //default school image
                level_id: levelId,
                is_all_units: isAllUnits,
                school_id: this.school.school.id,
            };
            const params = {
                user_id: this.teacher.id,
                type: "newTeacherLevel",
                params: levelParams,
            };
            this.classroomService
                .updateClassroomData(params)
                .then(() => {
                    this.closeModal();
                    void this.getTeacherLevels();
                })
                .catch((err) => {
                    console.error(err);
                })
                .finally(() => {
                    this.loader.setLoader(false);
                });
        } else {
            this.snackbarService.showSnackbar({
                status: false,
                msg: "Please fill in all fields with valid data before moving forward.",
            });
        }
    }

    private validateBlankValue(control: UntypedFormControl): any {
        if (this.newLevelForm) {
            return control.value.trim() === "" ? { emptyValue: true } : null;
        }
    }
}
