import {
    AfterViewInit,
    Component,
    ElementRef,
    EventEmitter,
    Input,
    NgZone,
    OnDestroy,
    Output,
    ViewChild,
} from "@angular/core";

interface GoogleRecaptchaApi {
    render(
        container: HTMLElement,
        parameters: {
            sitekey: string;
            callback: (token: string) => void;
            "expired-callback": () => void;
            "error-callback": () => void;
        },
    ): number;
    reset(widgetId?: number): void;
}

interface WindowWithRecaptcha extends Window {
    grecaptcha?: GoogleRecaptchaApi;
}

const RECAPTCHA_SCRIPT_ID = "google-recaptcha-v2-api";
let recaptchaApiPromise: Promise<GoogleRecaptchaApi> | undefined;

function loadRecaptchaApi(): Promise<GoogleRecaptchaApi> {
    const loadedApi = (window as WindowWithRecaptcha).grecaptcha;
    if (loadedApi) {
        return Promise.resolve(loadedApi);
    }

    if (recaptchaApiPromise === undefined) {
        recaptchaApiPromise = new Promise<GoogleRecaptchaApi>((resolve, reject) => {
            let script = document.getElementById(RECAPTCHA_SCRIPT_ID) as HTMLScriptElement | null;
            if (!script) {
                script = document.createElement("script");
                script.id = RECAPTCHA_SCRIPT_ID;
                script.src = "https://www.google.com/recaptcha/api.js?render=explicit";
                script.async = true;
                script.defer = true;
            }

            script.addEventListener(
                "load",
                () => {
                    const api = (window as WindowWithRecaptcha).grecaptcha;
                    if (api) {
                        resolve(api);
                    } else {
                        reject(new Error("reCAPTCHA API did not initialize."));
                    }
                },
                { once: true },
            );
            script.addEventListener(
                "error",
                () => {
                    script?.remove();
                    reject(new Error("Could not load the reCAPTCHA API."));
                },
                { once: true },
            );

            if (!document.head.contains(script)) {
                document.head.appendChild(script);
            }
        }).catch((error: unknown) => {
            recaptchaApiPromise = undefined;
            throw error;
        });
    }

    return recaptchaApiPromise;
}

@Component({
    selector: "app-recaptcha-checkbox",
    templateUrl: "./recaptcha-checkbox.component.html",
    standalone: false,
})
export class RecaptchaCheckboxComponent implements AfterViewInit, OnDestroy {
    @Input() siteKey: string;
    @Output() resolved = new EventEmitter<string | null>();
    @ViewChild("captchaHost", { static: true })
    private captchaHost: ElementRef<HTMLDivElement>;

    public loadFailed = false;
    private recaptchaApi: GoogleRecaptchaApi;
    private widgetId: number;
    private destroyed = false;

    constructor(private zone: NgZone) {}

    ngAfterViewInit(): void {
        void this.renderWidget();
    }

    ngOnDestroy(): void {
        this.destroyed = true;
    }

    public retry(): void {
        this.loadFailed = false;
        if (this.recaptchaApi && this.widgetId !== undefined) {
            this.recaptchaApi.reset(this.widgetId);
        } else {
            void this.renderWidget();
        }
    }

    public reset(): void {
        if (this.recaptchaApi && this.widgetId !== undefined) {
            this.recaptchaApi.reset(this.widgetId);
        }
    }

    private async renderWidget(): Promise<void> {
        try {
            const api = await loadRecaptchaApi();
            if (this.destroyed) {
                return;
            }

            this.recaptchaApi = api;
            this.widgetId = api.render(this.captchaHost.nativeElement, {
                sitekey: this.siteKey,
                callback: (token) => this.emitResponse(token),
                "expired-callback": () => this.emitResponse(null),
                "error-callback": () => {
                    this.zone.run(() => {
                        this.loadFailed = true;
                        this.resolved.emit(null);
                    });
                },
            });
        } catch {
            if (!this.destroyed) {
                this.zone.run(() => (this.loadFailed = true));
            }
        }
    }

    private emitResponse(token: string | null): void {
        if (this.destroyed) {
            return;
        }

        this.zone.run(() => {
            this.loadFailed = false;
            this.resolved.emit(token);
        });
    }
}
