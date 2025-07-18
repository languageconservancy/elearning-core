export const Settings = {
    AVANCE_TO_NEXT_EXERCISE_DELAY_MS: 100,
    DISPLAY_ERROR_TIME_MS: 2000,
    CORRECT_ANSWER_POPUP_TIME_BEFORE_DISAPPEARING_MS: 1500,
    DELAY_BEFORE_SHOWING_POPUP_MS: 100,
    REQUIRED_NUM_INCORRECT_ATTEMPTS_TO_COMPLETE_QUESTION: 3,
    RETURN_KEY_THROTTLE_DELAY_MS: 350,
};

export const Animation = {
    EXERCISE_POPUP_FADE_IN_TIME_MS: 1500,
    EXERCISE_POPUP_FADE_OUT_TIME_MS: 100,
};

export const TRANSLATION_FILE_PATH = "assets/translations/translations.json";

export const RegexConsts = {
    /**
     * This email regex taken from https://www.abstractapi.com/tools/email-regex-guide
     * with addition of apostrophe and changing the *$ at the end to +$, in order
     * to require a domain and top-level-domain (TLD).
     * Format: label@domain.tld
     * Also, this needs to be surrounded by backslashes, instead of quotes
     * or else it doesn't work correctly.
     * More rules taken from https://emailregex.com/email-validation-summary/
     * Local part:
     *   Start with any of these characters: a-zA-Z0-9.!#$%&'’*+\/=?^_`{|}~
     *   Followed by any of those plus hyphen character (without repeating)
     *   Ending with any of first set of characters (label can't end in a hyphen)
     *   Total length of local part not exceeding 64 characters
     * Domain labels:
     *   Can't start or end with hyphen
     *   Alphanumeric, max 64 characters
     * Domain Top-level-domain:
     *   Alphabetic, max 64 characters
     */
    EMAIL_REGEX: /^[a-zA-Z0-9.!#$%&'’*+\/=?^_`{|}~-]{1,64}@([a-zA-Z0-9-]{1,63}\.)+[a-zA-Z]{1,63}$/,
    /**
     * Age regex to validate age input.
     * Allows 1-149, no leading 0s.
     * The part before the | is for ages 100-149, the part after is for ages 1-99.
     */
    AGE_REGEX: /^(1[0-4][0-9]|[1-9][0-9]?)$/,
    /**
     * Split at spaces, excluding spaces within brackets.
     */
    SPLIT_AT_SPACES_REGEX: / (?![^\[]*\])/g,
    /**
     * Split at brackets, and include brackets.
     */
    SPLIT_AT_BRACKETS: /(\[[^\]]*\])|([^\[\]\s]+)/g,
};

export const MIN_SELF_CONSENT_AGE_DEFAULT = 13;
export const MIN_ADULT_AGE_DEFAULT = 18;
