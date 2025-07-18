import { Injectable } from "@angular/core";
import { Subject } from "rxjs";

@Injectable({
    providedIn: "root",
})
export class SnackbarService {
    private snackbarSubject = new Subject<any>();
    public snackbar = this.snackbarSubject.asObservable();

    constructor() {}

    showSnackbar({ msg, status = true }: { msg: string; status?: boolean }) {
        if (!msg) {
            console.warn("Snackbar service got falsy data.msg");
            return;
        }
        this.snackbarSubject.next({ msg, status });
    }

    handleError(err: any, fallbackMessage: string = ""): void {
        const message = err?.message || fallbackMessage;
        console.error("Error: ", err || message);
        this.showSnackbar({ status: false, msg: message });
    }
}
