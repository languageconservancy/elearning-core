import { NgModule } from "@angular/core";
import { CommonModule } from "@angular/common";
import { ScrollIntoViewDirective } from "./scroll-into-view.directive";
import { AdjustModalForKeyboardDirective } from "./adjust-modal-for-keyboard.directive";
import { AutoScrollModalInputDirective } from "./auto-scroll-modal-input.directive";

@NgModule({
    exports: [
        ScrollIntoViewDirective,
        AdjustModalForKeyboardDirective,
        AutoScrollModalInputDirective,
    ],
    imports: [
        AdjustModalForKeyboardDirective,
        AutoScrollModalInputDirective,
        ScrollIntoViewDirective,
        CommonModule,
    ], // Import CommonModule to use common Angular directives
})
export class DirectivesModule {}
