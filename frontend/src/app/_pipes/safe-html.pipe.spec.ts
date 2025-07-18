import { SafeHtmlPipe } from "./safe-html.pipe";
import { DomSanitizer } from "@angular/platform-browser";

describe("SafeHtmlPipe", () => {
    it("create an instance", () => {
        let sanitizer: DomSanitizer;
        const pipe = new SafeHtmlPipe(sanitizer);
        expect(pipe).toBeTruthy();
    });
});
