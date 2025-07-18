import { Pipe, PipeTransform } from "@angular/core";

@Pipe({
    name: "badgeImage",
})
export class BadgeImagePipe implements PipeTransform {
    transform(value: any): any {
        const array = [];
        value.reverse();
        for (let index = 0; index < value.length; index++) {
            if (value[index].status) {
                array.push(value[index]);
                break;
            }
        }
        return array;
    }
}
