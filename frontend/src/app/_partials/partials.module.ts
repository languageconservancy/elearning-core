import { NgModule } from "@angular/core";
import { CommonModule } from "@angular/common";
import { FormsModule, ReactiveFormsModule } from "@angular/forms";
import { RouterModule } from "@angular/router";
import { ShareModule } from "ngx-sharebuttons";

import { AudioRecordComponent } from "./audio-record/audio-record.component";
import { BreadcrumbComponent } from "./breadcrumb/breadcrumb.component";
import { SocialShareComponent } from "./social-share/social-share.component";
import { RequestQuoteComponent } from "./request-quote/request-quote.component";
import { ImageZoomComponent } from "./image-zoom/image-zoom.component";
import { SiteImageComponent } from "./site-image/site-image.component";
import { EventPromoComponent } from "./event-promo/event-promo.component";
import { PageTitleComponent } from "./page-title/page-title.component";
import { VirtualKeyboardComponent } from "./virtual-keyboard/virtual-keyboard.component";
import { PipesModule } from "app/_pipes/pipes.module";
import { UnitProgressNavComponent } from "./unit-progress-nav/unit-progress-nav.component";
import { TimerComponent } from "./timer/timer.component";
import { AgreementsAcceptanceComponent } from "./agreements-acceptance/agreements-acceptance.component";
import { SignupSuggestionComponent } from "./signup-suggestion/signup-suggestion.component";
import { AgePromptComponent } from "./age-prompt/age-prompt.component";
import { UpdateModalComponent } from './update-modal/update-modal.component';

@NgModule({
    imports: [FormsModule, ReactiveFormsModule, CommonModule, RouterModule, ShareModule, PipesModule],
    declarations: [
        AudioRecordComponent,
        BreadcrumbComponent,
        SocialShareComponent,
        RequestQuoteComponent,
        ImageZoomComponent,
        SiteImageComponent,
        EventPromoComponent,
        PageTitleComponent,
        VirtualKeyboardComponent,
        UnitProgressNavComponent,
        TimerComponent,
        AgreementsAcceptanceComponent,
        SignupSuggestionComponent,
        AgePromptComponent,
        UpdateModalComponent,
    ],
    exports: [
        AudioRecordComponent,
        BreadcrumbComponent,
        SocialShareComponent,
        RequestQuoteComponent,
        ImageZoomComponent,
        SiteImageComponent,
        EventPromoComponent,
        PageTitleComponent,
        VirtualKeyboardComponent,
        UnitProgressNavComponent,
        TimerComponent,
        AgreementsAcceptanceComponent,
        SignupSuggestionComponent,
        AgePromptComponent,
    ],
})
export class PartialsModule {}
