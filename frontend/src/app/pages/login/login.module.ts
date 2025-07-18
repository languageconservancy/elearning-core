import { NgModule } from "@angular/core";
import { CommonModule } from "@angular/common";
import { RouterModule } from "@angular/router";
import { FormsModule, ReactiveFormsModule } from "@angular/forms";
import {
    SocialLoginModule,
    SocialAuthServiceConfig,
    GoogleLoginProvider,
    FacebookLoginProvider,
} from "@abacritt/angularx-social-login";

import { PipesModule } from "app/_pipes/pipes.module";
import { PartialsModule } from "app/_partials/partials.module";
import { LoginComponent } from "./login/login.component";
import { environment } from "environments/environment";

@NgModule({
    declarations: [LoginComponent],
    imports: [
        CommonModule,
        FormsModule,
        ReactiveFormsModule,
        RouterModule,
        SocialLoginModule,
        PipesModule,
        PartialsModule,
    ],
    providers: [
        {
            provide: "SocialAuthServiceConfig",
            useValue: {
                autoLogin: false,
                providers: [
                    {
                        id: GoogleLoginProvider.PROVIDER_ID,
                        provider: new GoogleLoginProvider(environment.GOOGLE_CLIENT_ID_WEB, {
                            oneTapEnabled: false,
                        }),
                    },
                    {
                        id: FacebookLoginProvider.PROVIDER_ID,
                        provider: new FacebookLoginProvider(environment.FACEBOOK_APP_ID),
                    },
                ],
                onError: (err) => {
                    console.error(err);
                },
            } as SocialAuthServiceConfig,
        },
    ],
})
export class LoginModule {}
