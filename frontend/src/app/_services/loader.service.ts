import { Injectable } from "@angular/core";
import { BehaviorSubject } from "rxjs";

@Injectable()
export class Loader {
    private loaderSubject = new BehaviorSubject<any>({});
    public loader = this.loaderSubject.asObservable();

    constructor() {
        this.loaderSubject.next(false);
    }

    setLoader(value: boolean) {
        this.loaderSubject.next(value);
    }
}
