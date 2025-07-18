import { ComponentFixture, TestBed, waitForAsync } from "@angular/core/testing";

import { RewardPopupsComponent } from "./reward-popups.component";

describe("RewardPopupsComponent", () => {
    let component: RewardPopupsComponent;
    let fixture: ComponentFixture<RewardPopupsComponent>;

    beforeEach(waitForAsync(() => {
        void TestBed.configureTestingModule({
            declarations: [RewardPopupsComponent],
        }).compileComponents();
    }));

    beforeEach(() => {
        fixture = TestBed.createComponent(RewardPopupsComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it("should create", () => {
        expect(component).toBeTruthy();
    });
});
