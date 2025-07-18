import { Injectable } from "@angular/core";

import { BaseService } from "./base.service";
import * as API from "app/_constants/api.constants";

@Injectable()
export class LearningSpeedService extends BaseService {
    getLearningSpeed() {
        return this.callApi(API.Speed.FETCH, "GET", {}, {}, "site", true);
    }

    setLearningSpeed(data: any) {
        return this.callApi(API.User.UPDATE, "POST", data, {}, "site", true);
    }
}
