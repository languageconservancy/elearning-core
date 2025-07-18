import { Pipe, PipeTransform } from "@angular/core";

@Pipe({
    name: "capitalizeFirstLetter",
})
export class CapitalizeFirstLetterPipe implements PipeTransform {
    transform(text: string): string {
        if (!text) return text;
        return text[0].toUpperCase() + text.substr(1).toLowerCase();
    }
}
