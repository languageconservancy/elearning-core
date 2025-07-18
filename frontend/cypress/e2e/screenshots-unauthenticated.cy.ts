/**
 * Takes screenshots of all the pages of the app that don't require logging in.
 */

describe('Desktop Unauthenticated Screenshots', () => {
    const captureType = "viewport";
    const screenshotsPath = "";
    const fileNameSuffix = "-desktop";
    before(() => {
        cy.viewport(Cypress.env("desktop_width_px"), Cypress.env("desktop_height_px"));
    });
    it('Screenshot login page', () => {
        screenshotLoginPage(captureType, screenshotsPath, fileNameSuffix);
    });
    it('Screenshot unauthenticated navbar dropdown', () => {
        screenshotLoginNavDropdown(captureType, screenshotsPath, fileNameSuffix);
    });
    it('Screenshot /about', () => {
        screenshotAbout(captureType, screenshotsPath, fileNameSuffix);
    });
    it('Screenshot /forgot-password', () => {
        screenshotForgotPassword(captureType, screenshotsPath, fileNameSuffix);
    });
    it('Screenshot /contact-us', () => {
        screenshotContactUs(captureType, screenshotsPath, fileNameSuffix);
    });
    it('Screenshot /about/privacy', () => {
        screenshotPrivacyPolicy(captureType, screenshotsPath, fileNameSuffix);
    });
    it('Screenshot /about/terms', () => {
        screenshotTerms(captureType, screenshotsPath, fileNameSuffix);
    });
})

describe('Mobile Portrait Unauthenticated Screenshots', () => {
    const captureType = "viewport";
    const screenshotsPath = "";
    const fileNameSuffix = "-mobile-portrait";
    beforeEach(() => {
        cy.viewport(Cypress.env('mobile_viewport'));
    });
    it('Screenshot login page', () => {
        screenshotLoginPage(captureType, screenshotsPath, fileNameSuffix);
    });
    it('Screenshot unauthenticated navbar dropdown', () => {
        screenshotLoginNavDropdown(captureType, screenshotsPath, fileNameSuffix);
    });
    it('Screenshot /about', () => {
        screenshotAbout(captureType, screenshotsPath, fileNameSuffix);
    });
    it('Screenshot /about dropdown', () => {
        screenshotAboutDropdown(captureType, screenshotsPath, fileNameSuffix);
    });
    it('Screenshot /forgot-password', () => {
        screenshotForgotPassword(captureType, screenshotsPath, fileNameSuffix);
    });
    it('Screenshot /contact-us', () => {
        screenshotContactUs(captureType, screenshotsPath, fileNameSuffix);
    });
    it('Screenshot /about/privacy', () => {
        screenshotPrivacyPolicy(captureType, screenshotsPath, fileNameSuffix);
    });
    it('Screenshot /about/terms', () => {
        screenshotTerms(captureType, screenshotsPath, fileNameSuffix);
    });
})

let screenshotLoginPage = (captureType: any, screenshotsPath: string, fileNameSuffix: string = "") => {
    cy.visit('/');
    cy.get('#google-signin-link').should('exist');
    cy.scrollTo('top', {ensureScrollable: false});
    cy.screenshot(`${screenshotsPath}/login${fileNameSuffix}`, {
        capture: captureType,
        overwrite: true
    });
}

let screenshotLoginNavDropdown = (captureType: any, screenshotsPath: string, fileNameSuffix: string = "") => {
    cy.visit('/');
    cy.get('#google-signin-link').should('exist');
    if (fileNameSuffix.includes("mobile")) {
        cy.get('.navbar-toggler').first().click();
    } else {
        cy.get('#navbardropdown').first().click();
    }
    cy.wait(1000);
    cy.scrollTo('top', {ensureScrollable: false});
    cy.screenshot(`${screenshotsPath}/navbar-dropdown${fileNameSuffix}`, {
        capture: captureType,
        overwrite: true
    });
}

let screenshotForgotPassword = (captureType: any, screenshotsPath: string, fileNameSuffix: string = "") => {
    cy.visit('/forgot-password');
    cy.get('#new-pwd-submit-btn').should('exist');
    cy.scrollTo('top', {ensureScrollable: false});
    cy.screenshot(`${screenshotsPath}/forgot-password${fileNameSuffix}`, {
        capture: captureType,
        overwrite: true
    });
}

let screenshotContactUs = (captureType: any, screenshotsPath: string, fileNameSuffix: string = "") => {
    cy.visit('/contact-us');
    cy.get('#contact-us-send-btn').should('exist');
    cy.scrollTo('top', {ensureScrollable: false});
    cy.screenshot(`${screenshotsPath}/contact-us${fileNameSuffix}`, {
        capture: captureType,
        overwrite: true
    });
}

let screenshotAbout = (captureType: any, screenshotsPath: string, fileNameSuffix: string = "") => {
    cy.visit('/about');
    cy.get('#myTabContent').should('exist');
    cy.scrollTo('top', {ensureScrollable: false});
    cy.screenshot(`${screenshotsPath}/about${fileNameSuffix}`, {
        capture: captureType,
        overwrite: true
    });
}

let screenshotAboutDropdown = (captureType: any, screenshotsPath: string, fileNameSuffix: string = "") => {
    cy.visit('/about');
    cy.get('#myTabContent').should('exist');
    cy.get('#about-dropdown').click();
    cy.wait(1000);
    cy.scrollTo('top', {ensureScrollable: false});
    cy.screenshot(`${screenshotsPath}/about_dropdown${fileNameSuffix}`, {
        capture: captureType,
        overwrite: true
    });
}

let screenshotPrivacyPolicy = (captureType: any, screenshotsPath: string, fileNameSuffix: string = "") => {
    cy.visit('/about/privacy');
    cy.get('#myTabContent').should('exist');
    cy.scrollTo('top', {ensureScrollable: false});
    cy.screenshot(`${screenshotsPath}/about_privacy${fileNameSuffix}`, {
        capture: captureType,
        overwrite: true
    });
}

let screenshotTerms = (captureType: any, screenshotsPath: string, fileNameSuffix: string = "") => {
    cy.visit('/about/terms');
    cy.get('#myTabContent').should('exist');
    cy.scrollTo('top', {ensureScrollable: false});
    cy.screenshot(`${screenshotsPath}/about_terms${fileNameSuffix}`, {
        capture: captureType,
        overwrite: true
    });
}
