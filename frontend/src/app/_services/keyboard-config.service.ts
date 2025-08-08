import { Injectable } from "@angular/core";
import { HttpClient } from "@angular/common/http";
import { Observable, of, BehaviorSubject } from "rxjs";
import { catchError, map } from "rxjs/operators";
import { KeyboardLayoutObject } from "simple-keyboard";

export interface ButtonTheme {
    class: string;
    buttons: string;
}

export interface KeyboardSettings {
    languageSwitchKeys: {
        left: string;
        right: string;
    };
    enablePhysicalKeyboard: boolean;
    enableCharacterConversion: boolean;
    autoCapitalize: boolean;
}

export interface KeyboardConfig {
    defaultLayoutObject: KeyboardLayoutObject;
    alternateLayoutObject: KeyboardLayoutObject;
    buttonThemes: ButtonTheme[];
    keyCodesConversions: { [key: string]: string };
    charCombosTreatedAsOneChar: string[];
    characterReplacements?: { [key: string]: string };
    settings: KeyboardSettings;
    allWordCharsRegex: string;
}

@Injectable({
    providedIn: "root",
})
export class KeyboardConfigService {
    private config: KeyboardConfig | null = null;
    private configPath = "assets/keyboard/keyboard.json";
    private configLoadedSubject = new BehaviorSubject<boolean>(false);
    public configLoaded = this.configLoadedSubject.asObservable();

    constructor(private http: HttpClient) {}

    /**
     * Load keyboard configuration from JSON file
     * @returns Observable of KeyboardConfig
     */
    loadConfig(): Observable<KeyboardConfig> {
        if (this.config) {
            return of(this.config);
        }

        return this.http.get<KeyboardConfig>(this.configPath).pipe(
            map((config) => {
                this.config = this.validateAndMergeConfig(config);
                this.configLoadedSubject.next(true);
                console.log("loadConfig config:", JSON.stringify(this.config, null, "  "));
                return this.config;
            }),
            catchError((error) => {
                console.error("Failed to load keyboard config:", error);
                // Return default config if loading fails
                this.config = this.getDefaultConfig();
                this.configLoadedSubject.next(true);
                return of(this.config);
            }),
        );
    }

    /**
     * Get button themes for styling
     * @returns Array of ButtonTheme objects
     */
    getButtonThemes(): ButtonTheme[] {
        return this.config?.buttonThemes || [];
    }

    /**
     * Get key codes for physical keyboard mapping
     * @returns Array of key code strings
     */
    getKeyCodes(): string[] {
        return Object.keys(this.config?.keyCodesConversions || {});
    }

    /**
     * Get keyboard settings
     * @returns KeyboardSettings or null if not loaded
     */
    getSettings(): KeyboardSettings | null {
        return this.config?.settings || null;
    }

    /**
     * Get language switch keys
     * @returns Object with left and right language switch keys
     */
    getLanguageSwitchKeys(): { left: string; right: string } {
        return (
            this.config?.settings?.languageSwitchKeys || {
                left: "{metaleft}",
                right: "{metaright}",
            }
        );
    }

    /**
     * Check if physical keyboard is enabled
     * @returns boolean
     */
    isPhysicalKeyboardEnabled(): boolean {
        return this.config?.settings?.enablePhysicalKeyboard ?? true;
    }

    /**
     * Check if character conversion is enabled
     * @returns boolean
     */
    isCharacterConversionEnabled(): boolean {
        return this.config?.settings?.enableCharacterConversion ?? true;
    }

    /**
     * Check if auto capitalize is enabled
     * @returns boolean
     */
    isAutoCapitalizeEnabled(): boolean {
        return this.config?.settings?.autoCapitalize ?? false;
    }

    /**
     * Get character replacements from keyboard config
     * @returns Object with character replacements or empty object if not available
     */
    getCharacterReplacements(): { [key: string]: string } {
        return this.config?.characterReplacements || {};
    }

    /**
     * Get character combinations treated as one character
     * @returns Array of character combinations or empty array if not available
     */
    getCharCombosTreatedAsOneChar(): string[] {
        return this.config?.charCombosTreatedAsOneChar || [];
    }

    /**
     * Get key code conversions for physical keyboard mapping
     * @returns Object with key code conversions or empty object if not available
     */
    getKeyCodesConversions(): { [key: string]: string } {
        return this.config?.keyCodesConversions || {};
    }

    /**
     * Add a button theme
     * @param {ButtonTheme} theme - ButtonTheme object to add
     */
    addButtonTheme(theme: ButtonTheme): void {
        if (!this.config) {
            return;
        }
        this.config.buttonThemes.push(theme);
    }

    /**
     * Clear the cached configuration (useful for testing or reloading)
     */
    clearCache(): void {
        this.config = null;
        this.configLoadedSubject.next(false);
    }

    /**
     * Validate and merge configuration with defaults
     * @param {KeyboardConfig} config - Configuration to validate
     * @returns Validated and merged configuration
     */
    private validateAndMergeConfig(config: any): KeyboardConfig {
        const defaultConfig = this.getDefaultConfig();

        // Merge with defaults, ensuring all required properties exist
        const mergedConfig: KeyboardConfig = {
            defaultLayoutObject: {
                ...defaultConfig.defaultLayoutObject,
                ...config.defaultLayoutObject,
            },
            alternateLayoutObject: {
                ...defaultConfig.alternateLayoutObject,
                ...config.alternateLayoutObject,
            },
            buttonThemes: config.buttonThemes || defaultConfig.buttonThemes,
            keyCodesConversions: config.keyCodesConversions || defaultConfig.keyCodesConversions,
            charCombosTreatedAsOneChar:
                config.charCombosTreatedAsOneChar || defaultConfig.charCombosTreatedAsOneChar,
            settings: { ...defaultConfig.settings, ...config.settings },
            allWordCharsRegex: config.allWordCharsRegex || defaultConfig.allWordCharsRegex,
        };

        // Validate layouts. If the config is missing a layout, use the default layout.
        if (!mergedConfig.defaultLayoutObject.default) {
            mergedConfig.defaultLayoutObject.default = defaultConfig.defaultLayoutObject.default;
        }
        if (!mergedConfig.defaultLayoutObject.shift) {
            mergedConfig.defaultLayoutObject.shift = defaultConfig.defaultLayoutObject.shift;
        }
        if (!mergedConfig.alternateLayoutObject.default) {
            mergedConfig.alternateLayoutObject.default =
                defaultConfig.alternateLayoutObject.default;
        }
        if (!mergedConfig.alternateLayoutObject.shift) {
            mergedConfig.alternateLayoutObject.shift = defaultConfig.alternateLayoutObject.shift;
        }

        // Validate settings
        if (!mergedConfig.settings.languageSwitchKeys) {
            mergedConfig.settings.languageSwitchKeys = defaultConfig.settings.languageSwitchKeys;
        }
        return mergedConfig;
    }

    /**
     * Get default keyboard configuration
     * @returns Default KeyboardConfig
     */
    private getDefaultConfig(): KeyboardConfig {
        return {
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
                default: [],
                shift: [],
            },
            buttonThemes: [],
            keyCodesConversions: {},
            charCombosTreatedAsOneChar: [],
            characterReplacements: {},
            settings: {
                languageSwitchKeys: {
                    left: "{metaleft}",
                    right: "{metaright}",
                },
                enablePhysicalKeyboard: true,
                enableCharacterConversion: true,
                autoCapitalize: false,
            },
            allWordCharsRegex: "a-zA-Z,;-'\"",
        };
    }
}
