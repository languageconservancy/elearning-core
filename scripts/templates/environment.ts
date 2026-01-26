// Auto-generated from core/templates/environment.ts + app-config.json
export const environment = {
    FACEBOOK_APP_VERSION: "v19.0",
    GOOGLE_CONTACT_SCOPE: "https://www.googleapis.com/auth/contacts.readonly",
    API: "{{apiUrl}}",
    ROOT: "{{webUrl}}",
    GOOGLE_CLIENT_ID_WEB: "{{googleClientIdWeb}}",
    GOOGLE_CLIENT_ID_IOS: "{{googleClientIdIos}}",
    GOOGLE_API_KEY: "{{googleApiKey}}",
    FACEBOOK_APP_ID: "{{facebookAppId}}",
    FACEBOOK_CLIENT_TOKEN: "{{facebookClientToken}}",
    CLEVER_ID: "{{cleverId}}",
    LOGIN_URI: "{{loginUri}}",
    SITE_NAME: "{{appName}}",
    LANGUAGE: "{{languageEnglish}}",
    LANGUAGE_NATIVE: "{{languageNative}}",
    SITE_OWNER: "{{appOwner}}",
    IOS_APP_ID_NUMBER: "{{iosAppIdNumber}}",
    APP_ID: "{{appId}}",
    {{#if enableAppleLogin}}
    ENABLE_APPLE_LOGIN: {{enableAppleLogin}}, // true or false
    {{else}}
    ENABLE_APPLE_LOGIN: false,
    {{/if}}
    {{#if enableGoogleLogin}}
    ENABLE_GOOGLE_LOGIN: {{enableGoogleLogin}}, // true or false,
    {{else}}
    ENABLE_GOOGLE_LOGIN: false,
    {{/if}}
    {{#if enableFacebookLogin}}
    ENABLE_FACEBOOK_LOGIN: {{enableFacebookLogin}}, // true or false
    {{else}}
    ENABLE_FACEBOOK_LOGIN: false,
    {{/if}}
    {{#if enableCleverLogin}}
    ENABLE_CLEVER_LOGIN: {{enableCleverLogin}}, // true or false
    {{else}}
    ENABLE_CLEVER_LOGIN: false,
    {{/if}}
    production: {{production}}, // true or false
};
