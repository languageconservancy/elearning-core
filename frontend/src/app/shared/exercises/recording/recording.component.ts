import { Component, OnInit, OnDestroy, AfterViewInit, ChangeDetectorRef, Input } from "@angular/core";
import { Subscription } from "rxjs";
import * as WaveSurfer from "wavesurfer.js";
import Microphone from "wavesurfer.js/dist/plugin/wavesurfer.microphone.min.js";

import { CookieService } from "app/_services/cookie.service";
import { Loader } from "app/_services/loader.service";
import { LessonsService } from "app/_services/lessons.service";
import { ReviewService } from "app/_services/review.service";
import { ExerciseService } from "app/_services/exercise.service";
import { AnswerType } from "app/shared/utils/elearning-types";
import { AudioService } from "app/_services/audio.service";
import { LocalStorageService } from "app/_services/local-storage.service";
import { KeyboardService } from "app/shared/keyboard/keyboard.service";
import { SocialWebService } from "app/_services/social-web.service";
import { environment } from "environments/environment";

declare let jQuery: any;

@Component({
    selector: "app-recording",
    templateUrl: "./recording.component.html",
    styleUrls: ["./recording.component.scss"],
})
export class RecordingComponent implements OnInit, OnDestroy, AfterViewInit {
    public disableBtn: boolean = false;
    public mainResponse: string = "";
    public wavesurfer: any;
    public isPlaying: boolean = false;
    public firstRecorded: boolean = false;
    public file: any = {};
    public sharableLink: string = "";
    public cardIdArray: any = [];
    public disablePlay: boolean = true;
    public shared: boolean = false;
    public successMsg: string = "";
    public customCardId: number = null;
    public savedAudio: any = {};
    public emailShareModel: any = [];
    public forumShareModel: any = {
        title: "",
        content: "",
    };
    public successFlag: boolean = false;

    @Input() sessionType: string;
    public Answer = AnswerType;
    private specifiedService;

    public exerciseSubscription: Subscription;
    public questionSubscription: Subscription;
    public popupSubscription: Subscription;
    public recordSubscription: Subscription;
    public audioPathSubscription: Subscription;
    private keyboardToggleMediaSubscription: Subscription;
    public facebookLoaded: boolean = false;

    constructor(
        private cookieService: CookieService,
        private lessonService: LessonsService,
        private reviewService: ReviewService,
        public exerciseService: ExerciseService,
        public audioService: AudioService,
        private loader: Loader,
        private localStorage: LocalStorageService,
        private ref: ChangeDetectorRef,
        private keyboardService: KeyboardService,
        private socialWebService: SocialWebService,
    ) {
        this.cookieService
            .get("AuthUser")
            .then((value) => {
                if (value == "") {
                    throw value;
                }
                this.exerciseService.user = JSON.parse(value);
            })
            .catch(() => {
                console.error("AuthUser cookie empty");
            });

        WaveSurfer.microphone = Microphone;

        this.socialWebService
            .initFacebook()
            .then(() => {
                this.facebookLoaded = true;
            })
            .catch((error) => {
                console.error(error);
                return;
            });
    }

    ngOnInit() {
        this.setService();

        // Exercise subscription
        this.exerciseSubscription = this.specifiedService.currentExercise.subscribe((exercise) => {
            this.exerciseService.exercise = {};
            if (Object.keys(exercise).length > 0 && exercise.exercise_type == "recording") {
                this.exerciseService.exercise = exercise;
                this.mainResponse = exercise.promteresponsetype.split("-")[1];
            }
        });

        // Question subscription
        if (this.sessionType == "exercise") {
            this.questionSubscription = this.lessonService.currentQuestion.subscribe((ques) => {
                this.setUpQuestionSubscription(ques);
            });
        } else if (this.sessionType == "review") {
            this.questionSubscription = this.reviewService.currentExercise.subscribe((ques) => {
                this.setUpQuestionSubscription(ques);
            });
        }

        // Record subscription
        this.recordSubscription = this.specifiedService.newRecord.subscribe((obj) => {
            if (obj && Object.keys(obj).length > 0) {
                this.file = new File([obj.blob], this.getFileName("mp3"), { type: "audio/*" });
                this.firstRecorded = true;
                this.ref.detectChanges();
            }
        });

        // Audio Path subscription
        if (this.sessionType == "exercise") {
            this.audioPathSubscription = this.lessonService.audioPath.subscribe((param) => {
                if (param) {
                    this.sharableLink = "";
                }
            });
        }

        // Popup subscription
        this.popupSubscription = this.specifiedService.popup.subscribe((res) => {
            if (res.popUpClosed) {
                this.audioService.pauseAudio();
            }
        });

        this.setKeyboardListeners(true);
    }

