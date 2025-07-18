import { ComponentFixture, TestBed, waitForAsync } from "@angular/core/testing";
import { CookieService } from "app/_services/cookie.service";
import { HttpClientTestingModule } from "@angular/common/http/testing";
import { RouterTestingModule } from "@angular/router/testing";

import { McqComponent } from "./mcq.component";
import { LocalStorageService } from "app/_services/local-storage.service";
import { Loader } from "app/_services/loader.service";
import { PipesModule } from "app/_pipes/pipes.module";
import { LessonsService } from "app/_services/lessons.service";
import { ReviewService } from "app/_services/review.service";
import { ExerciseService } from "app/_services/exercise.service";

describe("McqComponent", () => {
    let component: McqComponent;
    let fixture: ComponentFixture<McqComponent>;

    beforeEach(waitForAsync(() => {
        void TestBed.configureTestingModule({
            declarations: [McqComponent],
            providers: [CookieService, LocalStorageService, Loader, LessonsService, ReviewService, ExerciseService],
            imports: [HttpClientTestingModule, RouterTestingModule, PipesModule],
        }).compileComponents();
    }));

    beforeEach(() => {
        fixture = TestBed.createComponent(McqComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it("should create", () => {
        expect(component).toBeTruthy();
    });
});
