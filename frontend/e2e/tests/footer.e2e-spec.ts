import { browser, by, element } from 'protractor';
import { ColorThemeRgba } from '../lib/color-theme';

describe('Footer', () => {
	var footer = element(by.className('footer_part'));

	beforeAll(async() => {
		await browser.get('');
	});

	beforeEach(async() => {
	});

	it(`should background of ${ColorThemeRgba.PRIMARY_COLOR_DARK}`, async() => {
		expect(await footer.getCssValue('background-color'))
			.toEqual(ColorThemeRgba.PRIMARY_COLOR_DARK);
	});
});
