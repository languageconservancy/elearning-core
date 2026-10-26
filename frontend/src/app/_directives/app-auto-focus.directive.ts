import { AfterViewInit, Directive, ElementRef, inject } from "@angular/core";

@Directive({
    selector: "[appAutoFocus]",
    standalone: true,
})
export class AppAutoFocusDirective implements AfterViewInit {
    private el = inject(ElementRef);

    ngAfterViewInit() {
        setTimeout(() => {
            console.log("Focusing ", this.el.nativeElement as HTMLElement);
            (this.el.nativeElement as HTMLElement).focus();
        }, 0);
    }
}
