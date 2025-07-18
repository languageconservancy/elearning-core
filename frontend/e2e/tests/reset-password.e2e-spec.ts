import { browser, by, element } from 'protractor';
import { ColorThemeRgba } from '../lib/color-theme';
import { EtoELib } from '../lib/e2e.lib';
import { EtoEConsts } from '../lib/e2e.consts';

describe('Reset Password page', () => {
	const header = element(by.id('forgot-pwd-header'));
	const emailInstruction = element(by.id('email-instruction'));
	const submitBtn = element(by.id('new-pwd-submit-btn'));
	const backToHomeBtn = element(by.id('back-to-home'));

	beforeAll(async() => {
		await browser.get('reset-password');
	});

	beforeEach(async() => {
	});

	describe('header', () => {
		it('should have correct text', async() => {
			expect(await header.getText()).toEqual('Forgot Password');
		});

		it(`should have background-color of ${ColorThemeRgba.PRIMARY_COLOR}`, async() => {
			expect(await header.getCssValue('background-color'))
				.toEqual(ColorThemeRgba.PRIMARY_COLOR);
		});

		it(`should have color of ${ColorThemeRgba.TEXT_PRIMARY_CONTRAST}`, async() => {
			expect(await header.getCssValue('color'))
				.toEqual(ColorThemeRgba.TEXT_PRIMARY_CONTRAST);
		});
	});

	describe('instruction', async() => {
		it('should have correct text', async() => {
			expect(await emailInstruction.getText()).toEqual('Enter Your Email Below');
		});

		it(`should have background-color of rgba(0, 0, 0, 0)`, async() => {
			expect(await emailInstruction.getCssValue('background-color'))
				.toEqual('rgba(0, 0, 0, 0)');
		});

		it(`should have color of ${ColorThemeRgba.TEXT_PRIMARY_CONTRAST}`, async() => {
			expect(await emailInstruction.getCssValue('color'))
				.toEqual(ColorThemeRgba.TEXT_PRIMARY_CONTRAST);
		});
	});

	describe('submit button', async() => {
		it('should have correct text', async() => {
			expect(await submitBtn.getText()).toEqual('Submit');
		});

		it(`should have background-color of ${ColorThemeRgba.UI_PANEL_LIGHT}
			and color of ${ColorThemeRgba.TEXT_PRIMARY}`, async() => {
			expect(await submitBtn.getCssValue('background-color'))
				.toEqual(ColorThemeRgba.UI_PANEL_LIGHT);
			expect(await submitBtn.getCssValue('color'))
				.toEqual(ColorThemeRgba.TEXT_PRIMARY);
		});

		it(`hovered should have background of ${ColorThemeRgba.PRIMARY_COLOR}
			and color of ${ColorThemeRgba.TEXT_PRIMARY_CONTRAST}`, async() => {
			await browser.actions()
				.mouseMove(submitBtn)
				.perform();
			await browser.wait(EtoELib.waitForCssValue(
				submitBtn, 'background-color', ColorThemeRgba.PRIMARY_COLOR), EtoEConsts.CSS_WAIT_TIMEOUT_MS);
			await browser.wait(EtoELib.waitForCssValue(
					submitBtn, 'color', ColorThemeRgba.TEXT_PRIMARY_CONTRAST), EtoEConsts.CSS_WAIT_TIMEOUT_MS);
		});
	});

	describe('back to home link', async() => {
		it('should have correct text', async() => {
			expect(await backToHomeBtn.getText()).toEqual('Back to Home');
		});

		it(`should have background-color of rgba(0, 0, 0, 0)
			and color of ${ColorThemeRgba.TEXT_UI_PANEL_DARK_CONTRAST}`, async() => {
			expect(await backToHomeBtn.getCssValue('background-color'))
				.toEqual('rgba(0, 0, 0, 0)');
			expect(await backToHomeBtn.getCssValue('color'))
				.toEqual(ColorThemeRgba.TEXT_UI_PANEL_DARK_CONTRAST);
		});

		it(`hovered should have background of rgba(0, 0, 0, 0)
			and color of ${ColorThemeRgba.PRIMARY_COLOR_LIGHT}`, async() => {
			await browser.actions()
				.mouseMove(backToHomeBtn)
				.perform();
			await browser.wait(EtoELib.waitForCssValue(
				backToHomeBtn, 'background-color', 'rgba(0, 0, 0, 0)'), EtoEConsts.CSS_WAIT_TIMEOUT_MS);
			await browser.wait(EtoELib.waitForCssValue(
					backToHomeBtn, 'color', ColorThemeRgba.PRIMARY_COLOR_LIGHT), EtoEConsts.CSS_WAIT_TIMEOUT_MS);
		});
	});
});
