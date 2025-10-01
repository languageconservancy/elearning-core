import { NgModule } from "@angular/core";
import { CommonModule } from "@angular/common";
import { BadgeImagePipe } from "./badge-image.pipe";
import { SafeHtmlPipe } from "./safe-html.pipe";
import { YoutubePipe } from "./youtube.pipe";
import { CapitalizeFirstLetterPipe } from "./capitalize-first-letter.pipe";
import { SafeUrlPipe } from "./safe-url.pipe";

@NgModule({
    imports: [CommonModule],
    declarations: [
        BadgeImagePipe,
        SafeHtmlPipe,
        YoutubePipe,
        CapitalizeFirstLetterPipe,
        SafeUrlPipe,
    ],

    exports: [BadgeImagePipe, SafeHtmlPipe, YoutubePipe, CapitalizeFirstLetterPipe, SafeUrlPipe],
})
export class PipesModule {}
