import { NgModule, ModuleWithProviders } from "@angular/core";
import { CommonModule } from "@angular/common";

import { KeyboardService } from "./keyboard.service";
import { KeyboardComponent } from "./keyboard.component";
import { KeyboardShortcutsComponent } from "./keyboard-shortcuts/keyboard-shortcuts.component";

@NgModule({
    imports: [CommonModule],
    declarations: [KeyboardComponent, KeyboardShortcutsComponent],
    exports: [KeyboardComponent, KeyboardShortcutsComponent],
})
export class KeyboardModule {
    static forRoot(): ModuleWithProviders<KeyboardModule> {
        return {
            ngModule: KeyboardModule,
            providers: [KeyboardService],
        };
    }
}
