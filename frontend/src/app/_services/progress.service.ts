import { Injectable } from "@angular/core";

import { BaseService } from "./base.service";
import * as API from "app/_constants/api.constants";

@Injectable()
export class ProgressService extends BaseService {
    getLeaderBoardData(params): any {
        return this.callApi(API.User.GET_LEADERBOARD, "POST", params, {}, "site", true);
    }
    getProgress(params): any {
        return this.callApi(API.Points.GET_PROGRESS, "POST", params, {}, "site", true);
    }
}
