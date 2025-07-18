import { Component, HostListener } from "@angular/core";
import { KeyboardService } from "./keyboard.service";

@Component({
    selector: "app-keyboard",
    template: "",
})
export class KeyboardComponent {
    constructor(private keyboardService: KeyboardService) {}

    @HostListener("document:keydown", ["$event"])
    keyDownEvent(event: KeyboardEvent) {
        switch (event.code) {
            case "Tab":
                this.keyboardService.toggleSelectionPressed(event);
                this.keyboardService.shortcutPressed(event);
                event.preventDefault();
                break;
            case "Enter":
            case "Escape":
                this.keyboardService.submitOrClosePressed(event);
                this.keyboardService.shortcutPressed(event);
                break;
            case "Space":
                this.keyboardService.toggleMediaPressed(event);
                this.keyboardService.shortcutPressed(event);
                event.preventDefault();
                break;
            case "Backspace":
                this.keyboardService.backspacePressed(event);
                this.keyboardService.shortcutPressed(event);
                break;
            default:
                if (event.code == "KeyH") {
                    this.keyboardService.listObservers();
                }
                this.keyboardService.typingPressed(event);
                break;
        }
    }

    @HostListener("document:keyup", ["$event"])
    keyUpEvent(event: KeyboardEvent) {
        switch (event.code) {
            case "Tab":
            case "Enter":
            case "Escape":
            case "Space":
            case "Backspace":
                this.keyboardService.shortcutReleased(event);
                break;
            default:
                break;
        }
    }
}
