// Protractor configuration file, see link for more information
// https://github.com/angular/protractor/blob/master/lib/config.ts
/*
	https://stackoverflow.com/questions/42648077/how-does-waitforangularenabled-work
 */

const { SpecReporter } = require('jasmine-spec-reporter');

exports.config = {
	allScriptsTimeout: 120000,
	specs: [
		'./e2e/**/*.e2e-spec.ts'
	],
	capabilities: {
		'browserName': 'chrome'
	},
	SELENIUM_PROMISE_MANAGER: false,
	directConnect: true,
	baseUrl: 'http://localhost:4200/',
	framework: 'jasmine',
	jasmineNodeOpts: {
		showColors: true,
		defaultTimeoutInterval: 120000,
		isVerbose: true,
		print: function() {}
	},
	onPrepare() {
		require('ts-node').register({
			project: 'e2e/tsconfig.e2e.json'
		});
		jasmine.getEnv().addReporter(new SpecReporter({ spec: { displayStacktrace: "pretty" } }));
		browser.driver.manage().window().maximize();
	}
};
