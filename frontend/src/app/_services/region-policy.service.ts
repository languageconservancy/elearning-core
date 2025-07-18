import { Injectable } from "@angular/core";
import { BaseService } from "./base.service";
import * as API from "app/_constants/api.constants";
import { MIN_ADULT_AGE_DEFAULT, MIN_SELF_CONSENT_AGE_DEFAULT } from "app/_constants/app.constants";

@Injectable({
    providedIn: "root",
})
export class RegionPolicyService {
    private adultMinAge: number = MIN_ADULT_AGE_DEFAULT; // Default adult age
    private selfConsentMinAge: number = MIN_SELF_CONSENT_AGE_DEFAULT; // Default self consent age

    constructor(private baseService: BaseService) {
        void this.fetchRegionPolicy();
    }

    /**
     * Fetches the region policy from the server.
     * @returns {Promise<void>} Promise that resolves when the region policy is fetched.
     */
    private async fetchRegionPolicy(): Promise<void> {
        try {
            const res = await this.baseService.callApi(
                API.User.GET_REGION_POLICY, "GET", {}, {}, "site", false,
            );
            if (!res.data?.status || !res.data?.results) {
                throw new Error("Error fetching region policy. " + res.data.message);
            }
            if (!res.data.results.adultMinAge || !res.data.results.selfConsentMinAge) {
                throw new Error("Region policy data is missing.");
            }

            this.setAdultMinAge(res.data.results.adultMinAge);
            this.setSelfConsentMinAge(res.data.results.selfConsentMinAge);
        } catch (error) {
            console.error("Error fetching region policy", error);
        }
    }

    /**
     * Sets the minimum age to be considered an adult.
     * @param age Minimum age to considered an adult
     */
    private setAdultMinAge(age: number): void {
        if (!age) {
            console.warn("Adult minimum age not set. Using default value.");
            return;
        }
        console.info("Adult minimum age set to", age);
        this.adultMinAge = age;
    }

    /**
     * Sets the minimum age to allow self consent.
     * @param age Minimum age to allow self consent
     */
    private setSelfConsentMinAge(age: number): void {
        if (!age) {
            console.warn("Self consent minimum age not set. Using default value.");
            return;
        }
        console.info("Self consent minimum age set to", age);
        this.selfConsentMinAge = age;
    }

    /**
     * Returns true if parental consent is required for the given age.
     * @param age Age to check if parental consent is required
     * @returns {boolean} True if parental consent is required, false otherwise.
     */
    public isChild(age: number): boolean {
        return isNaN(age) || age < this.selfConsentMinAge;
    }

    /**
     * Returns true if the age is less than the adult minimum age.
     * @param age Age to check
     * @returns {boolean} True if the age is less than the adult minimum age, false otherwise.
     */
    public isBetweenChildAndAdult(age: number): boolean {
        if (isNaN(age)) {
            return false;
        }
        return age >= this.selfConsentMinAge && age < this.adultMinAge;
    }

    /**
     * Returns true if the age is 18 or over.
     * @param age Age to check
     * @returns {boolean} True if the age is 18 or over, false otherwise (includes NaN).
     */
    public isAdult(age: number): boolean {
        return age >= this.adultMinAge;
    }
}
