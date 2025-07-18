import { ComponentFixture, TestBed, waitForAsync } from "@angular/core/testing";

import { SettingSidebarComponent } from "./setting-sidebar.component";

describe("SettingSidebarComponent", () => {
    let component: SettingSidebarComponent;
    let fixture: ComponentFixture<SettingSidebarComponent>;

    beforeEach(waitForAsync(() => {
        void TestBed.configureTestingModule({
            declarations: [SettingSidebarComponent],
        }).compileComponents();
    }));

    beforeEach(() => {
        fixture = TestBed.createComponent(SettingSidebarComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it("should create", () => {
        expect(component).toBeTruthy();
    });
});
