import { Injectable } from "@angular/core";

@Injectable()
export class BadgeService {
    constructor() {}
    badgeImgSet(allbadge: any, type: string): string {
        if (type === "firebadges") {
            const fire_days = allbadge.fire_days;
            let imagsrc = "./assets/images/fire_dead.png";
            if (fire_days > 0 && fire_days < 3) {
                imagsrc = "./assets/images/fire_low.png";
            } else if (fire_days >= 3 && fire_days < 7) {
                imagsrc = "./assets/images/fire_med.png";
            } else if (fire_days >= 7 && fire_days < 14) {
                imagsrc = "./assets/images/fire_high.png";
            } else if (fire_days >= 14) {
                imagsrc = "./assets/images/fire_ultra.png";
            }
            return imagsrc;
        } else if (type === "socialpoint") {
            let imagsrc = "";
            if (allbadge > 4 && allbadge < 100) {
                imagsrc = "./assets/images/teepee_01.png";
            } else if (allbadge >= 100 && allbadge < 250) {
                imagsrc = "./assets/images/teepee_02.png";
            } else if (allbadge >= 250 && allbadge < 1000) {
                imagsrc = "./assets/images/teepee_03.png";
            } else if (allbadge >= 1000 && allbadge < 5000) {
                imagsrc = "./assets/images/teepee_04.png";
            } else if (allbadge >= 5000) {
                imagsrc = "./assets/images/teepee_05.png";
            }
            return imagsrc;
        }
    }
}
