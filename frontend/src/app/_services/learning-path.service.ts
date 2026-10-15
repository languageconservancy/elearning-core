import { inject, Injectable } from "@angular/core";

import { BaseService } from "./base.service";
import { ForumService } from "./forum.service";
import * as API from "@app/_constants/api.constants";
import { ApiResponse } from "@app/shared/utils/elearning-types";

@Injectable()
export class LearningPathService extends BaseService {
    public forumService = inject(ForumService);

    async getLearningPaths(params: any): Promise<any> {
        const res: ApiResponse = await this.callApi(
            API.Path.FETCH,
            "POST",
            params,
            {},
            "site",
            true,
        );
        if (!res.data.status || !res.data.results || res.data.results.length === 0) {
            throw Error(res.data.message);
        }
        const userLearningPaths = res.data.results;
        return userLearningPaths;
    }

    setLearningPath(data: any) {
        return this.callApi(API.User.UPDATE, "POST", data, {}, "site", true);
    }

    goToVillage(pathId, userId, levelId) {
        this.localStorage.removeItem("forumId");
        const params: any = {
            path_id: pathId,
            user_id: userId,
            level_id: levelId,
            labelType: "levelfetch",
        };

        this.forumService.setForumParams(params);
        void this.router.navigate(["village"]);
    }

    getReviewImageUrl(fireImage: string, noReviews: boolean) {
        const UrlPrefix = "./assets/images/";
        let image = "fire_low.png";
        if (fireImage == "dead" || noReviews) {
            image = "fire_dead.png";
        } else if (fireImage == "low") {
            image = "fire_low.png";
        } else if (fireImage == "medium") {
            image = "fire_med.png";
        } else if (fireImage == "high") {
            image = "fire_high.png";
        } else if (fireImage == "ultra") {
            image = "fire_ultra.png";
        }
        return UrlPrefix + image;
    }

    getFireTypeFromStreak(fireDays) {
        if (fireDays < 3) {
            return "low";
        } else if (fireDays >= 3 && fireDays < 7) {
            return "medium";
        } else if (fireDays >= 7 && fireDays < 14) {
            return "high";
        } else if (fireDays >= 14) {
            return "ultra";
        }
    }
}
