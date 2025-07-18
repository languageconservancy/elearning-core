import { Directive, ElementRef, OnInit, OnDestroy } from "@angular/core";
import { VirtualKeyboardService } from "../_services/virtual-keyboard.service";
import { Subscription } from "rxjs";

@Directive({
    selector: "[appAdjustModalForKeyboard]",
})
export class AdjustModalForKeyboardDirective implements OnInit, OnDestroy {
    private keyboardHeightSubscription: Subscription;
    private currentKeyboardHeight: number = 0;

    constructor(
        private el: ElementRef,
        private keyboardService: VirtualKeyboardService,
    ) {}

    ngOnInit() {
        this.keyboardHeightSubscription = this.keyboardService.keyboardHeightChanges.subscribe(
            (height) => {
                this.currentKeyboardHeight = height;
                this.adjustModalPosition();
            },
        );
    }

    ngOnDestroy() {
        if (this.keyboardHeightSubscription) {
            this.keyboardHeightSubscription.unsubscribe();
        }
    }

    private adjustModalPosition() {
        const modalElement = this.el.nativeElement;

        if (this.currentKeyboardHeight > 0) {
            // Adjust padding or position for keyboard
            modalElement.style.paddingBottom = `${this.currentKeyboardHeight}px`;
        } else {
            // Reset padding when keyboard is hidden
            modalElement.style.paddingBottom = "";
        }
    }
}
