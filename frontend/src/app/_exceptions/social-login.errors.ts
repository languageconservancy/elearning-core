/**
 * Custom error types for social login failures
 * These allow components to handle different error types with specific user messages
 */

export enum SocialLoginErrorType {
    AUTH_CANCELLED = "AUTH_CANCELLED",
    NETWORK_ERROR = "NETWORK_ERROR",
    INVALID_TOKEN = "INVALID_TOKEN",
    NO_TOKEN = "NO_TOKEN",
    DECODE_ERROR = "DECODE_ERROR",
    CONFIGURATION_ERROR = "CONFIGURATION_ERROR",
    UNKNOWN_ERROR = "UNKNOWN_ERROR",
}

export class SocialLoginError extends Error {
    constructor(
        public readonly errorType: SocialLoginErrorType,
        public readonly provider: string,
        message: string,
        public readonly originalError?: any,
    ) {
        super(message);
        this.name = "SocialLoginError";

        // Maintains proper stack trace for where our error was thrown (only available on V8)
        if ((Error as any).captureStackTrace) {
            (Error as any).captureStackTrace(this, SocialLoginError);
        }
    }

    /**
     * Returns a user-friendly message based on the error type
     */
    getUserMessage(): string {
        switch (this.errorType) {
            case SocialLoginErrorType.AUTH_CANCELLED:
                return `Sign in with ${this.provider} was cancelled. Please try again.`;

            case SocialLoginErrorType.NETWORK_ERROR:
                return `Could not connect to ${this.provider}. Please check your internet connection and try again.`;

            case SocialLoginErrorType.INVALID_TOKEN:
            case SocialLoginErrorType.DECODE_ERROR:
                return `Invalid ${this.provider} credentials. Please try a different login method or contact support.`;

            case SocialLoginErrorType.NO_TOKEN:
                return `${this.provider} sign in was incomplete. Please try again.`;

            case SocialLoginErrorType.CONFIGURATION_ERROR:
                return `${this.provider} login is not properly configured. Please contact support.`;

            case SocialLoginErrorType.UNKNOWN_ERROR:
            default:
                return `Sign in with ${this.provider} failed. Please try again or use a different login method.`;
        }
    }
}
