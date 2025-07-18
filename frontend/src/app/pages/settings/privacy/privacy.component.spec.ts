import { ComponentFixture, TestBed, waitForAsync } from "@angular/core/testing";

import { PrivacyComponent } from "./privacy.component";

describe("PrivacyComponent", () => {
    let component: PrivacyComponent;
    let fixture: ComponentFixture<PrivacyComponent>;

    beforeEach(waitForAsync(() => {
        void TestBed.configureTestingModule({
            declarations: [PrivacyComponent],
        }).compileComponents();
    }));

    beforeEach(() => {
        fixture = TestBed.createComponent(PrivacyComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it("should create", () => {
        expect(component).toBeTruthy();
    });
});
