import { ComponentFixture, TestBed } from "@angular/core/testing";

import { VirtualKeyboardComponent } from "./virtual-keyboard.component";

describe("KeyboardComponent", () => {
    let component: VirtualKeyboardComponent;
    let fixture: ComponentFixture<VirtualKeyboardComponent>;

    beforeEach(() => {
        void TestBed.configureTestingModule({
            declarations: [VirtualKeyboardComponent],
        }).compileComponents();
    });

    beforeEach(() => {
        fixture = TestBed.createComponent(VirtualKeyboardComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it("should create", () => {
        expect(component).toBeTruthy();
    });
});
