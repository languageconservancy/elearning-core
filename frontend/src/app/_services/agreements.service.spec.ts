import { TestBed } from "@angular/core/testing";

import { AgreementsService } from "./agreements.service";

describe("AgreementsService", () => {
    let service: AgreementsService;

    beforeEach(() => {
        TestBed.configureTestingModule({});
        service = TestBed.inject(AgreementsService);
    });

    it("should be created", () => {
        expect(service).toBeTruthy();
    });
});
