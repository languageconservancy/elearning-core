import { browser, by, element } from 'protractor';
import { ColorThemeRgba } from '../lib/color-theme';

describe('Info Space', () => {
	var infoSpace = element(by.className('top_footerpart'));

	beforeAll(async() => {
		await browser.get('');
	});

	beforeEach(async() => {
	});

	it(`should background of ${ColorThemeRgba.INFO_SPACE}`, async() => {
		expect(await infoSpace.getCssValue('background-color'))
			.toEqual(ColorThemeRgba.INFO_SPACE);
	});
});
