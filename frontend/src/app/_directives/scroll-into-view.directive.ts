import { Directive, HostListener, OnInit, ElementRef, OnDestroy } from "@angular/core";
import { VirtualKeyboardService } from "../_services/virtual-keyboard.service";
import { Subscription } from "rxjs";
import { environment } from "../../environments/environment";

@Directive({
    // Apply to all input/textarea elements with this directive
    selector: "[appScrollIntoView]",
})
export class ScrollIntoViewDirective implements OnInit, OnDestroy {
    private keyboardHeightSubscription: Subscription;
    private navbarHeightSubscription: Subscription;
    private currentKeyboardHeight: number = 0;
    private navbarHeight: number = 0;
    private scrollOffset: number = 10;
    private lineHeight: number = 30;

    constructor(
        private el: ElementRef,
        private keyboardService: VirtualKeyboardService,
    ) {}

    ngOnInit() {
        this.keyboardHeightSubscription = this.keyboardService.keyboardHeightChanges.subscribe(
            (height) => {
                this.currentKeyboardHeight = height;
                setTimeout(() => {
                    this.adjustScrollPosition();
                }, 250);
            },
        );

        this.navbarHeightSubscription = this.keyboardService.navbarHeightChanges.subscribe(
            (height) => {
                this.navbarHeight = height;
            },
        );
    }

    ngOnDestroy() {
        this.keyboardHeightSubscription.unsubscribe();
        this.navbarHeightSubscription.unsubscribe();
    }

    @HostListener("focus", ["$event.target"])
    @HostListener("click", ["$event.target"])
    onFocus() {
        this.adjustScrollPosition();
    }

    private adjustScrollPosition() {
        if (this.currentKeyboardHeight > 0) {
            const elementBounds = this.el.nativeElement.getBoundingClientRect();
            // const offsetFromNavbar = elementBounds.top - this.navbarHeight;
            // const offsetFromViewportBottom = window.innerHeight - elementBounds.top;
            // const offsetFromKeyboardTop = offsetFromViewportBottom - this.currentKeyboardHeight;
            // Get location of middle between navbar bottom and keyboard
            const middle =
                (window.innerHeight - this.navbarHeight - this.currentKeyboardHeight) / 2;
            // Get location of top of element
            const elementTop = elementBounds.top;

            // Get font size of element
            const fontSize = this.getFontSize(this.el.nativeElement);
            // If the element is above the middle, scroll down
            // If the element is below the middle, scroll up
            // If the element is in the middle, do nothing
            let targetScrollY = 0;
            if (elementTop + fontSize <= middle) {
                targetScrollY = window.scrollY - (middle - elementTop + fontSize);
            } else {
                targetScrollY = window.scrollY + (elementTop - middle - fontSize);
            }
            targetScrollY = Math.max(
                0,
                Math.min(targetScrollY, document.body.scrollHeight - window.innerHeight),
            );

            setTimeout(() => {
                window.scrollTo({
                    top: targetScrollY,
                    behavior: "smooth",
                });
            }, 250);
        } else {
            if (!environment.production) {
                console.debug("No keyboard height, not scrolling");
            }
        }
    }

    private getFontSize(element: HTMLElement): number {
        const fontSize = window.getComputedStyle(element).fontSize;
        return parseFloat(fontSize);
    }
}
