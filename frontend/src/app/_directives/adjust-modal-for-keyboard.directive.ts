import { Directive, ElementRef, inject, OnInit, OnDestroy } from "@angular/core";
import { VirtualKeyboardService } from "../_services/virtual-keyboard.service";
import { Subscription } from "rxjs";

@Directive({
    selector: "[appAdjustModalForKeyboard]",
    standalone: true,
})
export class AdjustModalForKeyboardDirective implements OnInit, OnDestroy {
    private el = inject(ElementRef);
    private keyboardService = inject(VirtualKeyboardService);

    private keyboardHeightSubscription: Subscription;
    private currentKeyboardHeight: number = 0;

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
