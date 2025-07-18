/**
 * Log in and take screenshots of all pages that require login.
 */

import "../support/commands";

beforeEach(() => {
    cy.logInViaUi();
});

describe('Desktop Authenticated Screenshots', () => {
    const captureType = "viewport";
    const screenshotsPath = "";
    const fileNameSuffix = "-desktop"
    before(() => {
        cy.viewport(Cypress.env("desktop_width_px"), Cypress.env("desktop_height_px"));
    });
    it('Screenshot /dashboard', () => {
        screenshotDashboard(captureType, screenshotsPath, fileNameSuffix);
    });
    it('Screenshot /start-learning', () => {
        screenshotLearning(captureType, screenshotsPath, fileNameSuffix);
    });
    it('Screenshot /classroom', () => {
        screenshotClassroom(captureType, screenshotsPath, fileNameSuffix);
    });
    it('Screenshot /leader-board', () => {
        screenshotLeaderBoard(captureType, screenshotsPath, fileNameSuffix);
    });
    it('Screenshot /progress', () => {
        screenshotProgress(captureType, screenshotsPath, fileNameSuffix);
    });
    it('Screenshot /village', () => {
        screenshotVillage(captureType, screenshotsPath, fileNameSuffix);
    });
    it('Screenshot /forum-post-details', () => {
        screenshotForumPostDetails(captureType, screenshotsPath, fileNameSuffix);
    });
})

describe('Mobile Authenticated Screenshots', () => {
    const captureType = "viewport";
    const screenshotsPath = "";
    const fileNameSuffix = "-mobile-portrait";
    beforeEach(() => {
        cy.viewport(Cypress.env('mobile_viewport'));
    });
    it('Screenshot /dashboard', () => {
        screenshotDashboard(captureType, screenshotsPath, fileNameSuffix);
    });
    it('Screenshot /start-learning', () => {
        screenshotLearning(captureType, screenshotsPath, fileNameSuffix);
    });
    it('Screenshot /classroom', () => {
        screenshotClassroom(captureType, screenshotsPath, fileNameSuffix);
    });
    it('Screenshot /leader-board', () => {
        screenshotLeaderBoard(captureType, screenshotsPath, fileNameSuffix);
    });
    it('Screenshot /progress', () => {
        screenshotProgress(captureType, screenshotsPath, fileNameSuffix);
    });
    it('Screenshot /village', () => {
        screenshotVillage(captureType, screenshotsPath, fileNameSuffix);
    });
    it('Screenshot /forum-post-details', () => {
        screenshotForumPostDetails(captureType, screenshotsPath, fileNameSuffix);
    });
})

let screenshotDashboard = (captureType: any, screenshotsPath: string, fileNameSuffix: string = "") => {
    cy.visit('/dashboard');
    cy.get("#learn-btn").should('exist');
    cy.get("#learn-img").should('exist');
    cy.wait(5000);
    cy.scrollTo('top', {ensureScrollable: false});
    cy.screenshot(`${screenshotsPath}/dashboard${fileNameSuffix}`, {
        capture: captureType,
        overwrite: true
    });
}

let screenshotLearning = (captureType: any, screenshotsPath: string, fileNameSuffix: string = "") => {
    cy.visit('/start-learning');
    cy.get("#continue-btn").should('exist');
    cy.get("#unit-0-name").should('exist');
    cy.scrollTo('top', {ensureScrollable: false});
    cy.screenshot(`${screenshotsPath}/levels${fileNameSuffix}`, {
        capture: captureType,
        overwrite: true
    });
}

let screenshotClassroom = (captureType: any, screenshotsPath: string, fileNameSuffix: string = "") => {
    cy.visit('/classroom');
    cy.get("#continue-btn").should('exist');
    cy.get("#unit-0-name").should('exist');
    cy.scrollTo('top', {ensureScrollable: false});
    cy.screenshot(`${screenshotsPath}/classroom${fileNameSuffix}`, {
        capture: captureType,
        overwrite: true
    });
}

let screenshotLeaderBoard = (captureType: any, screenshotsPath: string, fileNameSuffix: string = "") => {
    cy.visit('/leader-board');
    cy.get(".learner-img").should('exist');
    cy.scrollTo('top', {ensureScrollable: false});
    cy.screenshot(`${screenshotsPath}/leader-board${fileNameSuffix}`, {
        capture: captureType,
        overwrite: true
    });
}

let screenshotProgress = (captureType: any, screenshotsPath: string, fileNameSuffix: string = "") => {
    cy.visit('/progress');
    cy.get(".summary-img-box").should('exist');
    cy.scrollTo('top', {ensureScrollable: false});
    cy.screenshot(`${screenshotsPath}/progress${fileNameSuffix}`, {
        capture: captureType,
        overwrite: true
    });
}

