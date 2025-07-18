/**
 * Takes screenshots of all the sign-up pages,
 * for both desktop and mobile portrait.
 */

import "../support/commands";

describe('Desktop Sign-up Screenshots', () => {
    before(() => {
        cy.viewport(Cypress.env("desktop_width_px"), Cypress.env("desktop_height_px"));
    });
    it('Screenshot registration process', () => {
        cy.signUp("fullPage", "", "-desktop");
    });
})

describe('Mobile Sign-up Screenshots', () => {
    beforeEach(() => {
        cy.viewport(Cypress.env('mobile_viewport'));
    });
    it('Screenshot registration process', () => {
        cy.signUp("viewport", "", "-mobile-portrait");
    });
})
