import { TestBed } from "@angular/core/testing";

import { AppInitService } from "./app-init.service";

fdescribe("AppInitService", () => {
    let service: AppInitService;

    beforeEach(() => {
        TestBed.configureTestingModule({});
        service = TestBed.inject(AppInitService);
    });

    it("should be created", () => {
        expect(service).toBeTruthy();
    });

    // it("should resolve", async () => {
    //     try {
    //         const response = await service.init();
    //         expect(response).toEqual(true);
    //         return;
    //     } catch (err) {
    //         console.error("app-init catch error: ", err);
    //         expect(err).toBeTruthy();
    //     }
    //     expect(false).toBeTruthy();
    // });
});
