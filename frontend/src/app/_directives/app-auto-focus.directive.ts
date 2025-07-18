import { Directive, ElementRef, AfterViewInit } from "@angular/core";

@Directive({
    selector: "[appAutoFocus]",
})
export class AppAutoFocusDirective implements AfterViewInit {
    constructor(private el: ElementRef) {}

    ngAfterViewInit() {
        setTimeout(() => {
            console.log("Focusing ", this.el.nativeElement as HTMLElement);
            (this.el.nativeElement as HTMLElement).focus();
        }, 0);
    }
}
