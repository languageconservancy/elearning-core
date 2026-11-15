import { ApplicationConfig, provideBrowserGlobalErrorListeners } from "@angular/core";
import { provideRouter } from "@angular/router";
import { provideHttpClient, withFetch, withInterceptors } from "@angular/common/http";
import { APP_BASE_HREF } from "@angular/common";
import { routes } from "./app.routes";
import { sessionInterceptor } from "./workspace-api";

export const appConfig: ApplicationConfig = {
    providers: [
        provideBrowserGlobalErrorListeners(), provideHttpClient(withFetch(), withInterceptors([sessionInterceptor])),
        provideRouter(routes),
        { provide: APP_BASE_HREF, useFactory: () => location.pathname },
    ],
};
