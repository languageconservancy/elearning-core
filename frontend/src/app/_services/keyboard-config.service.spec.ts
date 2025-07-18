import { TestBed } from "@angular/core/testing";
import { HttpClientTestingModule, HttpTestingController } from "@angular/common/http/testing";
import {
  KeyboardConfigService,
  KeyboardLayout,
  ButtonTheme,
  KeyboardSettings,
  KeyboardConfig,
} from "./keyboard-config.service";

describe("KeyboardConfigService", () => {
  let service: KeyboardConfigService;
  let httpMock: HttpTestingController;

  const mockConfig: KeyboardConfig = {
    layouts: {
      default: {
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
      custom: {
        default: [
          "1 2 3 4 5 6 7 8 9 0 {backspace}",
          "q w e r t y u i o p",
          "a s d f g h j k l {enter}",
          "z x c v b n m {shiftright}",
          "{space}",
        ],
        shift: [
          "! @ # $ % ^ & * ( ) {backspace}",
          "Q W E R T Y U I O P",
          "A S D F G H J K L {enter}",
          "Z X C V B N M {shiftright}",
          "{space}",
        ],
      },
    },
    buttonThemes: [
      {
        class: "btn-primary",
        buttons: "a b c",
      },
    ],
    keyCodes: ["Digit1 Digit2 Digit3 Digit4 Digit5", "KeyQ KeyW KeyE KeyR KeyT"],
    settings: {
      defaultLayout: "default",
      languageSwitchKeys: {
        left: "{metaleft}",
        right: "{metaright}",
      },
      enablePhysicalKeyboard: true,
      enableCharacterConversion: true,
      autoCapitalize: false,
    },
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

  it("should load config from HTTP", (done) => {
    service.loadConfig().subscribe((config) => {
      expect(config).toEqual(mockConfig);
      done();
    });

    const req = httpMock.expectOne("assets/platform/config/keyboard.json");
    expect(req.request.method).toBe("GET");
    req.flush(mockConfig);
  });

  it("should return default config on HTTP error", (done) => {
    service.loadConfig().subscribe((config) => {
      expect(config).toBeTruthy();
      expect(config.layouts).toBeDefined();
      expect(config.settings).toBeDefined();
      done();
    });

    const req = httpMock.expectOne("assets/platform/config/keyboard.json");
    req.error(new ErrorEvent("Network error"));
  });

  it("should get layout by name", (done) => {
    service.loadConfig().subscribe(() => {
      const layout = service.getLayout("default");
      expect(layout).toBeTruthy();
      expect(layout?.default).toEqual(mockConfig.layouts.default.default);
      done();
    });

    const req = httpMock.expectOne("assets/platform/config/keyboard.json");
    req.flush(mockConfig);
  });

  it("should return null for non-existent layout", (done) => {
    service.loadConfig().subscribe(() => {
      const layout = service.getLayout("nonexistent");
      expect(layout).toBeNull();
      done();
    });

    const req = httpMock.expectOne("assets/platform/config/keyboard.json");
    req.flush(mockConfig);
  });

  it("should get all layout names", (done) => {
    service.loadConfig().subscribe(() => {
      const names = service.getLayoutNames();
      expect(names).toEqual(["default"]);
      done();
    });

    const req = httpMock.expectOne("assets/platform/config/keyboard.json");
    req.flush(mockConfig);
  });

  it("should get button themes", (done) => {
    service.loadConfig().subscribe(() => {
      const themes = service.getButtonThemes();
      expect(themes).toEqual(mockConfig.buttonThemes);
      done();
    });

    const req = httpMock.expectOne("assets/platform/config/keyboard.json");
    req.flush(mockConfig);
  });

  it("should get settings", (done) => {
    service.loadConfig().subscribe(() => {
      const settings = service.getSettings();
      expect(settings).toEqual(mockConfig.settings);
      done();
    });

    const req = httpMock.expectOne("assets/platform/config/keyboard.json");
    req.flush(mockConfig);
  });

  it("should get default layout name", (done) => {
    service.loadConfig().subscribe(() => {
      const defaultName = service.getDefaultLayoutName();
      expect(defaultName).toBe("default");
      done();
    });

    const req = httpMock.expectOne("assets/platform/config/keyboard.json");
    req.flush(mockConfig);
  });

  it("should add layout at runtime", (done) => {
    service.loadConfig().subscribe(() => {
      const newLayout: KeyboardLayout = {
        default: ["1 2 3", "a b c"],
        shift: ["! @ #", "A B C"],
      };

      const success = service.addLayout("newLayout", newLayout);
      expect(success).toBe(true);

      const layout = service.getLayout("newLayout");
      expect(layout).toEqual(newLayout);
      done();
    });

    const req = httpMock.expectOne("assets/platform/config/keyboard.json");
    req.flush(mockConfig);
  });

  it("should clear cache", (done) => {
    service.loadConfig().subscribe(() => {
      service.clearCache();

      // Should trigger new HTTP request
      service.loadConfig().subscribe();

      const req = httpMock.expectOne("assets/platform/config/keyboard.json");
      req.flush(mockConfig);
      done();
    });

    const req = httpMock.expectOne("assets/platform/config/keyboard.json");
    req.flush(mockConfig);
  });
});
