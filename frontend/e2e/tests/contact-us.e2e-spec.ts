import { browser, by, element } from 'protractor';
import { ColorThemeRgba } from '../lib/color-theme';
import { EtoEConsts } from '../lib/e2e.consts';
import { EtoELib } from '../lib/e2e.lib';

describe('Contact-us page', () => {
	var header = element(by.tagName("h2"));
	var sendBtn = element(by.id('contact-us-send-btn'));
	var nameInput = element(by.id('name-input'));
	var usernameInput = element(by.id('username-input'));
	var emailInput = element(by.id('email-input'));
	var problemArea = element(by.id('problem-area'));

	beforeAll(async() => {
		await browser.get('contact-us');
	});

	beforeEach(async() => {
	});

	describe('header', () => {
		it('should have correct text', async() => {
			expect(await header.getText()).toEqual('Contact Us');
		});

		it(`should have background-color of
			${ColorThemeRgba.PRIMARY_COLOR}`, async() => {
			expect(await header.getCssValue('background-color'))
				.toEqual(ColorThemeRgba.PRIMARY_COLOR);
		});

		it(`should have color of
			${ColorThemeRgba.TEXT_PRIMARY_CONTRAST}`, async() => {
			expect(await header.getCssValue('color'))
				.toEqual(ColorThemeRgba.TEXT_PRIMARY_CONTRAST);
		});
	});

	describe('name input', () => {
		it('should have correct placeholder text', async() => {
			expect(await nameInput.getAttribute('placeholder')).toEqual('Name');
		});

		it(`should have color of
			${ColorThemeRgba.TEXT_UI_PANEL_LIGHT_PLACEHOLDER}`, async() => {
			var color = browser.executeScript(`
				var el = document.getElementById('name-input');
				return window.getComputedStyle(el, ':placeholder').getPropertyValue('color')`)
			expect(color).toEqual(ColorThemeRgba.TEXT_UI_PANEL_LIGHT_PLACEHOLDER);
		});
	});

	describe('username input', () => {
		it('should have correct placeholder text', async() => {
			expect(await usernameInput.getAttribute('placeholder')).toEqual('User name');
		});

		// it(`should have color of
		//   ${ColorThemeRgba.TEXT_UI_PANEL_LIGHT_PLACEHOLDER}`, async() => {
		//   var color = browser.executeScript(
		//     `return window.getComputedStyle(document.getElementById('username-input'), :placeholder').getPropertyValue('color');`);
		//   expect(color).toEqual(ColorThemeRgba.TEXT_UI_PANEL_LIGHT_PLACEHOLDER);
		// });
	});

	describe('email input', () => {
		it('should have correct placeholder text', async() => {
			expect(await emailInput.getAttribute('placeholder')).toEqual('Email');
		});

		it('should have color of '
			+ ColorThemeRgba.TEXT_UI_PANEL_LIGHT_PLACEHOLDER, async() => {
			var color = browser.executeScript(`
				const myEl = document.getElementById('email-input');
				return window.getComputedStyle(myEl, ':placeholder').getPropertyValue('color');`);
			expect(color).toEqual(ColorThemeRgba.TEXT_UI_PANEL_LIGHT_PLACEHOLDER);
		});
	});

	describe('problem details textarea', () => {
		it('should have correct placeholder text', async() => {
			expect(await problemArea.getAttribute('placeholder')).toEqual('Problem details');
		});

		it('should have color of '
			+ ColorThemeRgba.TEXT_UI_PANEL_LIGHT_PLACEHOLDER, async() => {
			var color = browser.executeScript(`return window.getComputedStyle(
				document.getElementById('problem-area'), ':placeholder')
					.getPropertyValue('color');`);
			expect(color).toEqual(ColorThemeRgba.TEXT_UI_PANEL_LIGHT_PLACEHOLDER);
		});
	})

	describe('send button', () => {
		it('should have correct text', async() => {
			expect(await sendBtn.getText()).toEqual('Send');
		});

		it('should have background-color of '
			+ ColorThemeRgba.UI_PANEL_LIGHT, async() => {
			expect(await sendBtn.getCssValue('background-color'))
				.toEqual(ColorThemeRgba.UI_PANEL_LIGHT);
		});

		it('should have color of '
			+ ColorThemeRgba.TEXT_PRIMARY, async() => {
			expect(await sendBtn.getCssValue('color'))
				.toEqual(ColorThemeRgba.TEXT_PRIMARY);
		});

		it('hovered should have background-color of '
		+ ColorThemeRgba.PRIMARY_COLOR, async() => {
			await browser.actions()
				.mouseMove(sendBtn)
				.perform();
			await browser.wait(EtoELib.waitForCssValue(
				sendBtn, 'background-color', ColorThemeRgba.PRIMARY_COLOR), EtoEConsts.CSS_WAIT_TIMEOUT_MS);
		});

		it('hovered should have color of '
		+ ColorThemeRgba.TEXT_PRIMARY_CONTRAST, async() => {
			await browser.wait(EtoELib.waitForCssValue(
				sendBtn, 'color', ColorThemeRgba.TEXT_PRIMARY_CONTRAST), EtoEConsts.CSS_WAIT_TIMEOUT_MS);
		});
	});

});
