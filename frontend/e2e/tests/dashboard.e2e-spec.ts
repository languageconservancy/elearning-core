import { browser, by, element } from 'protractor';
import { ColorThemeRgba } from '../lib/color-theme';
import { EtoEConsts } from '../lib/e2e.consts';
import { EtoELib } from '../lib/e2e.lib';

describe('The Dashboard page', () => {
	var learnBtn = element(by.id('learn-btn'));
	var reviewBtn = element(by.id('review-btn'));
	var pathLink = element(by.id('path-link'));

	beforeAll(async() => {
		await browser.get('');
		await EtoELib.logIn();
		expect(await browser.getCurrentUrl()).toBe("http://localhost:4200/dashboard");
	});

	beforeEach(async() => {
	});

	afterAll(async() => {
		await EtoELib.logOut();
	});

	describe('path link', () => {
		it(`should have color of ${ColorThemeRgba.PRIMARY_COLOR_LIGHT}`, async() => {
			expect(await pathLink.getCssValue('color')).toEqual(ColorThemeRgba.PRIMARY_COLOR_LIGHT);
		});

		it(`hovered should have color of ${ColorThemeRgba.PRIMARY_COLOR_DARK}`, async() => {
			try {
			await browser.actions()
				.mouseMove(pathLink)
				.perform();
			await browser.wait(EtoELib.waitForCssValue(
				pathLink, 'color', ColorThemeRgba.PRIMARY_COLOR_DARK), EtoEConsts.CSS_WAIT_TIMEOUT_MS);
			} catch (e) {
				console.error("Got exception: ", e);
			}
		});
	});

	describe('learn button', () => {
		it('should say "Learn"', async() => {
			expect(await learnBtn.getText()).toEqual("Learn");
		});

		it(`should have background of ${ColorThemeRgba.UI_PANEL_LIGHT}
			and color of ${ColorThemeRgba.TEXT_PRIMARY}`, async() => {
			expect(await learnBtn.getCssValue('background-color')).toEqual(ColorThemeRgba.UI_PANEL_LIGHT);
			expect(await learnBtn.getCssValue('color')).toEqual(ColorThemeRgba.TEXT_PRIMARY);
		});

		it(`hovered should have background of ${ColorThemeRgba.PRIMARY_COLOR}
			and color of ${ColorThemeRgba.TEXT_PRIMARY_CONTRAST}`, async() => {
			await browser.actions()
				.mouseMove(learnBtn)
				.perform();
			await browser.wait(EtoELib.waitForCssValue(
				learnBtn, 'background-color', ColorThemeRgba.PRIMARY_COLOR), EtoEConsts.CSS_WAIT_TIMEOUT_MS);
			await browser.wait(EtoELib.waitForCssValue(
					learnBtn, 'color', ColorThemeRgba.TEXT_PRIMARY_CONTRAST), EtoEConsts.CSS_WAIT_TIMEOUT_MS);
		});
	});

	describe('review button', () => {
		it('should say "Review"', async() => {
			expect(await reviewBtn.getText()).toEqual("Review");
		});

		it(`should have background of ${ColorThemeRgba.UI_PANEL_LIGHT}
			and color of ${ColorThemeRgba.TEXT_PRIMARY}`, async() => {
			expect(await reviewBtn.getCssValue('background-color')).toEqual(ColorThemeRgba.UI_PANEL_LIGHT);
			expect(await reviewBtn.getCssValue('color')).toEqual(ColorThemeRgba.TEXT_PRIMARY);
		});

		it(`hovered should have background of ${ColorThemeRgba.PRIMARY_COLOR}
			and color of ${ColorThemeRgba.TEXT_PRIMARY_CONTRAST}`, async() => {
			await browser.actions()
				.mouseMove(reviewBtn)
				.perform();
			await browser.wait(EtoELib.waitForCssValue(
				reviewBtn, 'background-color', ColorThemeRgba.PRIMARY_COLOR), EtoEConsts.CSS_WAIT_TIMEOUT_MS);
			await browser.wait(EtoELib.waitForCssValue(
					reviewBtn, 'color', ColorThemeRgba.TEXT_PRIMARY_CONTRAST), EtoEConsts.CSS_WAIT_TIMEOUT_MS);
		});
	});
});
