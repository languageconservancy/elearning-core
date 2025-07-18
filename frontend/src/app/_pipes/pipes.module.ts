import { NgModule } from "@angular/core";
import { CommonModule } from "@angular/common";
import { BadgeImagePipe } from "./badge-image.pipe";
import { SafeHtmlPipe } from "./safe-html.pipe";
import { YoutubePipe } from "./youtube.pipe";
import { CapitalizeFirstLetterPipe } from "./capitalize-first-letter.pipe";

@NgModule({
    imports: [CommonModule],
    declarations: [BadgeImagePipe, SafeHtmlPipe, YoutubePipe, CapitalizeFirstLetterPipe],

    exports: [BadgeImagePipe, SafeHtmlPipe, YoutubePipe, CapitalizeFirstLetterPipe],
})
export class PipesModule {}
