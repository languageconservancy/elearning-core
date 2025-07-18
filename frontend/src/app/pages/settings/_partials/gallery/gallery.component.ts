import { Component, OnDestroy } from "@angular/core";
import { trigger, transition, query, style, animate, group } from "@angular/animations";
import { Subscription } from "rxjs";
import { CookieService } from "app/_services/cookie.service";

import { Loader } from "app/_services/loader.service";
import { SettingsService } from "app/_services/settings.service";
import { SnackbarService } from "app/_services/snackbar.service";

@Component({
    selector: "app-gallery",
    templateUrl: "./gallery.component.html",
    styleUrls: ["./gallery.component.scss"],
    animations: [
        trigger("slider", [
            transition(
                ":increment",
                group([
                    query(
                        ":enter",
                        [
                            style({
                                transform: "translateX(100%)",
                            }),
                            animate("0.5s ease-out", style("*")),
                        ],
                        { optional: true },
                    ),
                    query(
                        ":leave",
                        [
                            animate(
                                "0.5s ease-out",
                                style({
                                    transform: "translateX(-100%)",
                                }),
                            ),
                        ],
                        { optional: true },
                    ),
                ]),
            ),
            transition(
                ":decrement",
                group([
                    query(
                        ":enter",
                        [
                            style({
                                transform: "translateX(-100%)",
                            }),
                            animate("0.5s ease-out", style("*")),
                        ],
                        { optional: true },
                    ),
                    query(
                        ":leave",
                        [
                            animate(
                                "0.5s ease-out",
                                style({
                                    transform: "translateX(100%)",
                                }),
                            ),
                        ],
                        { optional: true },
                    ),
                ]),
            ),
        ]),
    ],
})
export class GalleryComponent implements OnDestroy {
    private gallerySubscription: Subscription;
    private _images: string[] = [];
    public selectedIndex: number = 0;
    private user: any;
    public imageLength: number = 0;

    constructor(
        private settingsService: SettingsService,
        private loader: Loader,
        private cookieService: CookieService,
        private snackbarService: SnackbarService,
    ) {
        this.cookieService
            .get("AuthUser")
            .then((value) => {
                if (value == "") {
                    throw value;
                }
                this.user = JSON.parse(value);
            })
            .catch((err) => {
                console.warn("No AuthUser cookie", err);
            });

        this.gallerySubscription = this.settingsService.gallery.subscribe((gallery) => {
            if (gallery && gallery.length > 0) {
                this._images = [];
                this.imageLength = gallery.length;
                gallery.forEach((element) => {
                    this._images.push(element.FullImageUrl);
                });
            }
        });
    }

    ngOnDestroy() {
        this.gallerySubscription.unsubscribe();
    }

    private setLoader(val: boolean) {
        this.loader.setLoader(val);
    }

    get images() {
        return [this._images[this.selectedIndex]];
    }

    previous() {
        if (this.selectedIndex == 0) {
            this.selectedIndex = this._images.length;
        }
        this.selectedIndex = Math.max(this.selectedIndex - 1, 0);
    }

    next() {
        if (this.selectedIndex + 1 == this._images.length) {
            this.selectedIndex = -1;
        }
        this.selectedIndex = Math.min(this.selectedIndex + 1, this._images.length - 1);
    }

    uploadImage($event) {
        if ($event.target.files.length > 0) {
            this.setLoader(true);
            const file = $event.target.files[0];

            const formData = new FormData();
            formData.append("image", file, file.name);
            formData.append("id", this.user.id);

            this.settingsService
                .updateGalleryImage(formData)
                .then((res: any) => {
                    this.setLoader(false);
                    this.user = Object.assign({}, res.data?.results[0]);

                    setTimeout(() => {
                        this.settingsService.setGalleryImages(this.user.userimages);
                    }, 200);
                })
                .catch((err) => {
                    console.error("[uploadImage] err: ", err);
                    this.setLoader(false);
                    this.snackbarService.showSnackbar({
                        status: false,
                        msg: "Gallery image upload failed",
                    });
                });
        }
    }
}
