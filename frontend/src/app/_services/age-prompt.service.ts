import { Injectable } from "@angular/core";
import { BehaviorSubject } from "rxjs";

@Injectable({
    providedIn: "root",
})
export class AgePromptService {
    private responseSubject = new BehaviorSubject<{ ok: boolean | null }>({
        ok: null,
    });
    public response$ = this.responseSubject.asObservable();
    private _user: any = null;

    ageUpdated(ok: boolean): void {
        this.responseSubject.next({ ok });
    }

    public get user(): any {
        return this._user;
    }

    public setUser(value: any) {
        if (!value) {
            throw new Error("[AgePromptService] User cannot be null");
        }
        this._user = value;
    }
}
