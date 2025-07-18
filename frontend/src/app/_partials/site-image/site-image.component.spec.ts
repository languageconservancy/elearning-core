import { ComponentFixture, TestBed, waitForAsync } from "@angular/core/testing";

import { SiteImageComponent } from "./site-image.component";

describe("SiteImageComponent", () => {
    let component: SiteImageComponent;
    let fixture: ComponentFixture<SiteImageComponent>;

    beforeEach(waitForAsync(() => {
        void TestBed.configureTestingModule({
            declarations: [SiteImageComponent],
        }).compileComponents();
    }));

    beforeEach(() => {
        fixture = TestBed.createComponent(SiteImageComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it("should create", () => {
        expect(component).toBeTruthy();
    });
});
