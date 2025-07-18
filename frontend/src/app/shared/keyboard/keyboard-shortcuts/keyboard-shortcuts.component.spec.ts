import { ComponentFixture, TestBed, waitForAsync } from "@angular/core/testing";

import { KeyboardShortcutsComponent } from "./keyboard-shortcuts.component";

describe("KeyboardShortcutsComponent", () => {
    // let component: KeyboardShortcutsComponent;
    let fixture: ComponentFixture<KeyboardShortcutsComponent>;

    beforeEach(waitForAsync(() => {
        void TestBed.configureTestingModule({
            declarations: [KeyboardShortcutsComponent],
        }).compileComponents();
    }));

    beforeEach(() => {
        fixture = TestBed.createComponent(KeyboardShortcutsComponent);
        // component = fixture.componentInstance;
        fixture.detectChanges();
    });
});
