import { inject, Injectable } from "@angular/core";
import { HttpClient } from "@angular/common/http";

import { TRANSLATION_FILE_PATH } from "@app/_constants/app.constants";

@Injectable({
    providedIn: "root",
})
export class LocalizeService {
    private http = inject(HttpClient);

    public getTranslations() {
        return this.http.get(TRANSLATION_FILE_PATH);
    }
}
