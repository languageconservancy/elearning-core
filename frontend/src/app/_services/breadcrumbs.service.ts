import { BehaviorSubject } from "rxjs";
import { Injectable } from "@angular/core";
import { Routes } from "app/shared/utils/elearning-types";

export enum BreadcrumbType {
    LearningPath = "learningPath",
    Level = "level",
    Unit = "unit",
    Classroom = "classroom",
    ClassroomLevel = "classroomLevel",
    Review = "review",
}

export interface Breadcrumb {
    type: BreadcrumbType;
    name: string;
    url: string;
}

@Injectable({ providedIn: "root" })
export class BreadcrumbsService {
    private readonly STORAGE_KEY = "breadcrumb";
    private breadcrumbsSubject = new BehaviorSubject<Breadcrumb[]>(this.loadFromStorage());

    public breadcrumbs$ = this.breadcrumbsSubject.asObservable();

    private loadFromStorage(): Breadcrumb[] {
        const breadcrumbsStr = localStorage.getItem(this.STORAGE_KEY);
        return breadcrumbsStr ? JSON.parse(breadcrumbsStr) : [];
    }

    private saveToStorage(breadcrumbs: Breadcrumb[]) {
        localStorage.setItem(this.STORAGE_KEY, JSON.stringify(breadcrumbs));
    }

    setBreadcrumbs(breadcrumbs: Breadcrumb[]): void {
        this.breadcrumbsSubject.next(breadcrumbs);
        this.saveToStorage(breadcrumbs);
    }

    clearBreadcrumbs() {
        this.breadcrumbsSubject.next([]);
        localStorage.removeItem(this.STORAGE_KEY);
    }

    getLearningPathBreadcrumbFromStorage() {
        const breadcrumbs = this.loadFromStorage();
        return breadcrumbs.find((breadcrumb) => breadcrumb.type === BreadcrumbType.LearningPath);
    }

    getLevelBreadcrumbFromStorage() {
        const breadcrumbs = this.loadFromStorage();
        return breadcrumbs.find((breadcrumb) => breadcrumb.type === BreadcrumbType.Level);
    }

    getUnitBreadcrumbFromStorage() {
        const breadcrumbs = this.loadFromStorage();
        return breadcrumbs.find((breadcrumb) => breadcrumb.type === BreadcrumbType.Unit);
    }

    getLearningPathBreadcrumb(learningPath: string) {
        return {
            type: BreadcrumbType.LearningPath,
            name: learningPath,
            url: `/${Routes.LearningPath}`,
        };
    }

    getLevelBreadcrumb(level: string) {
        return {
            type: BreadcrumbType.Level,
            name: level,
            url: `/${Routes.StartLearning}`,
        };
    }

    getUnitBreadcrumb(unit: string) {
        return {
            type: BreadcrumbType.Unit,
            name: unit,
            url: `/${Routes.LessonsAndExercises}`,
        };
    }

    getReviewBreadcrumb() {
        return {
            type: BreadcrumbType.Review,
            name: "Review",
            url: `/${Routes.Review}`,
        };
    }

    getClassroomBreadcrumb() {
        return {
            type: BreadcrumbType.Classroom,
            name: "Classroom",
            url: `/${Routes.Classroom}`,
        };
    }

    getClassroomLevelBreadcrumb(level: string) {
        return {
            type: BreadcrumbType.ClassroomLevel,
            name: level,
            url: `/${Routes.Classroom}`,
        };
    }

    setLearningPathReviewBreadcrumbs(learningPath: string) {
        let learningPathBreadcrumb = null;
        if (!learningPath) {
            learningPathBreadcrumb = this.getLearningPathBreadcrumbFromStorage();
            learningPath = learningPathBreadcrumb.name;
        }
        if (!learningPath) {
            console.error("Learning path is required to set learning path review breadcrumb", {
                learningPath,
            });
            return;
        }
        const breadcrumbs: Breadcrumb[] = [
            this.getLearningPathBreadcrumb(learningPath),
            this.getReviewBreadcrumb(),
        ];

        this.setBreadcrumbs(breadcrumbs);
    }

    setLevelReviewBreadcrumbs(learningPath: string, level: string) {
        let learningPathBreadcrumb = null;
        let levelBreadcrumb = null;
        if (!learningPath) {
            learningPathBreadcrumb = this.getLearningPathBreadcrumbFromStorage();
            learningPath = learningPathBreadcrumb.name;
        }
        if (!level) {
            levelBreadcrumb = this.getLevelBreadcrumbFromStorage();
            level = levelBreadcrumb.name;
        }
        if (!learningPath || !level) {
            console.error("Learning path and level are required to set level review breadcrumb.", {
                learningPath,
                level,
            });
            return;
        }
        const breadcrumbs: Breadcrumb[] = [
            this.getLearningPathBreadcrumb(learningPath),
            this.getLevelBreadcrumb(level),
            this.getReviewBreadcrumb(),
        ];

        this.setBreadcrumbs(breadcrumbs);
    }

