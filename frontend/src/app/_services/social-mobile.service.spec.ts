import { TestBed } from "@angular/core/testing";

import { SocialMobileService } from "./social-mobile.service";

describe("SocialMobileService", () => {
    let service: SocialMobileService;

    beforeEach(() => {
        TestBed.configureTestingModule({});
        service = TestBed.inject(SocialMobileService);
    });

    it("should be created", () => {
        expect(service).toBeTruthy();
    });
});
