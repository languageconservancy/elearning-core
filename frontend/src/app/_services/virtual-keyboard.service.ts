import { Injectable } from "@angular/core";
import { BehaviorSubject } from "rxjs";

@Injectable({
    providedIn: "root",
})
export class VirtualKeyboardService {
    private keyboardVisibilitySubject = new BehaviorSubject<boolean>(false);
    public keyboardVisibility = this.keyboardVisibilitySubject.asObservable();

    private marginAddedSubject = new BehaviorSubject<boolean>(false);
    public marginAdded = this.marginAddedSubject.asObservable();

    private keyboardHeightChangesSubject = new BehaviorSubject<number>(0);
    public keyboardHeightChanges = this.keyboardHeightChangesSubject.asObservable();

    private navbarHeightChangesSubject = new BehaviorSubject<number>(0);
    public navbarHeightChanges = this.navbarHeightChangesSubject.asObservable();

    private keyboardInputSubject = new BehaviorSubject<string>("");
    public keyboardInput = this.keyboardInputSubject.asObservable();

    private activeInputId: string = "";

    constructor() {}

    keyboardHidden() {
        this.keyboardVisibilitySubject.next(false);
    }

    keyboardVisible() {
        this.keyboardVisibilitySubject.next(true);
    }

    marginWasAdded() {
        this.marginAddedSubject.next(true);
    }

    keyboardHeightChanged(height: number) {
        this.keyboardHeightChangesSubject.next(height);
    }

    navbarHeightChanged(height: number) {
        this.navbarHeightChangesSubject.next(height);
    }
}
