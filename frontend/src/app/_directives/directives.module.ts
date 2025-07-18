import { NgModule } from "@angular/core";
import { CommonModule } from "@angular/common";
import { ScrollIntoViewDirective } from "./scroll-into-view.directive";
import { AdjustModalForKeyboardDirective } from "./adjust-modal-for-keyboard.directive";
import { AutoScrollModalInputDirective } from "./auto-scroll-modal-input.directive";

@NgModule({
    declarations: [ScrollIntoViewDirective, AdjustModalForKeyboardDirective, AutoScrollModalInputDirective],
    exports: [ScrollIntoViewDirective, AdjustModalForKeyboardDirective, AutoScrollModalInputDirective],
    imports: [CommonModule], // Import CommonModule to use common Angular directives
})
export class DirectivesModule {}
