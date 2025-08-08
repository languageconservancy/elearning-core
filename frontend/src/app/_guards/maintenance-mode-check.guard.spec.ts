/// <reference types="jasmine" />
import { TestBed, inject } from "@angular/core/testing";
import { Router } from "@angular/router";
import { CookieService } from "app/_services/cookie.service";
import { SettingsService } from "app/_services/settings.service";
import { MaintenanceModeCheckGuard, MaintenanceModeGuard } from "./maintenance-mode-check.guard";

describe("MaintenanceModeCheckGuard", () => {
    let settingsService: any;
    let router: any;

    beforeEach(() => {
        settingsService = {
            getMaintenanceMode: jasmine.createSpy().and.returnValue(Promise.resolve({})),
            setMaintenanceMode: jasmine.createSpy(),
        };
        router = {
            navigate: jasmine.createSpy(),
        };

        TestBed.configureTestingModule({
            providers: [
                MaintenanceModeCheckGuard,
                { provide: SettingsService, useValue: settingsService },
                { provide: Router, useValue: router },
            ],
        });
    });

    it("should be created", inject(
        [MaintenanceModeCheckGuard],
        (guard: MaintenanceModeCheckGuard) => {
            expect(guard).toBeTruthy();
        },
    ));

    it("should allow route when site is not under construction", inject(
        [MaintenanceModeCheckGuard],
        async (guard: MaintenanceModeCheckGuard) => {
            const mockResponse = {
                data: {
                    status: true,
                    results: {
                        is_under_construction: "N",
                    },
                },
            };
            settingsService.getMaintenanceMode.and.returnValue(Promise.resolve(mockResponse));

            const result = await guard.canActivate();

            expect(result).toBe(true);
            expect(settingsService.setMaintenanceMode).toHaveBeenCalledWith(false);
            expect(router.navigate).not.toHaveBeenCalled();
        },
    ));

    it("should block route and redirect when site is under construction", inject(
        [MaintenanceModeCheckGuard],
        async (guard: MaintenanceModeCheckGuard) => {
            const mockResponse = {
                data: {
                    status: true,
                    results: {
                        is_under_construction: "Y",
                    },
                },
            };
            settingsService.getMaintenanceMode.and.returnValue(Promise.resolve(mockResponse));

            const result = await guard.canActivate();

            expect(result).toBe(false);
            expect(settingsService.setMaintenanceMode).toHaveBeenCalledWith(true);
            expect(router.navigate).toHaveBeenCalledWith(["under-construction"]);
        },
    ));

    it("should block route when response is invalid", inject(
        [MaintenanceModeCheckGuard],
        async (guard: MaintenanceModeCheckGuard) => {
            settingsService.getMaintenanceMode.and.returnValue(Promise.resolve(null));

            const result = await guard.canActivate();

            expect(result).toBe(false);
        },
    ));

    it("should allow route when API call fails", inject(
        [MaintenanceModeCheckGuard],
        async (guard: MaintenanceModeCheckGuard) => {
            settingsService.getMaintenanceMode.and.returnValue(Promise.reject("API Error"));

            const result = await guard.canActivate();

            expect(result).toBe(true);
            expect(settingsService.setMaintenanceMode).toHaveBeenCalledWith(false);
        },
    ));
});

describe("MaintenanceModeGuard", () => {
    let settingsService: any;
    let router: any;
    let cookieService: any;

    beforeEach(() => {
        settingsService = {
            getMaintenanceMode: jasmine.createSpy().and.returnValue(Promise.resolve({})),
            setMaintenanceMode: jasmine.createSpy(),
        };
        router = {
            navigate: jasmine.createSpy(),
        };
        cookieService = {
            get: jasmine.createSpy().and.returnValue(Promise.resolve("")),
        };

        TestBed.configureTestingModule({
            providers: [
                MaintenanceModeGuard,
                { provide: SettingsService, useValue: settingsService },
                { provide: Router, useValue: router },
                { provide: CookieService, useValue: cookieService },
            ],
        });
    });

    it("should be created", inject([MaintenanceModeGuard], (guard: MaintenanceModeGuard) => {
        expect(guard).toBeTruthy();
    }));

    it("should allow access to maintenance page when site is under construction", inject(
        [MaintenanceModeGuard],
        async (guard: MaintenanceModeGuard) => {
            const mockResponse = {
                data: {
                    status: true,
                    results: {
                        is_under_construction: "Y",
                    },
                },
            };
            settingsService.getMaintenanceMode.and.returnValue(Promise.resolve(mockResponse));

            const result = await guard.canActivate();

            expect(result).toBe(true);
        },
    ));

    it("should redirect authenticated user to dashboard when site is not under construction", inject(
        [MaintenanceModeGuard],
        async (guard: MaintenanceModeGuard) => {
            const mockResponse = {
                data: {
                    status: true,
                    results: {
                        is_under_construction: "N",
                    },
                },
            };
            settingsService.getMaintenanceMode.and.returnValue(Promise.resolve(mockResponse));
            cookieService.get.and.returnValue(Promise.resolve("auth-token"));

            await guard.canActivate();

            expect(settingsService.setMaintenanceMode).toHaveBeenCalledWith(false);
            expect(router.navigate).toHaveBeenCalledWith(["dashboard"]);
        },
    ));

    it("should redirect unauthenticated user to homepage when site is not under construction", inject(
        [MaintenanceModeGuard],
        async (guard: MaintenanceModeGuard) => {
            const mockResponse = {
                data: {
                    status: true,
                    results: {
                        is_under_construction: "N",
                    },
                },
            };
            settingsService.getMaintenanceMode.and.returnValue(Promise.resolve(mockResponse));
            cookieService.get.and.returnValue(Promise.resolve(""));

            await guard.canActivate();

            expect(settingsService.setMaintenanceMode).toHaveBeenCalledWith(false);
            expect(router.navigate).toHaveBeenCalledWith([""]);
        },
    ));

    it("should redirect to homepage when cookie check fails", inject(
        [MaintenanceModeGuard],
        async (guard: MaintenanceModeGuard) => {
            const mockResponse = {
                data: {
                    status: true,
                    results: {
                        is_under_construction: "N",
                    },
                },
            };
            settingsService.getMaintenanceMode.and.returnValue(Promise.resolve(mockResponse));
            cookieService.get.and.returnValue(Promise.reject("Cookie error"));

            await guard.canActivate();

            expect(settingsService.setMaintenanceMode).toHaveBeenCalledWith(false);
            expect(router.navigate).toHaveBeenCalledWith([""]);
        },
    ));

    it("should block access when API call fails", inject(
        [MaintenanceModeGuard],
        async (guard: MaintenanceModeGuard) => {
            settingsService.getMaintenanceMode.and.returnValue(Promise.reject("API Error"));

            const result = await guard.canActivate();

            expect(result).toBe(false);
            expect(settingsService.setMaintenanceMode).toHaveBeenCalledWith(false);
        },
    ));
});
