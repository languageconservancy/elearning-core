import { Component, Output, EventEmitter } from "@angular/core";

@Component({
    selector: "app-signup-suggestion",
    templateUrl: "./signup-suggestion.component.html",
    styleUrls: ["./signup-suggestion.component.css"]
})
export class SignupSuggestionComponent {
    // Event emitter for continuing with trial
    @Output() public continueWithTrial: EventEmitter<boolean> = new EventEmitter<boolean>();

    /**
     * Handle press of 'Next' button.
     */
    public signUpBtnPressed() {
        this.continueWithTrial.emit(false);
    }

    /**
     * Handle press of 'Reject' button.
     */
    public notNowBtnPressed() {
        this.continueWithTrial.emit(true);
    }
}
