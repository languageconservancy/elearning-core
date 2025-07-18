import { ComponentFixture, TestBed, waitForAsync } from "@angular/core/testing";

import { FindFriendsComponent } from "./find-friends.component";

describe("FindFriendsComponent", () => {
    let component: FindFriendsComponent;
    let fixture: ComponentFixture<FindFriendsComponent>;

    beforeEach(waitForAsync(() => {
        void TestBed.configureTestingModule({
            declarations: [FindFriendsComponent],
        }).compileComponents();
    }));

    beforeEach(() => {
        fixture = TestBed.createComponent(FindFriendsComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it("should create", () => {
        expect(component).toBeTruthy();
    });
});
