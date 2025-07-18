import { Pipe, PipeTransform } from "@angular/core";
import { DomSanitizer } from "@angular/platform-browser";

@Pipe({
    name: "safeHtml",
})
export class SafeHtmlPipe implements PipeTransform {
    constructor(private sanitized: DomSanitizer) {}

    transform(value) {
        if (!value) {
            return value;
        }
        let replaceTags = "";
        replaceTags = value.replace(/<size=(\d*)/g, (resp) => {
            const sizeVal = resp.match(/=(\d*)/);
            return '<span style="font-size:' + sizeVal[1] + 'px"';
        });
        replaceTags = replaceTags.replace(/<\/size>/g, "</span>");
        replaceTags = replaceTags.replace(/<color=(\#*\w*)/g, (resp) => {
            const colorVal = resp.match(/=(\#*\w*)/);
            return '<span style="color: ' + colorVal[1] + '"';
        });
        replaceTags = replaceTags.replace(/<\/color>/g, "</span>");
        replaceTags = replaceTags.replace(/[\n\r]+/g, "<br>");
        replaceTags = `<div style="margin: 0; padding: 0;">` + replaceTags + "</div>";

        return this.sanitized.bypassSecurityTrustHtml(replaceTags);
    }
}
