import { TestBed } from "@angular/core/testing";

import { SocialWebService } from "./social-web.service";

describe("SocialService", () => {
    let service: SocialWebService;

    beforeEach(() => {
        TestBed.configureTestingModule({});
        service = TestBed.inject(SocialWebService);
    });

    it("should be created", () => {
        expect(service).toBeTruthy();
    });
});
