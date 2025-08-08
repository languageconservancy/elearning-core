import { browser, by, element } from "protractor";
import { ColorThemeRgba } from "../lib/color-theme";
import { EtoEConsts } from "../lib/e2e.consts";
import { EtoELib } from "../lib/e2e.lib";

describe("Navbar", () => {
    const learnBtn = element(by.id("nav-learn-btn"));
    const reviewBtn = element(by.id("nav-review-btn"));
    const progressBtn = element(by.id("nav-progress-btn"));
    const leaderboardBtn = element(by.id("nav-leaderboard-btn"));
    const villageBtn = element(by.id("nav-village-btn"));
    const usernameText = element(by.id("username-text"));
    const profileDropdown = element(by.id("profile-dropdown"));
    const settingsDropdownItem = element(by.id("nav-settings-btn"));

    beforeAll(async () => {
        await browser.get("");
        await EtoELib.logIn();
        await learnBtn.click();
    });

    beforeEach(async () => {});

    afterAll(async () => {
        await EtoELib.logOut();
    });

    it("url should be correct", async () => {
        expect(await browser.getCurrentUrl()).toBe("http://localhost:4200/start-learning");
    });

    describe("learn button", () => {
        it("text should be correct", async () => {
            expect(await learnBtn.getText()).toEqual("Learn");
        });

        it(`should have background of ${ColorThemeRgba.PRIMARY_COLOR_DARK}
			and color of ${ColorThemeRgba.TEXT_PRIMARY_CONTRAST}`, async () => {
            expect(await learnBtn.getCssValue("background-color")).toEqual(
                ColorThemeRgba.PRIMARY_COLOR_DARK,
            );
            expect(await learnBtn.getCssValue("color")).toEqual(
                ColorThemeRgba.TEXT_PRIMARY_CONTRAST,
            );
        });

        it(`hovered should have background of ${ColorThemeRgba.PRIMARY_COLOR_DARK}
			and color of ${ColorThemeRgba.TEXT_PRIMARY_CONTRAST}`, async () => {
            await browser.actions().mouseMove(learnBtn).perform();
            await browser.wait(
                EtoELib.waitForCssValue(
                    learnBtn,
                    "background-color",
                    ColorThemeRgba.PRIMARY_COLOR_DARK,
                ),
                EtoEConsts.CSS_WAIT_TIMEOUT_MS,
            );
            await browser.wait(
                EtoELib.waitForCssValue(learnBtn, "color", ColorThemeRgba.TEXT_PRIMARY_CONTRAST),
                EtoEConsts.CSS_WAIT_TIMEOUT_MS,
            );
        });
    });

    describe("review button", () => {
        it("text should be correct", async () => {
            expect(await reviewBtn.getText()).toEqual("Review");
        });

        it(`should have background of rgba(0, 0, 0, 0)
			and color of ${ColorThemeRgba.TEXT_PRIMARY_CONTRAST}`, async () => {
            expect(await reviewBtn.getCssValue("background-color")).toEqual("rgba(0, 0, 0, 0)");
            expect(await reviewBtn.getCssValue("color")).toEqual(
                ColorThemeRgba.TEXT_PRIMARY_CONTRAST,
            );
        });

        it(`hovered should have background of ${ColorThemeRgba.PRIMARY_COLOR_DARK}
			and color of ${ColorThemeRgba.TEXT_PRIMARY_CONTRAST}`, async () => {
            await browser.actions().mouseMove(reviewBtn).perform();
            await browser.wait(
                EtoELib.waitForCssValue(
                    reviewBtn,
                    "background-color",
                    ColorThemeRgba.PRIMARY_COLOR_DARK,
                ),
                EtoEConsts.CSS_WAIT_TIMEOUT_MS,
            );
            await browser.wait(
                EtoELib.waitForCssValue(reviewBtn, "color", ColorThemeRgba.TEXT_PRIMARY_CONTRAST),
                EtoEConsts.CSS_WAIT_TIMEOUT_MS,
            );
        });
    });

    describe("user menu", () => {
        it("should have correct text", async () => {
            expect(await usernameText.getText()).toEqual(EtoEConsts.E2E_USERNAME);
        });

        it(`should have transparent background and color of ${ColorThemeRgba.TEXT_PRIMARY_CONTRAST}`, async () => {
            expect(await usernameText.getCssValue("background-color")).toEqual("rgba(0, 0, 0, 0)");
            expect(await usernameText.getCssValue("color")).toEqual(
                ColorThemeRgba.TEXT_PRIMARY_CONTRAST,
            );
        });

        it(`should drop down when hovered`, async () => {
            await browser.actions().mouseMove(usernameText).perform();
            await browser.wait(
                EtoELib.waitForCssValue(profileDropdown, "display", "block"),
                EtoEConsts.CSS_WAIT_TIMEOUT_MS,
            );
        });

        it(`dropdown menu items should have background of rgba(0, 0, 0, 0)
			and color of ${ColorThemeRgba.TEXT_UI_PANEL_DARK_CONTRAST}`, async () => {
            expect(await usernameText.getCssValue("background-color")).toEqual("rgba(0, 0, 0, 0)");
            expect(await usernameText.getCssValue("color")).toEqual(
                ColorThemeRgba.TEXT_PRIMARY_CONTRAST,
            );
        });

        it(`hovered dropdown menu items should have background of ${ColorThemeRgba.PRIMARY_COLOR}
			and color of ${ColorThemeRgba.TEXT_UI_PANEL_DARK_CONTRAST}`, async () => {
            await browser.actions().mouseMove(settingsDropdownItem).perform();
            await browser.wait(
                EtoELib.waitForCssValue(
                    settingsDropdownItem,
                    "background-color",
                    ColorThemeRgba.PRIMARY_COLOR,
                ),
                EtoEConsts.CSS_WAIT_TIMEOUT_MS,
            );
            await browser.wait(
                EtoELib.waitForCssValue(
                    settingsDropdownItem,
                    "color",
                    ColorThemeRgba.TEXT_PRIMARY_CONTRAST,
                ),
                EtoEConsts.CSS_WAIT_TIMEOUT_MS,
            );
        });
    });
});
