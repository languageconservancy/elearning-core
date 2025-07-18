import { browser, by, element } from 'protractor';

export class LoginPage {
	public static submitButton = element(by.buttonText('Submit'));

	navigateTo() {
		return browser.get('/');
	}

	getTitle() {
		return element(by.css('title')).getText();
	}

	getSocialLoginButtons() {
		return element.all(by.css('.seoc-btn'))
			.then(btns => {
				return btns;
			}).catch(error => {
				console.error(error);
				return null;
			});
	}
}
