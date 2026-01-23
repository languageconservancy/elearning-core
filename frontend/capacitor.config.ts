import { CapacitorConfig } from "@capacitor/cli";
import { environment } from "./src/environments/environment";

// Base plugins always included
const basePlugins = ["@capacitor/app", "@capacitor/device", "@capacitor/preferences"];

// Conditionally add social login plugins
const socialLoginPlugins: string[] = [];

if (environment.ENABLE_APPLE_LOGIN) {
    socialLoginPlugins.push("@capacitor-community/apple-sign-in");
}

if (environment.ENABLE_FACEBOOK_LOGIN) {
    socialLoginPlugins.push("@capacitor-community/facebook-login");
}

if (environment.ENABLE_GOOGLE_LOGIN || environment.ENABLE_APPLE_LOGIN || environment.ENABLE_FACEBOOK_LOGIN) {
    socialLoginPlugins.push("@capgo/capacitor-social-login");
}

const includePlugins = [...basePlugins, ...socialLoginPlugins];

const config: CapacitorConfig = {
    appId: environment.APP_ID,
    appName: environment.SITE_NAME,
    webDir: "dist",
    server: {
        androidScheme: "https",
    },
    plugins: {
        CapacitorCookies: {
            enabled: true,
        },
        CapacitorHttp: {
            enabled: true,
        },
    },
    android: {
        includePlugins,
    },
    ios: {
        includePlugins,
    },
};

export default config;
