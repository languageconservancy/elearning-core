/// <reference types="cypress" />
import { mount } from "cypress/angular";

declare global {
    namespace Cypress {
        interface Chainable<Subject> {
            /**
             * Custom command to mount an Angular component.
             * @example cy.mount(MyComponent)
             */
            mount: typeof mount;

            /**
             * Custom command to go through the login process in order to
             * access authenticated pages.
             */
            logInViaUi(): Chainable<void>;
            screenshotSignup(captureType: any, screenshotsPath: string, fileNameSuffix?: string): Chainable<void>;
            registerTestUser(captureType: any, screenshotsPath: string, fileNameSuffix?: string): Chainable<void>;
            screenshotLearningSpeedAndContinue(captureType: any, screenshotsPath: string, fileNameSuffix?: string): Chainable<void>;
            screenshotSpreadTheWordAndContinue(captureType: any, screenshotsPath: string, fileNameSuffix?: string): Chainable<void>;
            screenshotFindFriendsAndContinue(captureType: any, screenshotsPath: string, fileNameSuffix?: string): Chainable<void>;
            screenshotNewUserDashboard(captureType: any, screenshotsPath: string, fileNameSuffix?: string): Chainable<void>;
            signUp(captureType: any, screenshotsPath: string, fileNameSuffix?: string): Chainable<void>;
        }
    }
}

export {}; // Prevents global scope conflicts