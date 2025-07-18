import { TestBed, inject } from "@angular/core/testing";
import { HttpClient, HttpHandler } from "@angular/common/http";
import { RouterTestingModule } from "@angular/router/testing";
import { CookieService } from "app/_services/cookie.service";

import { AuthGuard } from "./auth.guard";
import { LocalStorageService } from "app/_services/local-storage.service";
import { SettingsService } from "app/_services/settings.service";

describe("AuthGuard", () => {
    beforeEach(() => {
        TestBed.configureTestingModule({
            providers: [AuthGuard, SettingsService, HttpClient, HttpHandler, CookieService, LocalStorageService],
            imports: [RouterTestingModule],
        });
    });

    it("should ...", inject([AuthGuard], (guard: AuthGuard) => {
        expect(guard).toBeTruthy();
    }));
});
