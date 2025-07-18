import { ComponentFixture, TestBed, waitForAsync } from "@angular/core/testing";

import { FriendSidebarComponent } from "./friend-sidebar.component";

describe("FriendSidebarComponent", () => {
    let component: FriendSidebarComponent;
    let fixture: ComponentFixture<FriendSidebarComponent>;

    beforeEach(waitForAsync(() => {
        void TestBed.configureTestingModule({
            declarations: [FriendSidebarComponent],
        }).compileComponents();
    }));

    beforeEach(() => {
        fixture = TestBed.createComponent(FriendSidebarComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it("should create", () => {
        expect(component).toBeTruthy();
    });
});
