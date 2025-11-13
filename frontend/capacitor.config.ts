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
              },
              android: {
                  includePlugins: ["@capacitor/app", "@capacitor/device", "@capacitor/preferences"],
              },
              ios: {
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
              },
              android: {
                  includePlugins: [
                      "@capacitor/app",
                      "@capacitor/device",
                      "@capacitor/preferences",
                      "@capacitor-community/apple-sign-in",
                      "@capacitor-community/facebook-login",
                      "@capgo/capacitor-social-login",
                  ],
              },
              ios: {
                  includePlugins: [
                      "@capacitor/app",
                      "@capacitor/device",
                      "@capacitor/preferences",
                      "@capacitor-community/apple-sign-in",
                      "@capacitor-community/facebook-login",
                      "@capgo/capacitor-social-login",
                  ],
              },
          };

export default config;
