import {
    AfterViewInit,
    ChangeDetectionStrategy,
    Component,
    ElementRef,
    inject,
    input,
    OnDestroy,
    output,
    viewChild,
} from "@angular/core";

let nextDialogId = 0;

@Component({
    selector: "studio-context-panel",
    changeDetection: ChangeDetectionStrategy.OnPush,
    template: `
        <dialog
            #dialog
            [class.drawer]="drawer()"
            [attr.aria-labelledby]="headingId"
            (cancel)="cancel($event)"
        >
            <header class="panel-heading">
                <h2 [id]="headingId">{{ heading() }}</h2>
                <button type="button" (click)="closed.emit()" aria-label="Close dialog">
                    Close
                </button>
            </header>
            <div class="panel-body"><ng-content /></div>
        </dialog>
    `,
})
export class ContextPanel implements AfterViewInit, OnDestroy {
    readonly headingId = "context-heading-" + nextDialogId++;
    readonly heading = input.required<string>();
    readonly drawer = input(false);
    readonly closed = output<void>();
    private readonly dialog = viewChild.required<ElementRef<HTMLDialogElement>>("dialog");
    private readonly opener = inject(ElementRef).nativeElement.ownerDocument
        .activeElement as HTMLElement | null;
    ngAfterViewInit() {
        this.dialog().nativeElement.showModal();
    }
    ngOnDestroy() {
        this.dialog().nativeElement.close();
        this.opener?.focus();
    }
    cancel(event: Event) {
        event.preventDefault();
        this.closed.emit();
    }
}
