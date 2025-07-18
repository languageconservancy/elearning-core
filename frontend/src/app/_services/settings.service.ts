import { BehaviorSubject } from "rxjs";
import { Injectable } from "@angular/core";
import { BaseService } from "./base.service";
import * as API from "app/_constants/api.constants";
import { ApiResponse } from "app/shared/utils/elearning-types";

@Injectable()
export class SettingsService extends BaseService {
    private tabSubject = new BehaviorSubject<any>("");
    private gallerySubject = new BehaviorSubject<any>([]);
    private parentalLockSubject = new BehaviorSubject<any>("");
    private userSubject = new BehaviorSubject<any>({});
    private maintenanceSubject = new BehaviorSubject<any>(false);
    private publicProfileSubject = new BehaviorSubject<any>({});
    private promoSeenSubject = new BehaviorSubject<boolean>(false);

    public currentTab = this.tabSubject.asObservable();
    public gallery = this.gallerySubject.asObservable();
    public promoSeen = this.promoSeenSubject.asObservable();
    public parentalLockCode = this.parentalLockSubject.asObservable();
    public userObj = this.userSubject.asObservable();
    public maintenanceMode = this.maintenanceSubject.asObservable();
    public currentProfile = this.publicProfileSubject.asObservable();

    setTab(tab: string) {
        this.tabSubject.next(tab);
    }
    setPromoSeen(seen: boolean) {
        this.promoSeenSubject.next(seen);
    }

    parentalLockInput(lockCode: string) {
        this.parentalLockSubject.next(lockCode);
    }

    setGalleryImages(images: any) {
        this.gallerySubject.next(images);
    }

    setUser(user: any) {
        this.userSubject.next(user);
    }

    setMaintenanceMode(value: boolean) {
        this.maintenanceSubject.next(value);
    }

    setPublicProfile(params: any) {
        this.publicProfileSubject.next(params);
    }

    getImage() {
        return this.callApi(API.Settings.LOGIN_IMAGE, "GET", {}, {}, "site", false);
    }

    getCMS() {
        return this.callApi(API.Settings.CMS_CONTENT, "GET", {}, {}, "site", false);
    }

    getMaintenanceMode() {
        return this.callApi(API.Settings.CONSTRUCTION_MODE, "GET", {}, {}, "site", false);
    }

    getPublicUser(userId: number | string) {
        return this.callApi(API.User.GET_PUBLIC_USER + "/" + userId + ".json", "GET", {}, {}, "site", true);
    }

    getAllSettings(userId: any) {
        return this.callApi(API.User.GET_USER + "/" + userId + ".json", "GET", {}, {}, "site", true);
    }

    updateUserSettings(data) {
        return this.callApi(API.User.UPDATE_SETTING, "POST", data, {}, "site", true);
    }

    updateUserData(data: any) {
        return this.callApi(API.User.UPDATE, "POST", data, {}, "site", true);
    }

    resetPassword(data: any) {
        return this.callApi(API.User.RESET_PASSWORD, "POST", data, {}, "site", false);
    }

    deactivateProfile(data: any) {
        return this.callApi(API.User.DEACTIVATE_ACCOUNT, "POST", data, {}, "site", true);
    }

    deleteAccount(data: { userId: number }) {
        return this.callApi(API.User.DELETE_ACCOUNT, "POST", data, {}, "site", true);
    }

    resetProgressData(data: any): Promise<any> {
        return this.callApi(API.Points.RESET_PROGRESS, "POST", data, {}, "site", true);
    }

    updateUserImage(data: any): Promise<any> {
        return this.callApi(API.User.UPDATE_SETTING, "POST", data, {}, "site", true);
    }

    updateGalleryImage(data: any) {
        return this.callApi(API.User.UPLOAD_GALLERY_IMAGE, "POST", data, {}, "site", true);
    }

    async notifyParentOfChildSignup(data: {
        parents_email: string;
        user_id: number;
    }): Promise<boolean> {
        const res: ApiResponse = await this.callApi(
            API.User.NOTIFY_PARENT,
            "POST",
            data,
            {},
            "site",
            true,
        );
        if (!res.data?.status) {
            throw new Error(res.data.message);
        }
        return true;
    }
}
