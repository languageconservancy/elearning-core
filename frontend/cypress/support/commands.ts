// ***********************************************
// This example namespace declaration will help
// with Intellisense and code completion in your
// IDE or Text Editor.
// ***********************************************

//
// NOTE: You can use it like so:
// Cypress.Commands.add('customCommand', customCommand);
//
// ***********************************************
// This example commands.ts shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************
//
//
import { mount } from "cypress/angular";

Cypress.Commands.add("mount", (component, config) => {
    return mount(component, config);
});

// -- This is a parent command --
Cypress.Commands.add("logInViaUi", () => {
    cy.session("login", () => {
        // Go to homepage and log in
        cy.intercept("POST", "http://localhost/backend/api/Users/login.json").as("login");
        cy.intercept("POST", "http://localhost/backend/api/Review/getReviewHaveOrNot.json").as("getReviewHaveOrNot");
        cy.visit("/");
        cy.get('input[name="email"]').type(Cypress.env("test_user_email"));
        cy.get('input[name="password"]').type(Cypress.env("test_user_password"));
        cy.get("#login-submit-btn").click();
        cy.url().should("contain", "dashboard");
        cy.wait(["@login", "@getReviewHaveOrNot"], { responseTimeout: 15000 });
    });
});

Cypress.Commands.add("screenshotSignup", (captureType: any, screenshotsPath: string, fileNameSuffix: string = "") => {
    // Go to sign-up page
    cy.visit("/register");
    cy.get('button[type="submit"]').should("exist");
    cy.url().should("contain", "register");
    // Take a screenshot
    cy.screenshot(`${screenshotsPath}/sign-up-01${fileNameSuffix}`, {
        capture: captureType,
        overwrite: true,
    });
});

Cypress.Commands.add("registerTestUser", (captureType: any, screenshotsPath: string, fileNameSuffix: string = "") => {
    // Fill in sign-up form and click submit button
    cy.get('input[name="name"]').type(Cypress.env("new_user_name"));
    cy.get('select[name="month"]').select(1);
    cy.get('select[name="day"]').select(1);
    cy.get('select[name="year"]').select("1991");
    cy.get('input[name="email"]').first().type(getNewUserEmail());
    cy.get('input[name="password"]').type(Cypress.env("new_user_password"));
    cy.get('input[name="confirmpassword"]').type(Cypress.env("new_user_password"));
    cy.get('button[type="submit"]').click();
    cy.wait(3000);
});

Cypress.Commands.add("screenshotLearningSpeedAndContinue",
    (captureType: any, screenshotsPath: string, fileNameSuffix: string = "") => {
        cy.url().should("contain", "learning-speed");
        cy.get("h2").contains("Learning Speed").should("exist");
        cy.get("#radioStacked1").should("exist");
        cy.scrollTo("top", { ensureScrollable: false });
        cy.screenshot(`${screenshotsPath}/sign-up-02_learning-speed${fileNameSuffix}`, {
            capture: captureType,
            overwrite: true,
        });
        // Check learning speed radio button and click next
        cy.get("#radioStacked1").check();
        cy.get('button[type="submit"]').click();
    },
);

Cypress.Commands.add("screenshotSpreadTheWordAndContinue",
    (captureType: any, screenshotsPath: string, fileNameSuffix: string = "") => {
        // Should be at spread-the-word page
        cy.url().should("contain", "spread-the-word");
        cy.get("h5").first().should("exist");
        cy.scrollTo("top", { ensureScrollable: false });
        cy.screenshot(`${screenshotsPath}/sign-up-03_spread-the-word${fileNameSuffix}`, {
            capture: captureType,
            overwrite: true,
        });
        cy.get('button[type="submit"]').click();
    },
);

Cypress.Commands.add("screenshotFindFriendsAndContinue",
    (captureType: any, screenshotsPath: string, fileNameSuffix: string = "") => {
        cy.url().should("contain", "find-friends");
        cy.get("i.fa-plus").should("exist");
        cy.scrollTo("top", { ensureScrollable: false });
        cy.screenshot(`${screenshotsPath}/sign-up-04_find-friends${fileNameSuffix}`, {
            capture: captureType,
            overwrite: true,
        });
        cy.get('button[type="submit"]').click();
    },
);

Cypress.Commands.add("screenshotNewUserDashboard",
    (captureType: any, screenshotsPath: string, fileNameSuffix: string = "") => {
        cy.url().should("contain", "dashboard");
        cy.get("#learn-img").should("be.visible");
        cy.wait(3000);
        cy.scrollTo("top", { ensureScrollable: false });
        cy.screenshot(`${screenshotsPath}/sign-up-05_dashboard${fileNameSuffix}`, {
            capture: captureType,
            overwrite: true,
        });
    },
);

Cypress.Commands.add("signUp", (captureType: any, screenshotsPath: string, fileNameSuffix: string = "") => {
    cy.screenshotSignup(captureType, screenshotsPath, fileNameSuffix);
    cy.registerTestUser(captureType, screenshotsPath, fileNameSuffix);
    cy.screenshotLearningSpeedAndContinue(captureType, screenshotsPath, fileNameSuffix);
    cy.screenshotSpreadTheWordAndContinue(captureType, screenshotsPath, fileNameSuffix);
    cy.screenshotFindFriendsAndContinue(captureType, screenshotsPath, fileNameSuffix);
    cy.screenshotNewUserDashboard(captureType, screenshotsPath, fileNameSuffix);
});

let getNewUserEmail = (): string => {
    return Math.floor(Math.random() * 10000) + Cypress.env("new_user_email");
};

//
//
// -- This is a child command --
// Cypress.Commands.add("drag", { prevSubject: 'element'}, (subject, options) => { ... })
//
//
// -- This is a dual command --
// Cypress.Commands.add("dismiss", { prevSubject: 'optional'}, (subject, options) => { ... })
//
//
// -- This will overwrite an existing command --
// Cypress.Commands.overwrite("visit", (originalFn, url, options) => { ... })
