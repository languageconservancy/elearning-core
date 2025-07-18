import { Injectable } from "@angular/core";
import { BaseService } from "./base.service";
import * as API from "app/_constants/api.constants";

@Injectable()
export class ResetPasswordService extends BaseService {
    submitForgotPassword(data: any) {
        return this.callApi(API.User.FORGOT_PASSWORD, "POST", data, {}, "site", false);
    }

    changePassword(data: any) {
        return this.callApi(API.User.PASSWORD_TOKEN, "POST", data, {}, "site", false);
    }

    teacherChangePassword(data: any) {
        return this.callApi(API.User.TEACHER_PASSWORD_CHANGE, "POST", data, {}, "site", false);
    }
}
