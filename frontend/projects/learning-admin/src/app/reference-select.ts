import { ChangeDetectionStrategy, Component, effect, inject, input, output, signal } from "@angular/core";
import { switchMap, timer } from "rxjs";
import { FieldValue } from "./models";
import { errorMessage, WorkspaceApi } from "./workspace-api";

let nextId = 0;
@Component({
    selector: "studio-reference-select",
    changeDetection: ChangeDetectionStrategy.OnPush,
    template: `
        <div class="reference-select">
            <input type="search" role="combobox" [attr.aria-label]="label()" [attr.aria-expanded]="open()"
                [attr.aria-controls]="id" [attr.aria-activedescendant]="open() && options()[active()] ? id + '-' + active() : null"
                aria-autocomplete="list" [value]="query()" (focus)="open.set(true)" (input)="search($event)" (keydown)="key($event)" placeholder="Search to select..." />
            @if (open()) {
                <ul role="listbox" [id]="id" [attr.aria-label]="label()">
                    @for (option of options(); track option.id; let i = $index) {
                        <li role="option" [id]="id + '-' + i" [attr.aria-selected]="active() === i">
                            <button type="button" (click)="select(option)">{{ option.title }}</button>
                        </li>
                    } @empty { <li role="option" aria-selected="false">{{ loading() ? "Searching..." : "No choices match" }}</li> }
                </ul>
                <button type="button" class="quiet" (click)="open.set(false)">Close choices</button>
            }
            @if (error()) { <small role="alert" class="error">{{ error() }}</small> }
        </div>
    `,
})
export class ReferenceSelect {
    readonly reference = input.required<string>();
    readonly label = input.required<string>();
    readonly value = input<FieldValue>(null);
    readonly chosen = output<number>();
    readonly query = signal("");
    readonly open = signal(false);
    readonly active = signal(0);
    readonly loading = signal(false);
    readonly error = signal("");
    readonly options = signal<{ id: number; title: string }[]>([]);
    readonly id = "reference-options-" + nextId++;
    private readonly api = inject(WorkspaceApi);
    constructor() {
        effect((cleanup) => {
            const reference = this.reference(), value = this.value();
            if (!value) return;
            const subscription = this.api.choices(reference, "", Number(value)).subscribe({
                next: (result) => this.query.set(result.items[0]?.title ?? `Missing reference #${value}`),
                error: (error: unknown) => this.error.set(errorMessage(error)),
            });
            cleanup(() => subscription.unsubscribe());
        });
        effect((cleanup) => {
            const q = this.query(), reference = this.reference(), open = this.open();
            if (!open) return;
            this.loading.set(true); this.error.set("");
            const subscription = timer(200).pipe(switchMap(() => this.api.choices(reference, q))).subscribe({
                next: (result) => { this.options.set(result.items); this.active.set(0); this.loading.set(false); },
                error: (error: unknown) => { this.error.set(errorMessage(error)); this.loading.set(false); },
            });
            cleanup(() => subscription.unsubscribe());
        });
    }
    search(event: Event) { this.query.set((event.target as HTMLInputElement).value); this.open.set(true); }
    select(option: { id: number; title: string }) { this.query.set(option.title); this.open.set(false); this.chosen.emit(option.id); }
    key(event: KeyboardEvent) {
        if (event.key === "Escape") { event.stopPropagation(); this.open.set(false); }
        if (event.key === "ArrowDown" || event.key === "ArrowUp") {
            event.preventDefault(); this.open.set(true);
            this.active.update((n) => Math.max(0, Math.min(this.options().length - 1, n + (event.key === "ArrowDown" ? 1 : -1))));
        }
        if (event.key === "Enter" && this.open()) {
            event.preventDefault(); const option = this.options()[this.active()]; if (option) this.select(option);
        }
    }
}
