import { Injectable } from "@angular/core";
import { Subject } from "rxjs";

@Injectable({
    providedIn: "root",
})
export class KeyboardService {
    // Move to next or previous item
    private toggleSelectionEventSubject = new Subject<KeyboardEvent>();
    public toggleSelectionEvent = this.toggleSelectionEventSubject.asObservable();
    // Click on sole button or selected button
    private submitOrCloseEventSubject = new Subject<KeyboardEvent>();
    public submitOrCloseEvent = this.submitOrCloseEventSubject.asObservable();
    // Toggle play/pause media (video, audio)
    private toggleMediaEventSubject = new Subject<KeyboardEvent>();
    public toggleMediaEvent = this.toggleMediaEventSubject.asObservable();
    // Backspace key
    private backspaceEventSubject = new Subject<KeyboardEvent>();
    public backspaceEvent = this.backspaceEventSubject.asObservable();
    // Typing keys
    private typingEventSubject = new Subject<KeyboardEvent>();
    public typingEvent = this.typingEventSubject.asObservable();
    // Shortcut pressed
    private shortcutPressedEventSubject = new Subject<KeyboardEvent>();
    public shortcutPressedEvent = this.shortcutPressedEventSubject.asObservable();
    // Shortcut released
    private shortcutReleasedEventSubject = new Subject<KeyboardEvent>();
    public shortcutReleasedEvent = this.shortcutReleasedEventSubject.asObservable();
    // List of subjects in order to print them out in the console
    private subjects: any = [
        { name: "ToggleSelection", subject: this.toggleSelectionEventSubject },
        { name: "SubmitOrClose", subject: this.submitOrCloseEventSubject },
        { name: "ToggleMedia", subject: this.toggleMediaEventSubject },
        { name: "Backspace", subject: this.backspaceEventSubject },
        { name: "Typing", subject: this.typingEventSubject },
        { name: "Shortcut Pressed", subject: this.shortcutPressedEventSubject },
        { name: "Shortcut Released", subject: this.shortcutReleasedEventSubject },
    ];

    toggleSelectionPressed(event: KeyboardEvent) {
        this.toggleSelectionEventSubject.next(event);
    }
    submitOrClosePressed(event: KeyboardEvent) {
        this.submitOrCloseEventSubject.next(event);
    }
    toggleMediaPressed(event: KeyboardEvent) {
        this.toggleMediaEventSubject.next(event);
    }
    backspacePressed(event: KeyboardEvent) {
        this.backspaceEventSubject.next(event);
    }
    typingPressed(event: KeyboardEvent) {
        this.typingEventSubject.next(event);
    }
    shortcutPressed(event: KeyboardEvent) {
        this.shortcutPressedEventSubject.next(event);
    }
    shortcutReleased(event: KeyboardEvent) {
        this.shortcutReleasedEventSubject.next(event);
    }
    listObservers() {
        for (const s of this.subjects) {
            if (s.subject.observers.length > 0) {
                console.log(s.name + " observers (" + s.subject.observers.length + "):");
                for (const o of s.subject.observers) {
                    console.log(o.destination);
                }
            }
        }
    }
}
