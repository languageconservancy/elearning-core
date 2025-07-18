import { YoutubePipe } from "./youtube.pipe";
import { DomSanitizer } from "@angular/platform-browser";

describe("YoutubePipe", () => {
    it("create an instance", () => {
        let dom: DomSanitizer;
        const pipe = new YoutubePipe(dom);
        expect(pipe).toBeTruthy();
    });
});
