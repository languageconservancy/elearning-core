import { Injectable } from "@angular/core";
import { BehaviorSubject, Subject } from "rxjs";

import { BaseService } from "./base.service";
import * as API from "app/_constants/api.constants";

@Injectable()
export class ForumService extends BaseService {
    private forumSubject = new BehaviorSubject<any>({});
    public forumObj = this.forumSubject.asObservable();

    private forumIdSubject = new BehaviorSubject<any>("");
    public forumObjId = this.forumIdSubject.asObservable();

    private userIdSubject = new BehaviorSubject<any>({});
    public currentUserId = this.userIdSubject.asObservable();

    private postReporterSubject = new Subject<any>();
    public postReporter = this.postReporterSubject.asObservable();

    private postReportDoneSubject = new Subject<boolean>();
    public postReportDone = this.postReportDoneSubject.asObservable();

    setForumParams(params: any) {
        this.forumSubject.next(params);
    }

    setForumId(params: any) {
        this.forumIdSubject.next(params);
    }

    setUserId(params: any) {
        this.userIdSubject.next(params);
    }

    openReportPostModal(params: any) {
        this.postReporterSubject.next(params);
    }

    postReportIsDone(status: boolean) {
        this.postReportDoneSubject.next(status);
    }

    getUsersFriends(params: any) {
        return this.callApi(API.User.GET_USERS_FRIENDS, "POST", params, {}, "site", true);
    }

    getForums(params: any) {
        return this.callApi(API.Forum.GET_FORUMS, "POST", params, {}, "site", true);
    }

    getForumPosts(params: any) {
        return this.callApi(API.Forum.GET_FORUM_POSTS, "POST", params, {}, "site", true);
    }

    createPost(params: any) {
        return this.callApi(API.Forum.ADD_POST, "POST", params, {}, "site", true);
    }

    getSinglePost(params: any) {
        return this.callApi(API.Forum.SINGLE_POST, "POST", params, {}, "site", true);
    }

    updatePost(params: any) {
        return this.callApi(API.Forum.UPDATE_POST, "POST", params, {}, "site", true);
    }

    deletePost(params: any) {
        return this.callApi(API.Forum.DELETE_POST, "POST", params, {}, "site", true);
    }

    flagPost(params: any) {
        return this.callApi(API.Forum.FLAG_POST, "POST", params, {}, "site", true);
    }

    flagPostList(params: any) {
        return this.callApi(API.Forum.FLAG_POST_LIST, "POST", params, {}, "site", true);
    }

    getFlagReasons() {
        return this.callApi(API.Forum.GET_FLAG_REASONS, "POST", {}, {}, "site", true);
    }
}
