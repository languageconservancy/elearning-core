import { Component, Input } from "@angular/core";

@Component({
    selector: "app-image-zoom",
    templateUrl: "./image-zoom.component.html",
    styleUrls: ["./image-zoom.component.scss"],
})
export class ImageZoomComponent {
    @Input() src: string;
    @Input() clazz: string;
    @Input() error: any;
    @Input() alt: string = "";
    @Input() id: string = "";
    public popup: boolean = false;

    constructor() {}

    /**
     * This toggles a class named "show-overlay", which shows the popup if assigned to an element.
     */
    togglePopup(): void {
        this.popup = !this.popup;
    }
}
