import { ComponentFixture, TestBed, waitForAsync } from "@angular/core/testing";

import { ImageZoomComponent } from "./image-zoom.component";

describe("ImageZoomComponent", () => {
    let component: ImageZoomComponent;
    let fixture: ComponentFixture<ImageZoomComponent>;

    beforeEach(waitForAsync(() => {
        void TestBed.configureTestingModule({
            declarations: [ImageZoomComponent],
        }).compileComponents();
    }));

    beforeEach(() => {
        fixture = TestBed.createComponent(ImageZoomComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it("should create", () => {
        expect(component).toBeTruthy();
    });
});
