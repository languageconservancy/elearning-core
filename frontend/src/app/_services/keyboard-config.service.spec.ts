import { TestBed } from "@angular/core/testing";
import { HttpClientTestingModule, HttpTestingController } from "@angular/common/http/testing";
import { KeyboardConfigService, KeyboardConfig } from "./keyboard-config.service";

fdescribe("KeyboardConfigService", () => {
    let service: KeyboardConfigService;
    let httpMock: HttpTestingController;

    const mockConfig: KeyboardConfig = {
        defaultLayoutObject: {
            default: [
                "` 1 2 3 4 5 6 7 8 9 0 - = {backspace}",
                "{tab} q w e r t y u i o p [ ] \\",
                "{capslock} a s d f g h j k l ; ' {enter}",
                "{shiftleft} z x c v b n m , . / {shiftright}",
                "{metaleft} {space} {metaright}",
            ],
            shift: [
                "~ ! @ # $ % ^ & * ( ) _ + {backspace}",
                "{tab} Q W E R T Y U I O P { } |",
                '{capslock} A S D F G H J K L : " {enter}',
                "{shiftleft} Z X C V B N M &lt; &gt; ? {shiftright}",
                "{metaleft} {space} {metaright}",
            ],
        },
        alternateLayoutObject: {
            default: [
                "` 1 2 3 4 5 6 7 8 9 0 - = {backspace}",
                "{tab} q w e r t y u i o p [ ] \\",
                "{capslock} a s d f g h j k l ; ' {enter}",
                "{shiftleft} z x c v b n m , . / {shiftright}",
                "{metaleft} {space} {metaright}",
            ],
            shift: [
                "~ ! @ # $ % ^ & * ( ) _ + {backspace}",
                "{tab} Q W E R T Y U I O P { } |",
                '{capslock} A S D F G H J K L : " {enter}',
                "{shiftleft} Z X C V B N M &lt; &gt; ? {shiftright}",
                "{metaleft} {space} {metaright}",
            ],
        },
        buttonThemes: [
            {
                class: "btn-primary",
                buttons: "a b",
            },
        ],
        keyCodesConversions: {
            "Digit1 Digit2 Digit3 Digit4 Digit5": "KeyQ KeyW KeyE KeyR KeyT",
        },
        charCombosTreatedAsOneChar: [],
        settings: {
            languageSwitchKeys: {
                left: "{metaleft}",
                right: "{metaright}",
            },
            enablePhysicalKeyboard: true,
            enableCharacterConversion: true,
            autoCapitalize: false,
        },
        characterReplacements: {
            "\u0060": "\u0027",
            "\u02bc": "\u0027",
            "\u2019": "\u0027",
            "\u0063\u030c": "\u010d",
            "\u0067\u030c": "\u01e7",
            "\u0068\u030c": "\u021f",
            "\u0073\u030c": "\u0161",
            "\u007a\u030c": "\u017e",
            "\u0048\u030c": "\u021e",
        },
        allWordCharsRegex: "a-zA-Z,;-'\"",
    };

    beforeEach(() => {
        TestBed.configureTestingModule({
            imports: [HttpClientTestingModule],
            providers: [KeyboardConfigService],
        });
        service = TestBed.inject(KeyboardConfigService);
        httpMock = TestBed.inject(HttpTestingController);
    });

    afterEach(() => {
        httpMock.verify();
        service.clearCache();
    });

    it("should be created", () => {
        expect(service).toBeTruthy();
    });

    fit("should load config from HTTP", (done) => {
        service.loadConfig().subscribe((config) => {
            // ensure config has expected keys
            expect(config.defaultLayoutObject).toBeDefined();
            expect(config.alternateLayoutObject).toBeDefined();
            expect(config.buttonThemes).toBeDefined();
            expect(config.keyCodesConversions).toBeDefined();
            expect(config.charCombosTreatedAsOneChar).toBeDefined();
            expect(config.characterReplacements).toBeDefined();
            expect(config.settings).toBeDefined();
            expect(config.allWordCharsRegex).toBeDefined();

            done();
        });

        const req = httpMock.expectOne("assets/keyboard/keyboard.json");
        expect(req.request.method).toEqual("GET");
        req.flush(mockConfig);
    });

    it("should return default config on HTTP error", (done) => {
        service.loadConfig().subscribe((config) => {
            expect(config).toBeTruthy();
            expect(config.settings).toBeTruthy();
            done();
        });

        const req = httpMock.expectOne("assets/keyboard/keyboard.json");
        req.error(new ErrorEvent("Network error"));
    });

    it("should get button themes", (done) => {
        service.loadConfig().subscribe(() => {
            const themes = service.getButtonThemes();
            expect(themes).toEqual(mockConfig.buttonThemes);
            done();
        });

        const req = httpMock.expectOne("assets/keyboard/keyboard.json");
        req.flush(mockConfig);
    });

    it("should get settings", (done) => {
        service.loadConfig().subscribe(() => {
            const settings = service.getSettings();
            expect(settings).toEqual(mockConfig.settings);
            done();
        });

        const req = httpMock.expectOne("assets/keyboard/keyboard.json");
        req.flush(mockConfig);
    });

    it("should clear cache", (done) => {
        service.loadConfig().subscribe(() => {
            service.clearCache();

            // Should trigger new HTTP request
            service.loadConfig().subscribe();

            const req = httpMock.expectOne("assets/keyboard/keyboard.json");
            req.flush(mockConfig);
            done();
        });

        const req = httpMock.expectOne("assets/keyboard/keyboard.json");
        req.flush(mockConfig);
    });
});
