import { ComponentFixture, TestBed, waitForAsync } from "@angular/core/testing";

import { RequestQuoteComponent } from "./request-quote.component";

describe("RequestQuoteComponent", () => {
    let component: RequestQuoteComponent;
    let fixture: ComponentFixture<RequestQuoteComponent>;

    beforeEach(waitForAsync(() => {
        void TestBed.configureTestingModule({
            declarations: [RequestQuoteComponent],
        }).compileComponents();
    }));

    beforeEach(() => {
        fixture = TestBed.createComponent(RequestQuoteComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it("should create", () => {
        expect(component).toBeTruthy();
    });
});
