import { enableProdMode, importProvidersFrom, provideZoneChangeDetection } from "@angular/core";
import { bootstrapApplication } from "@angular/platform-browser";
import { provideAnimations } from "@angular/platform-browser/animations";
import { AppComponent } from "@app/app.component";

import { AppModule } from "@app/app.module";
import { environment } from "@environments/environment";

if (environment.production) {
    enableProdMode();
}

bootstrapApplication(AppComponent, {
    providers: [provideZoneChangeDetection(), provideAnimations(), importProvidersFrom(AppModule)],
}).catch((err) => console.error(err));
