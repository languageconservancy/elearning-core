import { browser, by, element } from 'protractor';
import { ColorThemeRgba } from '../lib/color-theme';
import { EtoELib } from '../lib/e2e.lib';
import { EtoEConsts } from '../lib/e2e.consts';

xdescribe('Mcq page', () => {
	const settings = element(by.id('nav-village-btn'));
	const learnBtn = element(by.id('nav-learn-btn'));
	const unitStartBtn = element(by.id('unit-0-start-btn'));
	const promptAudioIcon = element(by.css('a.audio-prompt img'));
	// const promptAudioIcon = element(by.css('#prompt-audio > img'));

	beforeAll(async() => {
		await browser.get('');
		await EtoELib.logIn();
		await browser.sleep(3000);
		await EtoELib.goToSettings();
		await EtoELib.resetProgressData();
		await learnBtn.click();
		await unitStartBtn.click();
	});

	beforeEach(async() => {
	});

	it('url should be correct', async() => {
		// expect(await browser.getCurrentUrl()).toBe("http://localhost:4200/lessons-and-exercises");
	});

	describe('audio to image', () => {
		describe('audio icon', () => {
			it('should have color of rgba(0, 0, 0, 1) (black)', async() => {
				expect(await promptAudioIcon.getCssValue('color'))
					.toEqual('rgba(0, 0, 0, 1)');
			});

			// it(`should have hovered color of light black`, async() => {
			//   await browser.actions()
			//     .mouseMove(promptAudioIcon)
			//     .perform();
			//   expect(await promptAudioIcon.getCssValue('color'))
			//     .toEqual('rgba(74, 74, 74, 1)');
			// });
		});
	});
});
