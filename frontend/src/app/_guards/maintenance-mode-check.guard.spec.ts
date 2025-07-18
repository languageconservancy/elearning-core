import { TestBed, inject } from "@angular/core/testing";
import { RouterTestingModule } from "@angular/router/testing";
import { HttpClient, HttpHandler } from "@angular/common/http";
import { CookieService } from "app/_services/cookie.service";

import { SettingsService } from "app/_services/settings.service";
import { LocalStorageService } from "app/_services/local-storage.service";
import { MaintenanceModeCheckGuard } from "./maintenance-mode-check.guard";

describe("MaintenanceModeCheckGuard", () => {
    beforeEach(() => {
        TestBed.configureTestingModule({
            providers: [
                MaintenanceModeCheckGuard,
                SettingsService,
                HttpClient,
                HttpHandler,
                CookieService,
                LocalStorageService,
            ],
            imports: [RouterTestingModule],
        });
    });

    it("should ...", inject([MaintenanceModeCheckGuard], (guard: MaintenanceModeCheckGuard) => {
        expect(guard).toBeTruthy();
    }));
});
