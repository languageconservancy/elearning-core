
import { browser, by, element } from 'protractor';
import { EtoEConsts } from '../lib/e2e.consts';

export class EtoELib {
	public static async logIn() {
		return new Promise(async (resolve, reject) => {
			try {
					var emailInput = element(by.id("signin-email"));
					var pwdInput = element(by.id("signin-password"));
					var form = element(by.name("loginForm"));
					await emailInput.sendKeys(EtoEConsts.E2E_EMAIL);
					await pwdInput.sendKeys(EtoEConsts.E2E_PWD);
					await form.submit();
					resolve(true);
			} catch (err) {
				console.error("e2e login failed: ", err);
				reject("e2e login failed");
			}
		});
	}

	public static waitForCssValue(elementFinder, cssProperty, cssValue) {
		return function () {
				return elementFinder.getCssValue(cssProperty).then(function (actualValue) {
						return actualValue === cssValue;
				});
		};
	};

	public static async logOut() {
		return new Promise(async (resolve) => {
			await browser.executeScript(`
					var res = document.cookie;
					var multiple = res.split(";");
					for(var i = 0; i < multiple.length; i++) {
						 var key = multiple[i].split("=");
						 document.cookie = key[0]+" =; expires = Thu, 01 Jan 1970 00:00:00 UTC";
					}`
			);
			resolve(true);
		});
	}

	public static async goToSettings() {
		return new Promise(async (resolve, reject) => {
			try {
				await browser.get('account-settings');
				resolve(true);
			} catch (err) {
				console.error("e2e goToSettings failed");
				reject("e2e goToSettings failed");
			}
		});
	}

	public static async resetProgressData() {
		return new Promise(async (resolve, reject) => {
			try {
				const resetBtn = element(by.id('reset-data-btn'));
				const passwordInput = element(by.id('reset-data-pwd'));
				const yesBtn = element(by.id('reset-data-yes-btn'));
				await resetBtn.click();
				await passwordInput.sendKeys(EtoEConsts.E2E_PWD);
				await yesBtn.click();
				resolve(true);
			} catch (err) {
				console.error("e2e reset of progress data failed");
				reject("e2e reset of progress data failed");
			}
		});
	}

	public static async setPathToEToE() {
		return new Promise(async (resolve, reject) => {
			try {
				const pathBtn = element(by.id('learningPath'));
				const e2eOption = element(by.id('path-7'));
				await browser.get('http://localhost:4200/learning-settings');
				await pathBtn.click();
				await e2eOption.click();
				resolve(true);
			} catch (err) {
				console.error("Setting path to e2e failed");
				reject("Setting path to e2e failed");
			}
		});
	}
};
