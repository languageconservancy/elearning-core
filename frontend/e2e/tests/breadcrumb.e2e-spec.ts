import { browser, by, element } from 'protractor';
import { ColorThemeRgba } from '../lib/color-theme';
import { EtoEConsts } from '../lib/e2e.consts';
import { EtoELib } from '../lib/e2e.lib';

describe('Breadcrumb', () => {
	const learnBtn = element(by.id('nav-learn-btn'));
	const breadcrumbLink = element(by.id('breadcumb-link-0'));
	const breadcrumbSlash = element.all((by.className('breadcumb-slash')));

	beforeAll(async() => {
		await browser.get('');
		await EtoELib.logIn();
		await learnBtn.click();
	});

	beforeEach(async() => {
	});

	afterAll(async() => {
		await EtoELib.logOut();
	});

	it('url should be correct', async() => {
		expect(await browser.getCurrentUrl()).toBe("http://localhost:4200/start-learning");
	});

	describe('breadcrumb', () => {
		it(`should have background of rgba(0, 0, 0, 0)`, async() => {
			expect(await breadcrumbLink.getCssValue('background-color'))
				.toEqual('rgba(0, 0, 0, 0)');
		});

		it(`and color of ${ColorThemeRgba.TEXT_PRIMARY}`, async() => {
			expect(await breadcrumbLink.getCssValue('color'))
				.toEqual(ColorThemeRgba.TEXT_PRIMARY);
		});

		it(`hovered should have background of rgba(0, 0, 0, 0)`, async() => {
			await browser.actions()
				.mouseMove(breadcrumbLink)
				.perform();
			await browser.wait(EtoELib.waitForCssValue(
				breadcrumbLink, 'background-color', 'rgba(0, 0, 0, 0)'), EtoEConsts.CSS_WAIT_TIMEOUT_MS);
		});

		it(`hovered should have color of ${ColorThemeRgba.TEXT_PRIMARY}`, async() => {
			await browser.wait(EtoELib.waitForCssValue(
				breadcrumbLink, 'color', ColorThemeRgba.TEXT_PRIMARY), EtoEConsts.CSS_WAIT_TIMEOUT_MS);
		});

		it(`slashes should have color of ${ColorThemeRgba.TEXT_PRIMARY_CONTRAST}`, async() => {
			expect(await breadcrumbSlash.get(0).getCssValue('color'))
				.toEqual(ColorThemeRgba.TEXT_PRIMARY_CONTRAST);
		});
	});
});
