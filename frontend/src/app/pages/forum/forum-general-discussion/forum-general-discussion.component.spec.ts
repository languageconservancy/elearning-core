import { ComponentFixture, TestBed, waitForAsync } from "@angular/core/testing";

import { ForumGeneralDiscussionComponent } from "./forum-general-discussion.component";

describe("ForumGeneralDiscussionComponent", () => {
    let component: ForumGeneralDiscussionComponent;
    let fixture: ComponentFixture<ForumGeneralDiscussionComponent>;

    beforeEach(waitForAsync(() => {
        void TestBed.configureTestingModule({
            declarations: [ForumGeneralDiscussionComponent],
        }).compileComponents();
    }));

    beforeEach(() => {
        fixture = TestBed.createComponent(ForumGeneralDiscussionComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it("should create", () => {
        expect(component).toBeTruthy();
    });
});