    ngOnDestroy() {
        this.exerciseSubscription.unsubscribe();
        this.questionSubscription.unsubscribe();
        this.popupSubscription.unsubscribe();
        this.recordSubscription.unsubscribe();
        this.audioPathSubscription.unsubscribe();
        this.setKeyboardListeners(false);

        this.lessonService.setNewRecord({});
        this.sharableLink = "";
    }

    ngAfterViewInit() {
        this.setUpWaveSurfer();
    }

    setUpQuestionSubscription(ques) {
        this.audioService.pauseAudio();
        if (this.exerciseService.exercise.exercise_type == "recording" && Object.keys(ques).length > 0) {
            this.exerciseService.question = {};
            this.exerciseService.question = ques.question;
            this.exerciseService.response = ques.response;
            this.disableBtn = false;
            this.setPromptResponseTypes();
            this.getCardIdList();
            setTimeout(() => {
                if (this.exerciseService.promptTypes.indexOf("a") > -1) {
                    this.audioService.playPauseAudio(this.exerciseService.question.FullAudioUrl);
                }

                if (this.wavesurfer) {
                    this.wavesurfer.load(this.exerciseService.response.FullAudioUrl);
                    this.wavesurfer.on("ready", () => {
                        this.disablePlay = false;
                        this.ref.detectChanges();
                    });
                }
            }, 200);
        }
    }

    setService() {
        if (this.sessionType == "exercise") {
            this.specifiedService = this.lessonService;
        } else if (this.sessionType == "review") {
            this.specifiedService = this.reviewService;
        }
    }

    setKeyboardListeners(turnOn: boolean) {
        if (turnOn) {
            this.keyboardToggleMediaSubscription = this.keyboardService.toggleMediaEvent.subscribe(() => {
                this.togglePlay();
            });
        } else {
            if (!!this.keyboardToggleMediaSubscription) this.keyboardToggleMediaSubscription.unsubscribe();
        }
    }

    private setLoader(val: boolean) {
        this.loader.setLoader(val);
    }

    private setUpWaveSurfer() {
        this.wavesurfer = WaveSurfer.create({
            container: "#waveform",
            scrollParent: true,
            hideScrollbar: true,
            waveColor: "#ccc", // gray
            progressColor: "#2392d0", // neutral blue
            cursorColor: "#2392d0", // neutral blue
            height: 90,
            normalize: true,
        });

        this.wavesurfer.on("finish", () => {
            this.isPlaying = false;
            setTimeout(() => {
                this.wavesurfer.stop();
                this.ref.detectChanges();
            }, 1000);
        });
    }

