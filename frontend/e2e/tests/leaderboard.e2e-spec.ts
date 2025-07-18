import { browser, by, element } from 'protractor';
import { ColorThemeRgba, ColorThemeRgb } from '../lib/color-theme';
import { EtoEConsts } from '../lib/e2e.consts';
import { EtoELib } from '../lib/e2e.lib';

describe('Leaderboard page', () => {
	const leaderboardBtn = element.all(by.id('nav-leaderboard-btn'));
	const topHeaders = element.all(by.css('h3.top-heading span'));

	beforeAll(async() => {
		await browser.get('');
		await EtoELib.logIn();
		await leaderboardBtn.click();
	});

	beforeEach(async() => {
	});

	afterAll(async() => {
		await EtoELib.logOut();
	});

	it('url should be correct', async() => {
		expect(await browser.getCurrentUrl()).toBe("http://localhost:4200/leader-board");
	});

	describe('Top headers', () => {
		it(`should have background of ${ColorThemeRgba.PRIMARY_COLOR}`, async() => {
			expect(await topHeaders.get(0).getCssValue('background-color')).toEqual(ColorThemeRgba.PRIMARY_COLOR);
			expect(await topHeaders.get(1).getCssValue('background-color')).toEqual(ColorThemeRgba.PRIMARY_COLOR);
		});

		it(`should have color of ${ColorThemeRgba.TEXT_PRIMARY_CONTRAST}`, async() => {
			expect(await topHeaders.get(0).getCssValue('color')).toEqual(ColorThemeRgba.TEXT_PRIMARY_CONTRAST);
			expect(await topHeaders.get(1).getCssValue('color')).toEqual(ColorThemeRgba.TEXT_PRIMARY_CONTRAST);
		});
	});
});
