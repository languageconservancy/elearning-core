import { Component, OnDestroy } from "@angular/core";
import { Subscription } from "rxjs";

import { LessonsService } from "app/_services/lessons.service";
import { ReviewService } from "app/_services/review.service";
import { KeyboardService } from "../keyboard.service";

@Component({
    selector: "app-keyboard-shortcuts",
    templateUrl: "./keyboard-shortcuts.component.html",
    styleUrls: ["./keyboard-shortcuts.component.scss"],
})
/**
 * @class KeyboardShortuctsComponent
 * @brief Component for displaying a keyboard icon that when
 * clicked pops out a horizontal list of keyboard shortcuts
 * in the format of keyboard key icons followed by their function.
 */
export class KeyboardShortcutsComponent implements OnDestroy {
    // Template variables
    public showKeyboardShortcuts: boolean = false;
    public showSelectNext: boolean = false;
    public showSelectPrevious: boolean = false;
    public showGotoNext: boolean = false;
    public showGotoPrevious: boolean = false;
    public showSubmit: boolean = false;
    public showPlayAudio: boolean = false;
    public showTyping: boolean = false;

    // Key HTML elements for toggling keypress state css style
    public keysPressed: Array<string> = [];

    // Subscriptions
    private lessonFrameSubscription: Subscription;
    private exerciseActivitySubscription: Subscription;
    private reviewActivitySubscription: Subscription;

    constructor(
        private lessonsService: LessonsService,
        private reviewService: ReviewService,
        private keyboardService: KeyboardService,
    ) {
        // Lesson frame was just loaded
        this.lessonFrameSubscription = this.lessonsService.currentFrame.subscribe((data) => {
            if (!!data && Object.keys(data).length) {
                // Clear shortcuts and set to true only those that apply to the current frame
                this.clearShortcuts();
                this.showGotoPrevious = data.frameorder > 1;
                this.showGotoNext = true;
                for (const block of data.lesson_frame_blocks) {
                    if (block.is_card_audio == "Y") {
                        this.showPlayAudio = true;
                        break;
                    }
                }
            }
        });

        // Exercise activity was just loaded
        this.exerciseActivitySubscription = this.lessonsService.currentExercise.subscribe((data) => {
            if (!!data && Object.keys(data).length) {
                // Clear shortcuts and set to true only those that apply to the current exercise type
                this.clearShortcuts();
                switch (data.exercise_type) {
                    case "multiple-choice":
                    case "match-the-pair":
                    case "truefalse":
                    case "fill_in_the_blanks":
                    case "anagram":
                        this.showSelectNext = true;
                        this.showSelectPrevious = true;
                        this.showSubmit = true;
                        this.showPlayAudio = this.activityHasAudio(data);
                        this.showTyping = this.activityHasTyping(data);
                        break;
                    case "recording":
                        break;
                    default:
                        console.warn("Oops! Got unhandled exercise type");
                        break;
                }
            }
        });

        // Review activity was just loaded
        this.reviewActivitySubscription = this.reviewService.currentExercise.subscribe((data) => {
            if (!!data && Object.keys(data).length) {
                // Clear shortcuts and set to true only those that apply to the current exercise type
                this.clearShortcuts();
                switch (data.exercise_type) {
                    case "multiple-choice":
                    case "match-the-pair":
                    case "truefalse":
                    case "fill_in_the_blanks":
                    case "anagram":
                        this.showSelectNext = true;
                        this.showSelectPrevious = true;
                        this.showSubmit = true;
                        this.showPlayAudio = this.activityHasAudio(data);
                        this.showTyping = this.activityHasTyping(data);
                        break;
                    case "recording":
                        break;
                    default:
                        console.warn("Oops! Got unhandled exercise type");
                        break;
                }
            } else {
            }
        });

        this.keyboardService.shortcutPressedEvent.subscribe((event) => {
            this.keysPressed.push(event.code);
            if (event.shiftKey) {
                this.keysPressed.push("Shift");
            }
        });

        this.keyboardService.shortcutReleasedEvent.subscribe((event) => {
            this.keysPressed = this.keysPressed.filter((key) => key != event.code);
            if (event.shiftKey) {
                this.keysPressed = this.keysPressed.filter((key) => key != "Shift");
            }
        });
    }

    /**
     * @brief Called after this component is removed from the Document
     * Object Model (DOM). Must clean up dynamic memory to avoid leaks.
     */
    ngOnDestroy() {
        // Unsubscribe from rxjs Subject subscriptions
        this.lessonFrameSubscription.unsubscribe();
        this.exerciseActivitySubscription.unsubscribe();
        this.reviewActivitySubscription.unsubscribe();
    }

    /**
     * @brief Sets all template booleans to false.
     * Called in order to clear everything before re-determining
     * the keyboard shortcuts for the current activity.
     */
    private clearShortcuts() {
        this.showSelectNext = false;
        this.showSelectPrevious = false;
        this.showGotoNext = false;
        this.showGotoPrevious = false;
        this.showSubmit = false;
        this.showPlayAudio = false;
    }

    /**
     * @brief Determines if the current activity has playable audio
     * and therefore the keyboard shortcut for playing audio should be shown.
     * @return true if activity contains playable audio, otherwise false
     */
    private activityHasAudio(data) {
        if (
            (!!data.promotetype && data.promotetype.indexOf("a") > -1) ||
            (!!data.promteresponsetype && data.promteresponsetype.indexOf("a") > -1)
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @brief Determines if the current activity has typing
     * and therefore the keyboard shortcut for typing should be shown.
     * @return true if activity contains typing, otherwise false
     */
    private activityHasTyping(data) {
        let hasTyping = false;

        if (data.exercise_type == "anagram") {
            hasTyping = true;
        } else if (data.exercise_type == "fill_in_the_blanks") {
            if (!!data.questions && data.questions.length > 0) {
                if (
                    data.questions[0]?.question.exerciseOptions.fill_in_the_blank_type == "typing"
                ) {
                    hasTyping = true;
                }
            } else if (!!data.question) {
                hasTyping = true;
            }
        }

        return hasTyping;
    }

    /**
     * @brief Toggles whether or not the keyboard shortcuts are part of the
     * Document Object Model (DOM) and therefore if they are visible or not.
     */
    public toggleKeyboardShortcutsPopoutVisibility() {
        this.showKeyboardShortcuts = !this.showKeyboardShortcuts;
    }
}