    private setPromptResponseTypes() {
        if (this.exerciseService.exercise.card_type == "custom") {
            if (this.exerciseService.question.PromptType == "card") {
                this.exerciseService.promptTypes = this.exerciseService.question.exerciseOptions.prompt_preview_option
                    ? this.exerciseService.question.exerciseOptions.prompt_preview_option
                          .split(",")
                          .map((el) => el.trim())
                    : [];
                this.exerciseService.responseTypes = this.exerciseService.question.exerciseOptions
                    .responce_preview_option
                    ? this.exerciseService.question.exerciseOptions.responce_preview_option
                          .split(",")
                          .map((el) => el.trim())
                    : [];
            } else {
                this.exerciseService.promptTypes = [];
                if (this.exerciseService.question.prompt_audio_id !== null) {
                    this.exerciseService.promptTypes.push("a");
                    this.exerciseService.question.FullAudioUrl = this.exerciseService.question.audio.FullUrl;
                }

                if (this.exerciseService.question.prompt_image_id !== null) {
                    this.exerciseService.promptTypes.push("i");
                }
            }
        } else {
            this.exerciseService.promptTypes = this.exerciseService.question.exerciseOptions.prompt_preview_option
                ? this.exerciseService.question.exerciseOptions.prompt_preview_option.split(",").map((el) => el.trim())
                : [];
            this.exerciseService.responseTypes = this.exerciseService.question.exerciseOptions.responce_preview_option
                ? this.exerciseService.question.exerciseOptions.responce_preview_option
                      .split(",")
                      .map((el) => el.trim())
                : [];
        }
    }

    private getRandomString() {
        if (window.crypto && window.crypto.getRandomValues && navigator.userAgent.indexOf("Safari") === -1) {
            const uIntArray = new Uint32Array(3);
            const a = window.crypto.getRandomValues(uIntArray);
            let token = "";
            for (let i = 0, l = uIntArray.length; i < l; i++) {
                token += a[i].toString(36);
            }
            return token;
        } else {
            return (Math.random() * new Date().getTime()).toString(36).replace(/\./g, "");
        }
    }

    private getFileName(fileExtension) {
        const d = new Date();
        const year = d.getFullYear();
        const month = d.getMonth();
        const date = d.getDate();
        return "OW-" + year + month + date + "-" + this.getRandomString() + "." + fileExtension;
    }

    private getCardIdList() {
        if (this.exerciseService.exercise.card_type == "custom") {
            if (this.exerciseService.question.PromptType == "card") {
                if (this.cardIdArray.indexOf(this.exerciseService.question.id) == -1) {
                    this.cardIdArray.push(this.exerciseService.question.id);
                    this.customCardId = this.exerciseService.question.id;
                }
            }

            if (this.exerciseService.response.ResponseType == "card") {
                if (this.cardIdArray.indexOf(this.exerciseService.response.id) == -1) {
                    this.cardIdArray.push(this.exerciseService.response.id);
                }
            }
        } else {
            if (
                this.exerciseService.question.exerciseOptions.type == "card" ||
                this.exerciseService.question.exerciseOptions.type == "group"
            ) {
                if (this.cardIdArray.indexOf(this.exerciseService.question.id) == -1) {
                    this.cardIdArray.push(this.exerciseService.question.id);
                }
            }

            if (
                this.exerciseService.response.exerciseOptions.type == "card" ||
                this.exerciseService.response.exerciseOptions.type == "group"
            ) {
                if (this.cardIdArray.indexOf(this.exerciseService.response.id) == -1) {
                    this.cardIdArray.push(this.exerciseService.response.id);
                }
            }
        }
    }

    togglePlay() {
        this.audioService.pauseAudio();
        if (this.wavesurfer.isPlaying()) {
            this.wavesurfer.stop();
        } else {
            this.wavesurfer.play();
        }
        this.isPlaying = !this.isPlaying;
        this.ref.detectChanges();
    }

    submit() {
        if (
            this.exerciseService.response.exerciseOptions.response_true_false == "N" &&
            this.exerciseService.response.FullAudioUrl
        ) {
            this.audioService.playPauseAudio(this.exerciseService.response.FullAudioUrl);
        }

        if (this.sharableLink !== "") {
            this.submitAnswer();
        } else {
            this.submitFile();
        }
    }

    submitAnswer() {
        const params = {
            level_id: this.localStorage.getItem("LevelID"),
            unit_id: this.localStorage.getItem("unitID"),
            exercise_id: this.exerciseService.exercise.id,
            card_id:
                this.exerciseService.exercise.card_type == "custom"
                    ? this.customCardId
                    : this.exerciseService.question.id,
            activity_type: this.sessionType,
            user_id: this.exerciseService.user.id,
            answar_type: "right",
            experiencecard_ids: this.exerciseService.cardIdArray.join(),
            popup_status: true,
        };
        this.specifiedService.answerGiven(params);
    }

