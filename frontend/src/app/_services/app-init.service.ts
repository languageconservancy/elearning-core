import { Injectable } from "@angular/core";

@Injectable({
    providedIn: "root",
})
export class AppInitService {
    constructor() {}

    /**
     * Function that when used in conjunction with APP_INITIALIZER
     * allows stuff to be loaded/initialized before angular loads.
     */
    init() {
        return new Promise<boolean>((resolve) => {
            // Load Facebook SDK by adding new script element if it
            // doesn't already exist
            (function (doc, elementType, elementId) {
                // Add
                const existingNode = doc.getElementsByTagName(elementType)[0];
                if (doc.getElementById(elementId)) {
                    return;
                }
                const js = <HTMLScriptElement>doc.createElement(elementType);
                js.id = elementId;
                js.src = "//connect.facebook.net/en_US/sdk.js";
                existingNode.parentNode.insertBefore(js, existingNode);

                // Wait for Facebook SDK script to load before resolving promise
                js.addEventListener("load", scriptLoaded, false);
                function scriptLoaded() {
                    resolve(true);
                }
            })(document, "script", "facebook-jssdk");
        });
    }
}
