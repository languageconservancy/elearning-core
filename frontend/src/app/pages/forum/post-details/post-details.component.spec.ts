import { ComponentFixture, TestBed, waitForAsync } from "@angular/core/testing";

import { PostDetailsComponent } from "./post-details.component";

describe("PostDetailsComponent", () => {
    let component: PostDetailsComponent;
    let fixture: ComponentFixture<PostDetailsComponent>;

    beforeEach(waitForAsync(() => {
        void TestBed.configureTestingModule({
            declarations: [PostDetailsComponent],
        }).compileComponents();
    }));

    beforeEach(() => {
        fixture = TestBed.createComponent(PostDetailsComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it("should create", () => {
        expect(component).toBeTruthy();
    });
});
