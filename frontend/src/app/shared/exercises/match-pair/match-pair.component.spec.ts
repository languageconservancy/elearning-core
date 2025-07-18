import { ComponentFixture, TestBed, waitForAsync } from "@angular/core/testing";
import { CookieService as NgCookieService } from "ngx-cookie-service";

import { LocalStorageService } from "app/_services/local-storage.service";
import { LessonsService } from "app/_services/lessons.service";
import { CookieService } from "app/_services/cookie.service";
import { ReviewService } from "app/_services/review.service";
import { ExerciseService } from "app/_services/exercise.service";
import { AudioService } from "app/_services/audio.service";
import { Loader } from "app/_services/loader.service";
import { KeyboardService } from "app/shared/keyboard/keyboard.service";
import { MatchPairComponent } from "./match-pair.component";

describe("MatchPairComponent", () => {
    let component: MatchPairComponent;
    let fixture: ComponentFixture<MatchPairComponent>;

    beforeEach(waitForAsync(() => {
        void TestBed.configureTestingModule({
            declarations: [MatchPairComponent],
            imports: [],
            providers: [
                CookieService,
                NgCookieService,
                LessonsService,
                LocalStorageService,
                ReviewService,
                ExerciseService,
                AudioService,
                Loader,
                KeyboardService,
            ],
        }).compileComponents();
    }));

    beforeEach(() => {
        fixture = TestBed.createComponent(MatchPairComponent);
        component = fixture.componentInstance;
        component.sessionType = "exercise";
        fixture.detectChanges();
    });

    it("should create", () => {
        expect(component).toBeTruthy();
    });
});
