import { browser, by, element } from 'protractor';
import { ColorThemeRgba } from '../lib/color-theme';
import { EtoELib } from '../lib/e2e.lib';
import { EtoEConsts } from '../lib/e2e.consts';
import { environment } from '../../src/environments/environment';

describe('Login page', () => {
	const submitBtn = element(by.id('login-submit-btn'));
	const googleBtn = element(by.id('google-signin-link'));
	const facebookBtn = element(by.id('facebook-signin-link'));
	const cleverBtn = element(by.id('clever-signin-link'));
	const forgotPwdBtn = element(by.id('forgot-pwd-btn'));
	const signUpBtn = element(by.id('nav-signup-btn'));
	const emailInput = element(by.id('signin-email'));
	const passwordInput = element(by.id('signin-password'));

	beforeAll(async() => {
		await browser.get('');
	});

	beforeEach(async() => {

	});

	it('Page title should display ' + environment.SITE_NAME, async() => {
		expect(await browser.getTitle()).toEqual(environment.SITE_NAME);
	});

	describe('Google login button', () => {
		it('text should be correct', async() => {
			expect(await googleBtn.getText()).toEqual('Sign in with Google');
		});

		it(`should have background of ${ColorThemeRgba.UI_PANEL_LIGHT}
			and color of ${ColorThemeRgba.TEXT_UI_PANEL_LIGHT_CONTRAST}`, async() => {
			expect(await googleBtn.getCssValue('background-color'))
				.toEqual(ColorThemeRgba.UI_PANEL_LIGHT);
			expect(await googleBtn.getCssValue('color'))
				.toEqual(ColorThemeRgba.TEXT_UI_PANEL_LIGHT_CONTRAST);
		});

		it(`hovered should have background of ${ColorThemeRgba.UI_PANEL_LIGHT}
			and color of ${ColorThemeRgba.PRIMARY_COLOR}`, async() => {
			await browser.actions()
				.mouseMove(googleBtn)
				.perform();
			await browser.wait(EtoELib.waitForCssValue(
				googleBtn, 'background-color', ColorThemeRgba.UI_PANEL_LIGHT), EtoEConsts.CSS_WAIT_TIMEOUT_MS);
			await browser.wait(EtoELib.waitForCssValue(
					googleBtn, 'color', ColorThemeRgba.PRIMARY_COLOR), EtoEConsts.CSS_WAIT_TIMEOUT_MS);
		});
	});

	describe('Facebook login button', () => {
		it('text should be correct', async() => {
			expect(await facebookBtn.getText()).toEqual('Sign in with Facebook');
		});

		it(`should have background of ${ColorThemeRgba.UI_PANEL_LIGHT}
			and color of ${ColorThemeRgba.TEXT_UI_PANEL_LIGHT_CONTRAST}`, async() => {
			expect(await facebookBtn.getCssValue('background-color'))
				.toEqual(ColorThemeRgba.UI_PANEL_LIGHT);
			expect(await facebookBtn.getCssValue('color'))
				.toEqual(ColorThemeRgba.TEXT_UI_PANEL_LIGHT_CONTRAST);
		});

		it(`hovered should have background of ${ColorThemeRgba.UI_PANEL_LIGHT}
			and color of ${ColorThemeRgba.PRIMARY_COLOR}`, async() => {
			await browser.actions()
				.mouseMove(facebookBtn)
				.perform();
			await browser.wait(EtoELib.waitForCssValue(
				facebookBtn, 'background-color', ColorThemeRgba.UI_PANEL_LIGHT), EtoEConsts.CSS_WAIT_TIMEOUT_MS);
			await browser.wait(EtoELib.waitForCssValue(
					facebookBtn, 'color', ColorThemeRgba.PRIMARY_COLOR), EtoEConsts.CSS_WAIT_TIMEOUT_MS);
		});
	});

	describe('Clever login button', () => {
		it('should have correct text', async() => {
			expect(await cleverBtn.getText()).toEqual('Sign in with Clever');
		});

		it(`should have background of ${ColorThemeRgba.UI_PANEL_LIGHT}
			and color of ${ColorThemeRgba.TEXT_UI_PANEL_LIGHT_CONTRAST}`, async() => {
			expect(await cleverBtn.getCssValue('background-color'))
				.toEqual(ColorThemeRgba.UI_PANEL_LIGHT);
			expect(await cleverBtn.getCssValue('color'))
				.toEqual(ColorThemeRgba.TEXT_UI_PANEL_LIGHT_CONTRAST);
		});

		it(`hovered should have background of ${ColorThemeRgba.UI_PANEL_LIGHT}
			and color of ${ColorThemeRgba.PRIMARY_COLOR}`, async() => {
			await browser.actions()
				.mouseMove(cleverBtn)
				.perform();
			await browser.wait(EtoELib.waitForCssValue(
				cleverBtn, 'background-color', ColorThemeRgba.UI_PANEL_LIGHT), EtoEConsts.CSS_WAIT_TIMEOUT_MS);
			await browser.wait(EtoELib.waitForCssValue(
					cleverBtn, 'color', ColorThemeRgba.PRIMARY_COLOR), EtoEConsts.CSS_WAIT_TIMEOUT_MS);
		});
	});

	describe('email input', () => {
		it('should have correct placeholder text', async() => {
			expect(await emailInput.getAttribute('placeholder')).toEqual('Email');
		});

		it(`should have color of
			${ColorThemeRgba.TEXT_UI_PANEL_LIGHT_PLACEHOLDER}`, async() => {
			const color = browser.executeScript(`
				var myEl = document.getElementById('email-input');
				if (!myEl) return 'null element';
				return window.getComputedStyle(myEl, ':placeholder').getPropertyValue('color');`);
			expect(color).toEqual(ColorThemeRgba.TEXT_UI_PANEL_LIGHT_PLACEHOLDER);
		});
	});

	describe('password input', () => {
		it('should have correct placeholder text', async() => {
			expect(await passwordInput.getAttribute('placeholder')).toEqual('Password');
		});

		it(`should have color of
			${ColorThemeRgba.TEXT_UI_PANEL_LIGHT_PLACEHOLDER}`, async() => {
			const color = browser.executeScript(`
				var myEl = document.getElementById('password-input');
				if (!myEl) return 'null element';
				window.getComputedStyle(myEl, ':placeholder').getPropertyValue('color');`);
			expect(color).toEqual(ColorThemeRgba.TEXT_UI_PANEL_LIGHT_PLACEHOLDER);
		});
	});

	describe('forgot password button', () => {
		it(`should have color of
			${ColorThemeRgba.TEXT_UI_PANEL_DARK_CONTRAST}`, async() => {
			expect(await forgotPwdBtn.getCssValue('color'))
				.toEqual(ColorThemeRgba.TEXT_UI_PANEL_DARK_CONTRAST);
		});

		it(`hovered should have color of
			${ColorThemeRgba.PRIMARY_COLOR_LIGHT}`, async() => {
			await browser.actions()
				.mouseMove(forgotPwdBtn)
				.perform();
			await browser.wait(EtoELib.waitForCssValue(
					forgotPwdBtn, 'color', ColorThemeRgba.PRIMARY_COLOR_LIGHT), EtoEConsts.CSS_WAIT_TIMEOUT_MS);
		});
	});

	describe('submit button', () => {
		it('text should be correct', async() => {
			expect(await submitBtn.getText()).toEqual('Submit');
		});

		it(`should have background of ${ColorThemeRgba.UI_PANEL_LIGHT}
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

	describe('sign-up button', () => {
		it('text should be correct', async() => {
			expect(await signUpBtn.getText()).toEqual('Sign up');
		});

		it(`should have background of ${ColorThemeRgba.UI_PANEL_LIGHT}
			and color of ${ColorThemeRgba.PRIMARY_COLOR}`, async() => {
			expect(await signUpBtn.getCssValue('background-color'))
				.toEqual(ColorThemeRgba.UI_PANEL_LIGHT);
			expect(await signUpBtn.getCssValue('color'))
				.toEqual(ColorThemeRgba.PRIMARY_COLOR);
		});

		it(`hovered should have background of ${ColorThemeRgba.PRIMARY_COLOR}
			and color of ${ColorThemeRgba.PRIMARY_COLOR}`, async() => {
			await browser.actions()
				.mouseMove(signUpBtn)
				.perform();
			await browser.wait(EtoELib.waitForCssValue(
				signUpBtn, 'background-color', ColorThemeRgba.PRIMARY_COLOR), EtoEConsts.CSS_WAIT_TIMEOUT_MS);
			await browser.wait(EtoELib.waitForCssValue(
					signUpBtn, 'color', ColorThemeRgba.TEXT_PRIMARY_CONTRAST), EtoEConsts.CSS_WAIT_TIMEOUT_MS);
		});
	});
});
