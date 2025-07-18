import { ComponentFixture, TestBed, waitForAsync } from "@angular/core/testing";

import { AddFriendsComponent } from "./add-friends.component";

describe("AddFriendsComponent", () => {
    let component: AddFriendsComponent;
    let fixture: ComponentFixture<AddFriendsComponent>;

    beforeEach(waitForAsync(() => {
        void TestBed.configureTestingModule({
            declarations: [AddFriendsComponent],
        }).compileComponents();
    }));

    beforeEach(() => {
        fixture = TestBed.createComponent(AddFriendsComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it("should create", () => {
        expect(component).toBeTruthy();
    });
});
