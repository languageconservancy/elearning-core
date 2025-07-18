import { Directive, ElementRef, HostListener, OnDestroy, OnInit } from "@angular/core";
import { VirtualKeyboardService } from "../_services/virtual-keyboard.service";
import { Subscription } from "rxjs";

@Directive({
    selector: "[appAutoScrollModalInput]",
})
export class AutoScrollModalInputDirective implements OnInit, OnDestroy {
    private keyboardHeightSubscription: Subscription;
    private currentKeyboardHeight: number = 0;

    constructor(
        private el: ElementRef,
        private keyboardService: VirtualKeyboardService,
    ) {}

    ngOnInit() {
        // Subscribe to keyboard height changes
        this.keyboardHeightSubscription = this.keyboardService.keyboardHeightChanges.subscribe(
            (height) => {
                if (this.currentKeyboardHeight !== height) {
                    this.currentKeyboardHeight = height;
                    this.adjustScrollPosition();
                }
            },
        );
    }

    ngOnDestroy() {
        if (this.keyboardHeightSubscription) {
            this.keyboardHeightSubscription.unsubscribe();
        }
    }

    @HostListener("focus", ["$event.target"])
    @HostListener("click", ["$event.target"])
    onFocus() {
        this.adjustScrollPosition();
    }

    private adjustScrollPosition() {
        const elementBounds = this.el.nativeElement.getBoundingClientRect();
        const modalContent = this.el.nativeElement.closest(".modal");

        if (this.currentKeyboardHeight > 0 && modalContent) {
            // Calculate the required scroll offset
            const viewportBottomToElementBottom = window.innerHeight - elementBounds.bottom;
            const elementBottomAboveKeyboardTop =
                viewportBottomToElementBottom - this.currentKeyboardHeight;
            if (elementBottomAboveKeyboardTop < 0) {
                // Scroll the modal content to make the input visible
                modalContent.scrollTop += Math.abs(elementBottomAboveKeyboardTop) + 10; // Add some padding
            }
        }
    }
}
