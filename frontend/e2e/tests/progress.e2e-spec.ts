import { browser, by, element } from 'protractor';
import { ColorThemeRgba, ColorThemeRgb } from '../lib/color-theme';
import { EtoEConsts } from '../lib/e2e.consts';
import { EtoELib } from '../lib/e2e.lib';

describe('Progress page', () => {
	const progressBtn = element(by.id('nav-progress-btn'));
	const activeLevelBtn = element(by.id('level-0-btn'));
	const inactiveLevelBtn = element(by.id('level-1-btn'));
	const backBtn = element(by.className('title-back-btn'));
	const pageHeader = element(by.css('.page-title h3 a'));
	const topHeaders = element.all(by.css('h3.top-heading span'));
	const pointsHeader = element(by.css('.total-point-txt span'));
	const leftPanelBtns = element.all(by.css('div.profile-leftpannel ul li a'));

	beforeAll(async() => {
		await browser.get('');
		await EtoELib.logIn();
		await progressBtn.click();
	});

	beforeEach(async() => {
	});

	afterAll(async() => {
		await EtoELib.logOut();
	});

	it('url should be correct', async() => {
		expect(await browser.getCurrentUrl()).toBe("http://localhost:4200/progress");
	});

	describe('Page header', () => {
		it(`should have background of rgba(0, 0, 0, 0)`, async() => {
			expect(await pageHeader.getCssValue('background-color'))
				.toEqual('rgba(0, 0, 0, 0)');
		});

		it(`should have color of black`, async() => {
			expect(await pageHeader.getCssValue('color'))
				.toEqual('rgba(0, 0, 0, 1)');
		});
	});

	describe('Top headers', () => {
		it(`should have background of ${ColorThemeRgba.PRIMARY_COLOR}`, async() => {
			expect(await topHeaders.get(0).getCssValue('background-color'))
				.toEqual(ColorThemeRgba.PRIMARY_COLOR);
			expect(await topHeaders.get(1).getCssValue('background-color'))
				.toEqual(ColorThemeRgba.PRIMARY_COLOR);
		});

		it(`should have color of ${ColorThemeRgba.TEXT_PRIMARY_CONTRAST}`, async() => {
			expect(await topHeaders.get(0).getCssValue('color'))
				.toEqual(ColorThemeRgba.TEXT_PRIMARY_CONTRAST);
			expect(await topHeaders.get(1).getCssValue('color'))
				.toEqual(ColorThemeRgba.TEXT_PRIMARY_CONTRAST);
		});
	});

	describe('Total Points span', () => {
		it(`should have background of rgba(0, 0, 0, 0) (transparent)`, async() => {
			expect(await pointsHeader.getCssValue('background-color'))
				.toEqual('rgba(0, 0, 0, 0)');
		});

		it(`should have color of ${ColorThemeRgba.TEXT_PRIMARY}`, async() => {
			expect(await pointsHeader.getCssValue('color'))
				.toEqual(ColorThemeRgba.TEXT_PRIMARY);
		});
	});

	describe('Left Panel', () => {
		it(`should have active button background of ${ColorThemeRgba.PRIMARY_COLOR}`, async() => {
			expect(await leftPanelBtns.get(0).getCssValue('background-color'))
				.toEqual(ColorThemeRgba.PRIMARY_COLOR);
		});

		it(`should have active button color of ${ColorThemeRgba.TEXT_PRIMARY_CONTRAST}`, async() => {
			expect(await leftPanelBtns.get(0).getCssValue('color'))
				.toEqual(ColorThemeRgba.TEXT_PRIMARY_CONTRAST);
		});

		it(`should have inactive button background of rgba(255, 255, 255, 1) (white)`, async() => {
			expect(await leftPanelBtns.get(1).getCssValue('background-color'))
				.toEqual('rgba(255, 255, 255, 1)');
		});

		it(`should have inactive button color of ${ColorThemeRgba.TEXT_UI_PANEL_LIGHT_CONTRAST_LIGHT}`, async() => {
			expect(await leftPanelBtns.get(1).getCssValue('color'))
				.toEqual(ColorThemeRgba.TEXT_UI_PANEL_LIGHT_CONTRAST_LIGHT);
		});
	});
});
