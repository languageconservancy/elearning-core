import { KeyboardConfigService } from "../../_services/keyboard-config.service";

export class OwoksapeUtils {
    private static keyboardConfigService: KeyboardConfigService;

    public static setKeyboardConfigService(service: KeyboardConfigService) {
        OwoksapeUtils.keyboardConfigService = service;
    }

    public static incrementWrap(value: number, min: number, max: number) {
        return value >= max ? min : value + 1;
    }

    public static decrementWrap(value: number, min: number, max: number) {
        return value <= min ? max : value - 1;
    }

    public static convertNonTargetLanguageKeyboardToTargetKeyboard(event: KeyboardEvent) {
        // Use keyboard config if available, otherwise return empty string
        if (this.keyboardConfigService) {
            const conversions = this.keyboardConfigService.getKeyCodesConversions();
            if (Object.keys(conversions).length > 0) {
                return conversions[event.code];
            }
        }

        return "";
    }

    public static lowerFirstLetter(text) {
        return text.charAt(0).toLowerCase() + text.slice(1);
    }

    public static replaceNonStandardChars(text) {
        // Use keyboard config if available, otherwise return text
        if (this.keyboardConfigService) {
            const replacements = this.keyboardConfigService.getCharacterReplacements();
            if (Object.keys(replacements).length > 0) {
                let newText = text;
                for (const key in replacements) {
                    newText = newText.replace(new RegExp(key, "g"), replacements[key]);
                }
                return newText;
            }
        }

        return text;
    }

    public static convertTextToCombineCharsArray(text) {
        let jumbledArray = [];

        if (this.keyboardConfigService) {
            const treatedAsOneChar = this.keyboardConfigService.getCharCombosTreatedAsOneChar();
            if (treatedAsOneChar.length > 0) {
                // Loop through characters in text
                for (let i = 0; i < text.length; ++i) {
                    let matched = false;
                    if (text.length - i >= 3) {
                        // Check for 3-character matches
                        const nextThreeChars = text.substring(i, i + 3);
                        if (treatedAsOneChar.indexOf(nextThreeChars) >= 0) {
                            jumbledArray.push(nextThreeChars);
                            i += 2;
                            matched = true;
                        }
                        if (!matched) {
                            // Check for 2-character matches
                            const nextTwoChars = text.substring(i, i + 2);
                            if (treatedAsOneChar.indexOf(nextTwoChars) >= 0) {
                                jumbledArray.push(nextTwoChars);
                                i += 1;
                                matched = true;
                            }
                        }
                    } else if (text.length - i == 2) {
                        // Check for 2-character matches
                        const nextTwoChars = text.substring(i, i + 2);
                        if (treatedAsOneChar.indexOf(nextTwoChars) >= 0) {
                            jumbledArray.push(nextTwoChars);
                            i += 1;
                            matched = true;
                        }
                    }
                    if (!matched) {
                        jumbledArray.push(text[i]);
                    }
                }
                return jumbledArray;
            }
        }

        if (jumbledArray.length == 0) {
            jumbledArray = text.split("");
        }

        return jumbledArray;
    }

    public static numCharsInPotentialLigatureReversed(text) {
        // Use keyboard config if available, otherwise return 1
        if (this.keyboardConfigService) {
            const treatedAsOneChar = this.keyboardConfigService.getCharCombosTreatedAsOneChar();
            if (treatedAsOneChar.length > 0) {
                // Check if last three characters are a trigraph
                if (text.length >= 3) {
                    // Check for 3-character matches
                    const nextThreeChars = text.substring(text.length - 3, text.length);
                    if (treatedAsOneChar.indexOf(nextThreeChars) >= 0) {
                        return 3;
                    }
                }
                if (text.length >= 2) {
                    // Check if last two characters are a digraph
                    const nextTwoChars = text.substring(text.length - 2, text.length);
                    if (treatedAsOneChar.indexOf(nextTwoChars) >= 0) {
                        return 2;
                    }
                }
                return 1;
            }
        }

        return 1;
    }

    public static subscriptionClosed(subscription) {
        return !subscription || subscription.closed;
    }

    public static stripHtml(input: string) {
        if (!!input) {
            return input.replace(/(<([^>]+)>)/gi, "");
        } else {
            return input;
        }
    }

    public static async hashUserAgent(): Promise<string> {
        const encoder = new TextEncoder();
        const data = encoder.encode(window.navigator.userAgent);

        const hashBuffer = await crypto.subtle.digest("SHA-256", data);
        const hashArray = Array.from(new Uint8Array(hashBuffer));
        const hashHex = hashArray.map((byte) => byte.toString(16).padStart(2, "0")).join("");

        return hashHex;
    }
}
