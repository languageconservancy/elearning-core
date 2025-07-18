import { Component, AfterViewInit, ChangeDetectorRef } from "@angular/core";
import * as MRecordRTC from "recordrtc";
import * as DetectRTC from "detectrtc";
import WaveSurfer from "wavesurfer.js";
import Microphone from "wavesurfer.js/dist/plugin/wavesurfer.microphone.min.js";
import { Subscription } from "rxjs";

import { LessonsService } from "app/_services/lessons.service";
import { ReviewService } from "app/_services/review.service";

@Component({
    selector: "app-audio-record",
    templateUrl: "./audio-record.component.html",
    styleUrls: ["./audio-record.component.scss"],
})
export class AudioRecordComponent implements AfterViewInit {
    public audio: any;
    public recorder: any;
    public microphone: any;
    public isEdge: any;
    public isSafari: any;
    public btnStartRecording: any;
    public btnStopRecording: any;
    public btnReleaseMicrophone: any;
    public wavesurfer: any;
    public isPlaying: boolean = false;
    public isRecording: boolean = false;
    public showRecord: boolean = true;

    public popupSubscription: Subscription;

    constructor(
        private ref: ChangeDetectorRef,
        private lessonService: LessonsService,
        private reviewService: ReviewService,
    ) {
        WaveSurfer.microphone = Microphone;

        this.popupSubscription = this.lessonService.popup.subscribe((res) => {
            if (res.popUpClosed) {
                this.resetRecording();
                if (this.wavesurfer) {
                    this.wavesurfer.empty();
                }
            }
        });

        this.popupSubscription = this.reviewService.popup.subscribe((res) => {
            if (res.popUpClosed) {
                this.resetRecording();
                if (this.wavesurfer) {
                    this.wavesurfer.empty();
                }
            }
        });
    }

    ngAfterViewInit() {
        this.audio = document.querySelector("audio");
        this.isEdge = /msie\s|trident\/|edge\//i.test(window.navigator.userAgent);
        this.isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
        this.btnStartRecording = document.getElementById("btn-start-recording");
        this.btnStopRecording = document.getElementById("btn-stop-recording");
        this.btnReleaseMicrophone = document.querySelector("#btn-release-microphone");
        DetectRTC.load(() => {
            DetectRTC.hasMicrophone;
            DetectRTC.isWebsiteHasMicrophonePermissions;
            DetectRTC.audioInputDevices;
        });
        this.ref.detectChanges();
        this.setUpWaveSurfer();
    }

    startRecording() {
        if (DetectRTC.hasMicrophone) {
            if (!this.microphone) {
                this.captureMicrophone((mic) => {
                    this.microphone = mic;

                    if (this.isSafari) {
                        // this.startRecording();
                        alert(
                            "Please click startRecording button again. First time we tried to access your microphone. Now we will record it.",
                        );
                        return;
                    }

                    this.click(this.btnStartRecording);
                });
                return;
            }

            this.initRecordRTC();
        } else {
        }
    }

    private initRecordRTC() {
        const options: any = {
            type: "audio",
            numberOfAudioChannels: this.isEdge ? 1 : 2,
            checkForInactiveTracks: true,
            bufferSize: 16384,
        };

        if (navigator.platform && navigator.platform.toString().toLowerCase().indexOf("win") === -1) {
            options.sampleRate = 44100; // or 44100 or remove this line for default
        }

        if (this.recorder) {
            this.recorder.destroy();
            this.recorder = null;
        }

        this.recorder = MRecordRTC(this.microphone, options);
        this.recorder.startRecording();
        this.isRecording = true;
    }

    private captureMicrophone(callback) {
        if (typeof navigator.mediaDevices === "undefined" || !navigator.mediaDevices.getUserMedia) {
            alert("This browser does not support WebRTC getUserMedia API.");
        }

        const obj: any = { echoCancellation: false };
        navigator.mediaDevices
            .getUserMedia({
                audio: this.isEdge ? true : obj,
                video: false,
            })
            .then((mic) => {
                callback(mic);
            })
            .catch((error) => {
                alert("Unable to capture your microphone. Please check your connections.");
                console.error(error);
            });

        if (this.microphone) {
            callback(this.microphone);
            return;
        }
    }

    private stopRecordingCallback() {
        const blob = this.recorder.getBlob();
        this.wavesurfer.loadBlob(blob);
        this.ref.detectChanges();
        this.lessonService.setNewRecord({ blob: blob });
        this.reviewService.setNewRecord({ blob: blob });
        if (this.isSafari) {
            this.click(this.btnReleaseMicrophone);
        }
    }

    private click(el) {
        el.disabled = false; // make sure that element is not disabled
        const evt = document.createEvent("Event");
        evt.initEvent("click", true, true);
        el.dispatchEvent(evt);
    }

    private setUpWaveSurfer() {
        this.wavesurfer = WaveSurfer.create({
            container: "#record-audio",
            scrollParent: true,
            hideScrollbar: true,
            waveColor: "#ccc",
            progressColor: "#2392d0",
            cursorColor: "#2392d0",
            height: 90,
        });

        this.wavesurfer.on("finish", () => {
            this.isPlaying = false;
            setTimeout(() => {
                this.wavesurfer.stop();
                this.ref.detectChanges();
            }, 1000);
        });
    }

    releaseMic() {
        if (this.microphone) {
            this.microphone.stop();
            this.microphone = null;
        }
    }

    stopRecording() {
        this.isRecording = false;
        this.showRecord = false;
        this.recorder.stopRecording(() => {
            this.stopRecordingCallback();
        });
    }

    togglePlay() {
        if (this.wavesurfer.isPlaying()) {
            this.wavesurfer.stop();
        } else {
            this.wavesurfer.play();
        }
        this.isPlaying = !this.isPlaying;
        this.ref.detectChanges();
    }

    resetRecording(clicked: boolean = false) {
        this.lessonService.setAudipPath(true);
        this.showRecord = true;
        this.isPlaying = false;
        this.isRecording = false;
        if (this.wavesurfer) {
            this.wavesurfer.stop();
        }
        if (clicked) {
            this.ref.detectChanges();
        }
        if (this.recorder) {
            this.recorder.reset();
        }
    }
}
