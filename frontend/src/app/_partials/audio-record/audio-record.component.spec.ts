import { ComponentFixture, TestBed, waitForAsync } from "@angular/core/testing";

import { AudioRecordComponent } from "./audio-record.component";

describe("AudioRecordComponent", () => {
    let component: AudioRecordComponent;
    let fixture: ComponentFixture<AudioRecordComponent>;

    beforeEach(waitForAsync(() => {
        void TestBed.configureTestingModule({
            declarations: [AudioRecordComponent],
        }).compileComponents();
    }));

    beforeEach(() => {
        fixture = TestBed.createComponent(AudioRecordComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it("should create", () => {
        expect(component).toBeTruthy();
    });
});
