import { ComponentFixture, TestBed, waitForAsync } from "@angular/core/testing";
import { RouterTestingModule } from "@angular/router/testing";
import { HttpClientModule } from "@angular/common/http";
import { CookieService as NgCookieService } from "ngx-cookie-service";
import {
    SocialAuthServiceConfig,
    GoogleLoginProvider,
    FacebookLoginProvider,
    AmazonLoginProvider,
} from "@abacritt/angularx-social-login";

import { CookieService } from "app/_services/cookie.service";
import { PartialsModule } from "app/_partials/partials.module";
import { LocalStorageService } from "app/_services/local-storage.service";
import { Loader } from "app/_services/loader.service";
import { LessonsService } from "app/_services/lessons.service";
import { AudioService } from "app/_services/audio.service";
import { KeyboardService } from "app/shared/keyboard/keyboard.service";
import { PipesModule } from "app/_pipes/pipes.module";
import { LessonsComponent } from "./lessons.component";

fdescribe("LessonsComponent", () => {
    let component: LessonsComponent;
    let fixture: ComponentFixture<LessonsComponent>;
    // const ValidImageSrc = "https://owoksape.com/assets/images/icon_01.png";

    const frame = {
        id: 2772,
        lesson_id: 376,
        audio_id: null,
        duration: null,
        name: "Frame 1",
        number_of_block: 1,
        frame_preview: "landscape",
        frameorder: 1,
        modified: "2021-05-27T12:13:13",
        created: "2021-05-16T15:12:28",
        lesson_frame_blocks: [],
        FrameAudioUrl: "",
    };

    // const simpleBlock = {
    //     id: 6098,
    //     lesson_frame_id: 2772,
    //     card_id: null,
    //     audio_id: null,
    //     image_id: null,
    //     video_id: null,
    //     block_no: 1,
    //     type: "html",
    //     is_card_lakota: "N",
    //     is_card_english: "N",
    //     is_card_audio: "N",
    //     is_card_video: "N",
    //     is_card_image: "N",
    //     custom_html:
    //         "\u003Cb\u003EModel for following exercise set:\u003C/b\u003E\r\nDescribe each picture from the previous activity using only the verb. See the corresponding English translation using pronouns instead of nouns.",
    //     created: "2021-05-27T12:13:13",
    //     modified: "2021-05-27T12:13:13",
    //     AudioName: "",
    //     ImageName: "",
    //     VideoName: "",
    //     AudioUrl: "",
    //     ImageUrl: "",
    //     VideoUrl: "",
    //     CardDetails: "",
    // };

    // const imageBlock = {
    //     id: 6099,
    //     lesson_frame_id: 2772,
    //     card_id: 4656,
    //     audio_id: null,
    //     image_id: null,
    //     video_id: null,
    //     block_no: 2,
    //     type: "card",
    //     is_card_lakota: "Y",
    //     is_card_english: "Y",
    //     is_card_audio: "N",
    //     is_card_video: "N",
    //     is_card_image: "Y",
    //     custom_html: null,
    //     created: "2021-05-27T12:13:13",
    //     modified: "2021-05-27T12:13:13",
    //     AudioName: "",
    //     ImageName: "",
    //     VideoName: "",
    //     AudioUrl: "",
    //     ImageUrl: "",
    //     VideoUrl: "",
    //     CardDetails: {
    //         id: 4656,
    //         inflection_id: null,
    //         reference_dictionary_id: null,
    //         image_id: 28966,
    //         video_id: null,
    //         audio: "6071",
    //         card_type_id: 3,
    //         lakota: "Wi\u010dh\u00e1\u0161a ki\u014b \u0161\u00fa\u014bkawak\u021f\u00e1\u014b ki\u014b oy\u00faspe.",
    //         english: "The man caught the horse.",
    //         gender: "default",
    //         include_review: "1",
    //         alt_lakota: "",
    //         alt_english: "",
    //         created: "2020-06-23T16:49:35",
    //         modified: "2021-06-10T07:55:13",
    //         is_active: 1,
    //         cardtype: {
    //             id: 3,
    //             title: "Pattern",
    //             created: null,
    //             modified: null,
    //         },
    //         video: null,
    //         image: {
    //             id: 28966,
    //             upload_user_id: 12,
    //             name: "HBunit5image10_card",
    //             description: "HBunit5image10_card",
    //             format: "png",
    //             type: "image",
    //             file_name: "HBunit5image10_card.png",
    //             aws_link: "https://owoksape.s3.us-west-2.amazonaws.com/HBunit5image10_card.png",
    //             created: "2020-06-22T00:00:00",
    //             modified: "2021-06-10T07:47:57",
    //             FullUrl: "https://owoksape.s3.us-west-2.amazonaws.com/HBunit5image10_card.png",
    //             ResizeImageUrl: "https://owoksape.s3.us-west-2.amazonaws.com/resizeProfile/HBunit5image10_card.png",
    //         },
    //         inflection: null,
    //         dictionary: null,
    //         FullAudioUrl: "https://owoksape.s3.us-west-2.amazonaws.com/lgh-5-12.mp3",
    //         FullAudioUrlArray: ["https://owoksape.s3.us-west-2.amazonaws.com/lgh-5-12.mp3"],
    //         AudioFile: "lgh-5-12.mp3",
    //         AudioFileArray: ["lgh-5-12.mp3"],
    //         AudioCount: 1,
    //         ImageFile: "HBunit5image10_card.png",
    //         VideoFile: "",
    //         TypeTitle: "Pattern",
    //     },
    // };

    beforeEach(waitForAsync(() => {
        void TestBed.configureTestingModule({
            declarations: [LessonsComponent],
            providers: [
                CookieService,
                LessonsService,
                AudioService,
                KeyboardService,
                Loader,
                LocalStorageService,
                NgCookieService,
                {
                    provide: "SocialAuthServiceConfig",
                    useValue: {
                        providers: [
                            {
                                id: GoogleLoginProvider.PROVIDER_ID,
                                provider: new GoogleLoginProvider("clientId"),
                            },
                            {
                                id: FacebookLoginProvider.PROVIDER_ID,
                                provider: new FacebookLoginProvider("clientId"),
                            },
                            {
                                id: AmazonLoginProvider.PROVIDER_ID,
                                provider: new AmazonLoginProvider("clientId"),
                            },
                        ],
                    } as SocialAuthServiceConfig,
                },
            ],
            imports: [RouterTestingModule, HttpClientModule, PartialsModule, PipesModule],
        }).compileComponents();
    }));

    beforeEach(() => {
        frame.lesson_frame_blocks = [];
        fixture = TestBed.createComponent(LessonsComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it("should create", () => {
        expect(component).toBeTruthy();
    });

    // it("correct image should display", fakeAsync(() => {
    //     frame.lesson_frame_blocks.push(imageBlock);
    //     frame.number_of_block = 1;
    //     frame.lesson_frame_blocks[0].CardDetails.image.ResizeImageUrl = ValidImageSrc;
    //     component.frame = frame;
    //     fixture.detectChanges();
    //     void fixture.whenStable().then(() => {
    //         const img = fixture.nativeElement.querySelector("#one-block-block1-img") as HTMLImageElement;
    //         expect(img.src).toEqual(ValidImageSrc);
    //     });
    // }));

    // it("invalid image should display alternative image one-block frames", fakeAsync(() => {
    //     const imageErrorSpy = spyOn(component, "imageError").and.callThrough();
    //     expect(imageErrorSpy).toHaveBeenCalledTimes(0);
    //     frame.lesson_frame_blocks.push(imageBlock);
    //     frame.number_of_block = 1;
    //     frame.lesson_frame_blocks[0].CardDetails.image.ResizeImageUrl = "invalid-src";
    //     frame.lesson_frame_blocks[0].CardDetails.image.FullUrl = "";
    //     component.frame = frame;
    //     fixture.detectChanges();
    //     const img = fixture.nativeElement.querySelector("#one-block-block1-img") as HTMLImageElement;
    //     fixture
    //         .whenStable()
    //         .then(() => {
    //             expect(img.src).toEqual(window.location.origin + "/invalid-src");
    //         })
    //         .catch((err) => {
    //             console.error(err);
    //         });
    //     // Update FullUrl to valid image and trigger error event
    //     frame["lesson_frame_blocks"][0].CardDetails.image.FullUrl = ValidImageSrc;
    //     fixture.detectChanges();
    //     // Create onerror event and trigger it
    //     tick(500);
    //     const evt = new Event("onerror");
    //     img.dispatchEvent(evt);
    //     component["imageError"](evt, 0);
    //     fixture.detectChanges();
    //     fixture
    //         .whenStable()
    //         .then(() => {
    //             expect(imageErrorSpy).toHaveBeenCalledTimes(1);
    //             expect(img.src).toEqual(ValidImageSrc);
    //         })
    //         .catch((err) => {
    //             console.error(err);
    //         });
    // }));

    // it("invalid image should display alternative image in two-block frames", fakeAsync(() => {
    //     const imageErrorSpy = spyOn(component, "imageError").and.callThrough();
    //     expect(imageErrorSpy).toHaveBeenCalledTimes(0);
    //     frame.lesson_frame_blocks.push(imageBlock);
    //     frame.lesson_frame_blocks.push(imageBlock);
    //     frame.number_of_block = 2;
    //     for (let i = 0; i < 2; ++i) {
    //         frame.lesson_frame_blocks[i].CardDetails.image.ResizeImageUrl = "invalid-src";
    //         frame.lesson_frame_blocks[i].CardDetails.image.FullUrl = "";
    //         component.frame = frame;
    //         fixture.detectChanges();
    //         const img = fixture.nativeElement.querySelector(`#two-block-block${i + 1}-img`) as HTMLImageElement;
    //         fixture
    //             .whenStable()
    //             .then(() => {
    //                 expect(img.src).toEqual(window.location.origin + "/invalid-src");
    //             })
    //             .catch((err) => {
    //                 console.error(err);
    //             });
    //         // Update FullUrl to valid image and trigger error event
    //         frame["lesson_frame_blocks"][i].CardDetails.image.FullUrl = ValidImageSrc;
    //         fixture.detectChanges();
    //         // Create onerror event and trigger it
    //         tick(500);
    //         const evt = new Event("onerror");
    //         img.dispatchEvent(evt);
    //         component["imageError"](evt, 0);
    //         fixture.detectChanges();
    //         fixture
    //             .whenStable()
    //             .then(() => {
    //                 expect(imageErrorSpy).toHaveBeenCalledTimes(i + 1);
    //                 expect(img.src).toEqual(ValidImageSrc);
    //             })
    //             .catch((err) => {
    //                 console.error(err);
    //             });
    //     }
    // }));

    // it("invalid image should display alternative image three-block frames", fakeAsync(() => {
    //     const imageErrorSpy = spyOn(component, "imageError").and.callThrough();
    //     expect(imageErrorSpy).toHaveBeenCalledTimes(0);
    //     frame.lesson_frame_blocks.push(imageBlock);
    //     frame.lesson_frame_blocks.push(imageBlock);
    //     frame.lesson_frame_blocks.push(imageBlock);
    //     frame.number_of_block = 3;
    //     for (let i = 0; i < frame.number_of_block; ++i) {
    //         frame.lesson_frame_blocks[i].CardDetails.image.ResizeImageUrl = "invalid-src";
    //         frame.lesson_frame_blocks[i].CardDetails.image.FullUrl = "";
    //         component.frame = frame;
    //         fixture.detectChanges();
    //         const img = fixture.nativeElement.querySelector(`#box-${i + 1}-img > img`) as HTMLImageElement;
    //         fixture
    //             .whenStable()
    //             .then(() => {
    //                 expect(img.src).toEqual(window.location.origin + "/invalid-src");
    //             })
    //             .catch((err) => {
    //                 console.error(err);
    //             });
    //         // Update FullUrl to valid image and trigger error event
    //         frame.lesson_frame_blocks[i].CardDetails.image.FullUrl = ValidImageSrc;
    //         fixture.detectChanges();
    //         // Create onerror event and trigger it
    //         tick(500);
    //         const evt = new Event("onerror");
    //         img.dispatchEvent(evt);
    //         component["imageError"](evt, 0);
    //         fixture.detectChanges();
    //         fixture
    //             .whenStable()
    //             .then(() => {
    //                 expect(imageErrorSpy).toHaveBeenCalledTimes(i + 1);
    //                 expect(img.src).toEqual(ValidImageSrc);
    //             })
    //             .catch((err) => {
    //                 console.error(err);
    //             });
    //     }
    // }));
});
