Description
===
This repo contains two features in a single project.
1. An admin panel interface allowing database manipulation.
1. An API for the front-end endpoints.

Configuration
===

### Config/Language Selection
This repo is used in the E-Learning Platform, which supports multiple languages/sites.

Run this command and follow the prompts to select the desired config in ./config/ and have it copied to app.php.
* `composer run set-config`
or
* `composer run set-config app.<language>-<environment>.php`

### Language-Specificity
Configuration files in config/app.\*php contain settings and constants specific to each language or environment.

Tests
===

### Unit Tests
Unit tests are defined in the ./tests directory.

Run this command to run these tests:
* `composer run test`

Coding Standard
===

### PSR12 (PHP Standard Recommendation 12)
The PHP code in this repo should adhere to PSR12.

To ensure this, PHP CodeSniffer is run on all `git commit` commands.

Git Hooks
===

### Pre-Commit
A git pre-commit hook is enabled using the [composer-git-hooks](https://github.com/BrainMaestro/composer-git-hooks) package.

It is defined in `composer.json` and runs PHP CodeSniffer and unit tests. If either fails, the commit won't happen.
