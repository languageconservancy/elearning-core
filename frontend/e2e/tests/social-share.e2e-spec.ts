import { browser, by, element } from 'protractor';
import { ColorThemeRgba } from '../lib/color-theme';
import { EtoELib } from '../lib/e2e.lib';
import { EtoEConsts } from '../lib/e2e.consts';

describe('Social share partial', () => {
	const header = element(by.css('div.social-share h3'));
	const villageBtn = element(by.id('nav-village-btn'));

	beforeAll(async() => {
		await browser.get('');
		await EtoELib.logIn();
		await villageBtn.click();
	});

	beforeEach(async() => {
	});

	it('url should be correct', async() => {
		expect(await browser.getCurrentUrl()).toBe("http://localhost:4200/village");
	});

	describe('header', () => {
		it('should have correct text', async() => {
			expect(await header.getText()).toEqual('Share on...');
		});

		it(`should have background-color of rgba(0, 0, 0, 0)`, async() => {
			expect(await header.getCssValue('background-color'))
				.toEqual('rgba(0, 0, 0, 0)');
		});

		it(`should have color of rgba(255, 255, 255, 1) (white)`, async() => {
			expect(await header.getCssValue('color'))
				.toEqual('rgba(255, 255, 255, 1)');
		});
	});
});