    setUnitReviewBreadcrumbs(learningPath: string, level: string, unit: string) {
        let learningPathBreadcrumb = null;
        let levelBreadcrumb = null;
        let unitBreadcrumb = null;
        if (!learningPath) {
            learningPathBreadcrumb = this.getLearningPathBreadcrumbFromStorage();
            learningPath = learningPathBreadcrumb.name;
        }
        if (!level) {
            levelBreadcrumb = this.getLevelBreadcrumbFromStorage();
            level = levelBreadcrumb.name;
        }
        if (!unit) {
            unitBreadcrumb = this.getUnitBreadcrumbFromStorage();
            unit = unitBreadcrumb.name;
        }

        if (!learningPath || !level || !unit) {
            console.error(
                "Learning path, level, and unit are required to set unit review breadcrumb.",
                {
                    learningPath,
                    level,
                    unit,
                },
            );
            return;
        }

        const breadcrumbs: Breadcrumb[] = [
            this.getLearningPathBreadcrumb(learningPath),
            this.getLevelBreadcrumb(level),
            this.getUnitBreadcrumb(unit),
            this.getReviewBreadcrumb(),
        ];

        this.setBreadcrumbs(breadcrumbs);
    }

    setLearningPathBreadcrumbs(learningPath: string) {
        let learningPathBreadcrumb = null;
        if (!learningPath) {
            learningPathBreadcrumb = this.getLearningPathBreadcrumbFromStorage();
            learningPath = learningPathBreadcrumb.name;
        }
        if (!learningPath) {
            console.error("Learning path is required to set learning path breadcrumb", {
                learningPath,
            });
            return;
        }
        const breadcrumbs: Breadcrumb[] = [this.getLearningPathBreadcrumb(learningPath)];

        this.setBreadcrumbs(breadcrumbs);
    }

    setLevelBreadcrumbs(learningPath: string, level: string) {
        let learningPathBreadcrumb = null;
        let levelBreadcrumb = null;
        if (!learningPath) {
            learningPathBreadcrumb = this.getLearningPathBreadcrumbFromStorage();
            learningPath = learningPathBreadcrumb.name;
        }
        if (!level) {
            levelBreadcrumb = this.getLevelBreadcrumbFromStorage();
            level = levelBreadcrumb.name;
        }
        if (!learningPath || !level) {
            console.error("Learning path and level are required to set level breadcrumb.", {
                learningPath,
                level,
            });
            return;
        }
        const breadcrumbs: Breadcrumb[] = [
            this.getLearningPathBreadcrumb(learningPath),
            this.getLevelBreadcrumb(level),
        ];

        this.setBreadcrumbs(breadcrumbs);
    }

    setClassroomLevelBreadcrumbs(level: string) {
        let levelBreadcrumb = null;
        if (!level) {
            levelBreadcrumb = this.getLevelBreadcrumbFromStorage();
            level = levelBreadcrumb.name;
        }
        if (!level) {
            console.error("Level is required to set classroom level breadcrumb", {
                level,
            });
            return;
        }
        const breadcrumbs: Breadcrumb[] = [
            this.getClassroomBreadcrumb(),
            this.getClassroomLevelBreadcrumb(level),
        ];

        this.setBreadcrumbs(breadcrumbs);
    }

    setUnitBreadcrumbs(learningPath: string, level: string, unit: string) {
        let learningPathBreadcrumb = null;
        let levelBreadcrumb = null;
        let unitBreadcrumb = null;
        if (!learningPath) {
            learningPathBreadcrumb = this.getLearningPathBreadcrumbFromStorage();
            learningPath = learningPathBreadcrumb.name;
        }
        if (!level) {
            levelBreadcrumb = this.getLevelBreadcrumbFromStorage();
            level = levelBreadcrumb.name;
        }
        if (!unit) {
            unitBreadcrumb = this.getUnitBreadcrumbFromStorage();
            unit = unitBreadcrumb.name;
        }
        if (!learningPath || !level || !unit) {
            console.error("Learning path, level, and unit are required to set unit breadcrumb.", {
                learningPath,
                level,
                unit,
            });
            return;
        }

        const breadcrumbs: Breadcrumb[] = [
            this.getLearningPathBreadcrumb(learningPath),
            this.getLevelBreadcrumb(level),
            this.getUnitBreadcrumb(unit),
        ];

        this.setBreadcrumbs(breadcrumbs);
    }
}
