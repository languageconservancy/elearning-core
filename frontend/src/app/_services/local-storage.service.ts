import { Injectable } from "@angular/core";

declare let localStorage: any;

@Injectable()
export class LocalStorageService {
    constructor() {}

    clear(): void {
        localStorage.clear();
    }

    getItem(index: string): any {
        return localStorage.getItem(index);
    }

    key(key: number): string {
        return localStorage.key(key);
    }

    length(): number {
        return localStorage.length;
    }

    removeItem(index: string): void {
        localStorage.removeItem(index);
    }

    setItem(index: string, data: any): void {
        localStorage.setItem(index, data);
    }
}
