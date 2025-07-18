import { Injectable } from "@angular/core";
import { Subscription } from "rxjs";

import { KeyboardService } from "app/shared/keyboard/keyboard.service";
import { AnswerType } from "app/shared/utils/elearning-types";

@Injectable()
export class ExerciseService {
    public user: any = {}; // user object
    public exercise: any = {}; // exercise object
    public question: any = {}; // question object
    public response: any = {}; // response object
    public choices: any = []; // array of choice objects
    public promptType: string = ""; //
    public promptTypes: any = [];
    public primaryPromptType: string = "";
    public responseType: string = "";
    public responseTypes: any = [];
    public firstTime: boolean = true;
    public nextButtonShouldBeClickable: boolean = false;
    public hasError: boolean = false;
    public userAnswer: AnswerType = AnswerType.NONE;
    public answerGivenObj: any = {};
    public ansId: any;
    public exerciseCompleted: boolean;

    public cardIdArray: any = [];
    public customCardId: number = null;
    private keyboardCloseWindowSubscription: Subscription;

    setCloseWindowSubscription(turnOn: boolean, keyboardService: KeyboardService) {
        if (turnOn) {
            this.keyboardCloseWindowSubscription = keyboardService.submitOrCloseEvent.subscribe(() => {});
        } else {
            this.keyboardCloseWindowSubscription.unsubscribe();
        }
    }

    /**
     * Sets the prompt and response types based on the exercise and question properties.
     */
    setPromptResponseTypes() {
        this.promptTypes = [];
        this.responseTypes = [];
        this.responseType = this.exercise.promteresponsetype ? this.exercise.promteresponsetype.split("-")[1] : "";

        if (this.isCustomExercise()) {
            if (this.isPromptTypeCard()) {
                this.setPromptTypesFromExerciseOptionsOrExercise();
            } else {
                this.setPromptTypesForHtml();
            }

            if (this.isResponseTypeCard()) {
                this.setResponseTypesFromExerciseOptions();
            } else {
                this.setResponseTypesForHtml();
            }
        } else if (this.isCardGroupExercise()) {
            this.setPromptTypesFromExercise();
            this.setResponseTypesFromExercise();
        } else {
            this.setPromptTypesFromExerciseOptionsOrExercise();
            this.setResponseTypesFromExerciseOptions();
            if (this.responseTypes.length === 0) {
                this.setResponseTypesFromExercise();
            }
        }
    }

    private setPromptTypesFromExerciseOptionsOrExercise() {
        this.promptTypes = this.question.exerciseOptions.prompt_preview_option
            ? this.question.exerciseOptions.prompt_preview_option
                  .split(",")
                  .map((el: string) => el.trim())
            : [];
        if (this.promptTypes.length === 0) {
            this.promptTypes = this.exercise.promotetype.split(",").map((el: string) => el.trim());
        }
    }

    private setResponseTypesFromExerciseOptions() {
        if (!this.response.exerciseOptions?.response_preview_option) {
            return;
        }
        this.responseTypes = this.response.exerciseOptions.responce_preview_option
            ? this.response.exerciseOptions.responce_preview_option
                  .split(",")
                  .map((el: string) => el.trim())
            : [];
    }

    private setPromptTypesFromExercise() {
        if (this.exercise.promotetype) {
            this.promptTypes = this.exercise.promotetype.split(",").map((el: string) => el.trim());
        }
    }

    private setResponseTypesFromExercise() {
        if (this.exercise.responsetype) {
            this.responseTypes = this.exercise.responsetype
                .split(",")
                .map((el: string) => el.trim());
        }
    }

    private setPromptTypesForHtml() {
        if (
            this.question.prompt_audio_id !== null &&
            this.question.audio &&
            this.promptTypes.indexOf("a") < 0
        ) {
            this.promptTypes.push("a");
            this.question.FullAudioUrl = this.question.audio.FullUrl;
        }

        if (this.question.prompt_image_id !== null) {
            this.promptTypes.push("i");
        }
    }

    private setResponseTypesForHtml() {
        if (
            this.response.response_audio_id !== null &&
            this.response.audio &&
            this.responseTypes.indexOf("a") < 0
        ) {
            this.responseTypes.push("a");
            this.response.FullAudioUrl = this.response.audio.FullUrl;
        }

        if (this.response.response_image_id !== null) {
            this.responseTypes.push("i");
        }
    }

    private isCustomExercise() {
        return this.exercise.card_type == "custom";
    }

    private isCardGroupExercise() {
        return this.exercise.card_type == "card_group";
    }

    private isPromptTypeCard() {
        return (
            this.question.PromptType == "card" ||
            (!this.question.PromptType && this.exercise.card_type == "card")
        );
    }

    private isResponseTypeCard() {
        return (
            this.response.ResponseType == "card" ||
            (!this.response.ResponseType && this.exercise.card_type == "card")
        );
    }

    public getCardIdList() {
        if (this.exercise.card_type == "custom") {
            if (this.question.PromptType == "card") {
                if (this.cardIdArray.indexOf(this.question.id) == -1) {
                    this.cardIdArray.push(this.question.id);
                    this.customCardId = this.question.id;
                }
            }

            if (!!this.response && this.response.ResponseType == "card") {
                if (this.cardIdArray.indexOf(this.response.id) == -1) {
                    this.cardIdArray.push(this.response.id);
                }
            }
        } else {
            if (this.question.exerciseOptions.type == "card" || this.question.exerciseOptions.type == "group") {
                if (this.cardIdArray.indexOf(this.question.id) == -1) {
                    this.cardIdArray.push(this.question.id);
                }
            }

            if (!!this.response && ["card", "group"].indexOf(this.response.exerciseOptions.type) >= 0) {
                if (this.cardIdArray.indexOf(this.response.id) == -1) {
                    this.cardIdArray.push(this.response.id);
                }
            }
        }
    }

    setCorrectAnswer(choice) {
        this.ansId = choice.id;
        this.userAnswer = choice.id == this.response.id ? AnswerType.CORRECT : AnswerType.INCORRECT;
    }

    goToNext() {
        if (this.nextButtonShouldBeClickable) {
            this.hasError = false;
        }
        return true;
    }

    cleanUp() {
        this.answerGivenObj = {};
        this.nextButtonShouldBeClickable = false;
    }
}
