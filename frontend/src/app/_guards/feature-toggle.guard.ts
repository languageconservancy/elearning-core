import { CanActivateFn, Router } from "@angular/router";
import { inject } from "@angular/core";
import { SiteSettingsService } from "app/_services/site-settings.service";

export const FeatureToggleGuard: (featureKey: string) => CanActivateFn = (featureKey) => {
    return async () => {
        const featureToggleService = inject(SiteSettingsService);
        const router = inject(Router);

        const isEnabled = (await featureToggleService.getFeatures(featureKey)) === "1";
        if (isEnabled) {
            return true;
        } else {
            return router.createUrlTree(["/page-not-found"]);
        }
    };
};
