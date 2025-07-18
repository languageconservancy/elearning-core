import { browser, by, element } from 'protractor';
import { ColorThemeRgba, ColorThemeRgb } from '../lib/color-theme';
import { EtoEConsts } from '../lib/e2e.consts';
import { EtoELib } from '../lib/e2e.lib';

describe('Learn page', () => {
	const learnBtn = element(by.id('nav-learn-btn'));
	const continueBtn = element(by.id('continue-btn'));
	const activeLevelBtn = element(by.id('level-0-btn'));
	const inactiveLevelBtn = element(by.id('level-1-btn'));
	const unitStartBtn = element(by.id('unit-0-start-btn'));
	const unitNameText = element(by.id('unit-0-name'));
	const unitDescriptionText = element(by.id('unit-0-description'));
	const unitsContainer = element(by.className('units-container'));
	const unitContainer = element(by.id('unit-0-container'));

	beforeAll(async() => {
		await browser.get('');
		await EtoELib.logIn();
		await learnBtn.click();
		await activeLevelBtn.click();
	});

	beforeEach(async() => {
	});

	afterAll(async() => {
		await EtoELib.logOut();
	});

	it('url should be correct', async() => {
		expect(await browser.getCurrentUrl()).toBe("http://localhost:4200/start-learning");
	});

	describe('continue button', () => {
		it(`should have background of ${ColorThemeRgba.PRIMARY_COLOR}
			and color of ${ColorThemeRgba.TEXT_PRIMARY_CONTRAST}`, async() => {
			expect(await continueBtn.getCssValue('background-color'))
				.toEqual(ColorThemeRgba.PRIMARY_COLOR);
			expect(await continueBtn.getCssValue('color'))
				.toEqual(ColorThemeRgba.TEXT_PRIMARY_CONTRAST);
		});

		it(`hovered should have background of ${ColorThemeRgba.UI_PANEL_LIGHT}
			and color of ${ColorThemeRgba.TEXT_PRIMARY}`, async() => {
			await browser.actions()
				.mouseMove(continueBtn)
				.perform();
			await browser.wait(EtoELib.waitForCssValue(
				continueBtn, 'background-color', ColorThemeRgba.UI_PANEL_LIGHT), EtoEConsts.CSS_WAIT_TIMEOUT_MS);
			await browser.wait(EtoELib.waitForCssValue(
					continueBtn, 'color', ColorThemeRgba.TEXT_PRIMARY), EtoEConsts.CSS_WAIT_TIMEOUT_MS);
		});
	});

	describe('active level button', () => {
		it(`should have background of ${ColorThemeRgba.PRIMARY_COLOR}
			and color of ${ColorThemeRgba.TEXT_PRIMARY_CONTRAST}`, async() => {
			expect(await activeLevelBtn.getCssValue('background-color'))
				.toEqual(ColorThemeRgba.PRIMARY_COLOR);
			expect(await activeLevelBtn.getCssValue('color'))
				.toEqual(ColorThemeRgba.TEXT_PRIMARY_CONTRAST);
		});

		it(`hovered should have background of ${ColorThemeRgba.PRIMARY_COLOR}
			and color of ${ColorThemeRgba.TEXT_PRIMARY_CONTRAST}`, async() => {
			await browser.actions()
				.mouseMove(activeLevelBtn)
				.perform();
			await browser.wait(EtoELib.waitForCssValue(
				activeLevelBtn, 'background-color', ColorThemeRgba.PRIMARY_COLOR), EtoEConsts.CSS_WAIT_TIMEOUT_MS);
			await browser.wait(EtoELib.waitForCssValue(
					activeLevelBtn, 'color', ColorThemeRgba.TEXT_PRIMARY_CONTRAST), EtoEConsts.CSS_WAIT_TIMEOUT_MS);
		});
	});

	describe('inactive level button', () => {
		it(`should have background of ${ColorThemeRgba.UI_PANEL_LIGHT}
			and color of ${ColorThemeRgba.TEXT_UI_PANEL_LIGHT_CONTRAST_LIGHT}`, async() => {
			expect(await inactiveLevelBtn.getCssValue('background-color'))
				.toEqual(ColorThemeRgba.UI_PANEL_LIGHT);
			expect(await inactiveLevelBtn.getCssValue('color'))
				.toEqual(ColorThemeRgba.TEXT_UI_PANEL_LIGHT_CONTRAST_LIGHT);
		});

		it(`hovered should have background of ${ColorThemeRgba.PRIMARY_COLOR}
			and color of ${ColorThemeRgba.TEXT_PRIMARY_CONTRAST}`, async() => {
			await browser.actions()
				.mouseMove(inactiveLevelBtn)
				.perform();
			await browser.wait(EtoELib.waitForCssValue(
				inactiveLevelBtn, 'background-color', ColorThemeRgba.PRIMARY_COLOR), EtoEConsts.CSS_WAIT_TIMEOUT_MS);
			await browser.wait(EtoELib.waitForCssValue(
					inactiveLevelBtn, 'color', ColorThemeRgba.TEXT_PRIMARY_CONTRAST), EtoEConsts.CSS_WAIT_TIMEOUT_MS);
		});
	});

	describe('unit container', () => {
		it(`should have border color of ${ColorThemeRgba.PRIMARY_COLOR}`, async() => {
			expect(await unitContainer.getCssValue('border-color'))
				.toEqual(ColorThemeRgb.PRIMARY_COLOR);
		});
	});

	describe('unit start button', () => {
		it(`should have opacity of 1`, async() => {
			expect(await unitStartBtn.getCssValue('opacity'))
				.toEqual('1');
		});

		it(`hovered should have opacity of 0.8`, async() => {
			await browser.actions()
				.mouseMove(unitStartBtn)
				.perform();
			await browser.wait(EtoELib.waitForCssValue(
				unitStartBtn, 'opacity', '0.8'), EtoEConsts.CSS_WAIT_TIMEOUT_MS);
		});
	});

	describe('unit name', () => {
		it(`should have color of ${ColorThemeRgba.TEXT_PRIMARY}`, async() => {
			expect(await unitNameText.getCssValue('color'))
				.toEqual(ColorThemeRgba.TEXT_PRIMARY);
		});
	});

	describe('unit description', () => {
		it(`should have color of ${ColorThemeRgba.TEXT_UI_PANEL_LIGHT_CONTRAST}`, async() => {
			expect(await unitDescriptionText.getCssValue('color'))
				.toEqual(ColorThemeRgba.TEXT_UI_PANEL_LIGHT_CONTRAST);
		});
	});
});
