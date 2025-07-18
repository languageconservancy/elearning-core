import { CapacitorConfig } from "@capacitor/cli";
import { environment } from "./src/environments/environment";

const config: CapacitorConfig =
    environment.ENABLE_SOCIAL_LOGINS !== "true"
        ? {
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
                  GoogleAuth: {},
              },
              android: {
                  flavor: environment.ANDROID_FLAVOR_IOS_SCHEME,
                  includePlugins: ["@capacitor/app", "@capacitor/device", "@capacitor/preferences"],
              },
              ios: {
                  scheme: environment.ANDROID_FLAVOR_IOS_SCHEME,
                  includePlugins: ["@capacitor/app", "@capacitor/device", "@capacitor/preferences"],
              },
          }
        : {
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
                  GoogleAuth: {
                      scopes: ["profile", "email"],
                      iosClientId: environment.GOOGLE_CLIENT_ID_IOS,
                      androidClientId: environment.GOOGLE_CLIENT_ID_WEB,
                      clientId: environment.GOOGLE_CLIENT_ID_WEB,
                      forceCodeForRefreshToken: true,
                  },
              },
              android: {
                  flavor: environment.ANDROID_FLAVOR_IOS_SCHEME,
                  includePlugins: [
                      "@capacitor/app",
                      "@capacitor/device",
                      "@capacitor/preferences",
                      "@capacitor-community/apple-sign-in",
                      "@capacitor-community/facebook-login",
                      "@codetrix-studio/capacitor-google-auth",
                  ],
              },
              ios: {
                  scheme: environment.ANDROID_FLAVOR_IOS_SCHEME,
                  includePlugins: [
                      "@capacitor/app",
                      "@capacitor/device",
                      "@capacitor/preferences",
                      "@capacitor-community/apple-sign-in",
                      "@capacitor-community/facebook-login",
                      "@codetrix-studio/capacitor-google-auth",
                  ],
              },
          };

export default config;