    submitFile(fromShare: boolean = false) {
        this.setLoader(true);
        const formData = new FormData();
        formData.append("audio", this.file);
        formData.append("user_id", this.exerciseService.user.id);
        formData.append("exercise_id", this.exerciseService.exercise.id);
        formData.append("is_app", "0");
        formData.append("type", "exercise");

        this.specifiedService
            .saveRecordedAudio(formData)
            .then((res: any) => {
                if (res.data.status) {
                    this.setLoader(false);
                    this.savedAudio = res.data.data.results;
                    this.sharableLink = res.data.data.results.link;
                    if (fromShare) {
                        jQuery("#share").modal("show");
                    } else {
                        this.submitAnswer();
                    }
                }
            })
            .catch((err) => {
                console.error("Error: ", err);
            });
    }

    shareFile() {
        if (this.sharableLink !== "") {
            jQuery("#share").modal("show");
        } else {
            this.submitFile(true);
        }
    }

    share(type) {
        jQuery("#share").modal("hide");
        switch (type) {
            case "fb":
                this.fbShare();
                break;
            case "email":
                jQuery("#email-share").modal("show");
                break;
            case "forum":
                jQuery("#forum-share").modal("show");
                break;
            default:
                break;
        }
    }

    fbShare() {
        if (!this.facebookLoaded) {
            console.warn("Can't share on Facebook. Facebook not loaded.");
            return;
        }

        this.socialWebService
            .ui({
                method: "share_open_graph",
                action_type: "og.shares",
                action_properties: JSON.stringify({
                    object: {
                        "og:url": this.sharableLink,
                        "og:title": "Audio Recording by " + this.exerciseService.user.name,
                        "og:description": "This audio was recorded as an answer to an exercise.",
                        "og:image": environment.ROOT + "assets/images/ro-red.png",
                    },
                }),
            })
            .then((res) => {
                console.log(res);
                this.showSuccess("Recording successfully shared on Facebook.", true);
            })
            .catch((err) => {
                console.error(err);
            });
    }

    emailShare() {
        console.log(this.emailShareModel);
        const emailsArray = [];
        this.emailShareModel.forEach((email) => {
            emailsArray.push(email.value);
        });

        setTimeout(() => {
            this.callEmailShareApi(emailsArray);
        }, 100);
    }

    private callEmailShareApi(emails) {
        const params = {
            email_ids: emails.join(),
            audio_id: this.savedAudio.id,
        };

        this.setLoader(true);
        this.lessonService
            .shareRecordedAudioEmail(params)
            .then(() => {
                jQuery("#email-share").modal("hide");
                this.showSuccess("Audio successfully shared via email.", true);
            })
            .catch((err) => {
                console.error(err);
            })
            .finally(() => {
                this.setLoader(false);
            });
    }

    forumShare() {
        if (this.forumShareModel.title.trim() == "") {
            this.showSuccess("Please enter the share title", false);
            return;
        }
        if (this.forumShareModel.content.trim() == "") {
            this.showSuccess("Please enter the share content", false);
            return;
        }
        const params = {
            user_id: this.exerciseService.user.id,
            title: this.forumShareModel.title,
            content: this.forumShareModel.content,
            audio_id: this.savedAudio.id,
            unit_id: this.localStorage.getItem("unitID"),
        };
        this.setLoader(true);
        this.specifiedService
            .shareRecordedAudioForum(params)
            .then(() => {
                jQuery("#forum-share").modal("hide");
                this.showSuccess("Audio successfully shared on forum.", true);
            })
            .catch((err) => {
                console.error(err);
            })
            .finally(() => {
                this.setLoader(false);
            });
    }

    showSuccess(msg, flag) {
        this.successFlag = flag;
        this.shared = true;
        this.successMsg = msg;
        setTimeout(() => {
            this.shared = false;
        }, 2500);
    }
}
