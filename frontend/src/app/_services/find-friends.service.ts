import { Injectable } from "@angular/core";
import { CapacitorHttp } from "@capacitor/core";

import { BaseService } from "./base.service";
import { environment } from "environments/environment";
import * as API from "app/_constants/api.constants";

@Injectable()
export class FindFriendsService extends BaseService {
    checkIfFriends(data: any) {
        return this.callApi(API.User.CHECK_FRIENDS, "POST", data, {}, "site", true);
    }

    addRemoveFriend(data: any) {
        return this.callApi(API.User.SET_FRIENDS, "POST", data, {}, "site", true);
    }

    getGoogleInvitees(data: any) {
        return this.callApi(API.User.INVITE, "POST", data, {}, "site", true);
    }

    sendInvites(data: any) {
        return this.callApi(API.User.EMAIL_INVITE, "POST", data, {}, "site", true);
    }

    getAllUsers(data: any) {
        return this.callApi(API.User.GET_FRIENDS, "POST", data, {}, "site", true);
    }

    getContacts(data: any) {
        return CapacitorHttp.get({
            url:
                environment.GOOGLE_CONTACT_SCOPE +
                "contacts/default/thin?alt=json&access_token=" +
                data.access_token +
                "&max-results=500&v=3.0",
        });
    }
}