let screenshotVillage = (captureType: any, screenshotsPath: string, fileNameSuffix: string = "") => {
    cy.visit('/village');
    cy.get(".forum-content").should('exist');
    cy.scrollTo('top');
    cy.scrollTo('top', {ensureScrollable: false});
    cy.screenshot(`${screenshotsPath}/village${fileNameSuffix}`, {
        capture: captureType,
        overwrite: true
    });
    // Dropdown menu expanded (Mobile only)
    if (screenshotsPath.includes('mobile')) {
        cy.get('button.dropdown-toggle').click();
        cy.wait(2000);
        cy.scrollTo('top', {ensureScrollable: false});
        cy.screenshot(`${screenshotsPath}/village_dropdown-menu${fileNameSuffix}`, {
            capture: captureType,
            overwrite: true
        });
    }
    // New post modal
    cy.get('button').contains('New Topic').click();
    cy.get('button[type="submit"]').should('be.visible');
    cy.scrollTo('top', {ensureScrollable: false});
    cy.screenshot(`${screenshotsPath}/village_new-post${fileNameSuffix}`, {
        capture: captureType,
        overwrite: true
    });
    // New post modal with keyboard
    cy.get('span.slider').click();
    cy.get('#input_id').click();
    cy.wait(500);
    cy.scrollTo('top', {ensureScrollable: false});
    cy.screenshot(`${screenshotsPath}/village_new-post_keyboard${fileNameSuffix}`, {
        capture: captureType,
        overwrite: true
    });
    cy.get('span.slider').click();
    // Close modal
    cy.get('button').contains('Cancel').click();
    // Friend sidebar
    cy.get("#drawer-opener").should('exist');
    cy.get("#drawer-opener").click();
    cy.wait(1000);
    cy.scrollTo('top', {ensureScrollable: false});
    cy.screenshot(`${screenshotsPath}/village_friend-sidebar${fileNameSuffix}`, {
        capture: captureType,
        overwrite: true
    });
}

let screenshotForumPostDetails = (captureType: any, screenshotsPath: string, fileNameSuffix: string = "") => {
    // Go to village and click on a post
    cy.visit('/village');
    cy.get(".forum-content").should('exist');
    cy.get(".forum-content > h4").first().should('exist');
    cy.get(".forum-content > h4").first().click();
    cy.url().should('contain', 'forum-post-details');
    cy.wait(1000);
    cy.get(".forum-profile-logos").first().should('exist');
    cy.wait(1000);
    cy.scrollTo('top', {ensureScrollable: false});
    cy.screenshot(`${screenshotsPath}/village_forum-post-details${fileNameSuffix}`, {
        capture: captureType,
        overwrite: true
    });
    // Reply modal without keyboard
    cy.get('button').contains('Reply').click();
    cy.get('#replyPost').should('be.visible');
    cy.screenshot(`${screenshotsPath}/village_forum-post-reply${fileNameSuffix}`, {
        capture: captureType,
        overwrite: true
    });
    // Reply modal with keyboard
    cy.get('#reply-toggle').click();
    cy.get('#reptextarea_id').click();
    cy.wait(1000);
    cy.scrollTo('top', {ensureScrollable: false});
    cy.screenshot(`${screenshotsPath}/village_forum-post-reply_keyboard${fileNameSuffix}`, {
        capture: captureType,
        overwrite: true
    });
}

let screenshotAddFriends = (captureType: any, screenshotsPath: string, fileNameSuffix: string = "") => {
    cy.visit('/village');
    cy.get(".forum-content").should('exist');
    cy.get("drawer-opener").should('exist');
    cy.get("drawer-opener").click();
    cy.wait(1000);
    cy.get("i.fa-plus-square").click();
    cy.url().should('contain', 'add-friends');
    cy.get("a").contains("Add Friend");
    cy.scrollTo('top', {ensureScrollable: false});
    cy.screenshot(`${screenshotsPath}/add-friends${fileNameSuffix}`, {
        capture: captureType,
        overwrite: true
    });
}

let screenshotSettings = (captureType: any, screenshotsPath: string, fileNameSuffix: string = "") => {
    // Learning settings
    cy.visit('/learning-settings');
    cy.get("div").contains("Learning Path").should('exist');
    cy.scrollTo('top', {ensureScrollable: false});
    cy.screenshot(`${screenshotsPath}/settings_learning${fileNameSuffix}`, {
        capture: captureType,
        overwrite: true
    });
    // Dropdown menu
    cy.get("button.dropdown-toggle").click();
    cy.wait(1000);
    cy.scrollTo('top', {ensureScrollable: false});
    cy.screenshot(`${screenshotsPath}/settings_dropdown${fileNameSuffix}`, {
        capture: captureType,
        overwrite: true
    });
    // Profile settings
    cy.visit('/profile-settings');
    cy.get("input#profile_image").should('exist');
    cy.scrollTo('top', {ensureScrollable: false});
    cy.screenshot(`${screenshotsPath}/settings_profile${fileNameSuffix}`, {
        capture: captureType,
        overwrite: true
    });
    // Privacy settings
    cy.visit('/privacy-settings');
    cy.get("#view-privacy-btn").should('exist');
    cy.scrollTo('top', {ensureScrollable: false});
    cy.screenshot(`${screenshotsPath}/settings_privacy${fileNameSuffix}`, {
        capture: captureType,
        overwrite: true
    });
    // Account settings
    cy.visit('/account-settings');
    cy.get("#reset-pwd-btn").should('exist');
    cy.scrollTo('top', {ensureScrollable: false});
    cy.screenshot(`${screenshotsPath}/settings_account${fileNameSuffix}`, {
        capture: captureType,
        overwrite: true
    });
}
