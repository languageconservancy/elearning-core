import { Pipe, PipeTransform } from "@angular/core";
import { DomSanitizer } from "@angular/platform-browser";

@Pipe({
    name: "youtube",
})
/**
 * Pipe to allow youtube video HTML markup to pass through Angular's
 * sanitizer. Only lets it pass if <script> tag doesn't exist in the string.
 */
export class YoutubePipe implements PipeTransform {
    constructor(private dom: DomSanitizer) {}

    /**
     * Bypasses HTML security if there's no <script> tag and only
     * if there's an <iframe> tag
     */
    transform(value: string): any {
        if (!value || value.search("<script>") >= 0 || value.search("iframe") < 0) {
            return value;
        }
        return this.dom.bypassSecurityTrustHtml(value);
    }
}
