Description
===
This backend package contains two features in a single project.
1. An admin panel interface allowing database manipulation.
1. An API for the frontend endpoints.

### Admin UI and local Apache setup

The admin UI uses the `maiconpinto/cakephp-adminlte-theme` CakePHP plugin.
AdminLTE provides the presentation layer; the admin controllers, authorization,
and application menus remain in this project.

Apache must load `mod_rewrite` and allow the backend's `.htaccess` rules
(`AllowOverride All` on the document-root directory). In MAMP, check
`/Applications/MAMP/conf/apache/httpd.conf` for:

```apache
LoadModule rewrite_module modules/mod_rewrite.so
```

Validate the Apache configuration and restart Apache after changing it.
MAMP may regenerate this configuration when server settings change.

If `/backend/` renders an unstyled login page but
`/backend/admin_l_t_e/css/AdminLTE.min.css` and
`/backend/admin/users/login` return Apache 404 pages, check rewriting before
changing Composer dependencies. CakePHP's asset middleware serves the plugin
assets from `vendor/`; these requests must reach the front controller.
Verify that the login URL and its local CSS/JavaScript URLs return HTTP 200.

For menu and layout redesigns, edit the application overrides in
[`templates/plugin/AdminLTE/`](../../backend/templates/plugin/AdminLTE/),
especially
[`element/aside/sidebar-menu.php`](../../backend/templates/plugin/AdminLTE/element/aside/sidebar-menu.php)
and [`layout/default.php`](../../backend/templates/plugin/AdminLTE/layout/default.php).
Keep role-based menu visibility and controller authorization intact. Changes
belong in these application templates, not in `vendor/`, and must be synced to
the local server before testing.

Configuration
===

### Config/Language Selection
This package is used in the eLearning Platform core submodule, which supports multiple languages/sites.

Run this command and follow the prompts to select the desired config in ./config/ and have it copied to app.php.
* `composer run set-config`
or
* `composer run set-config app.<language>-<environment>.php`

### Language-Specificity
Configuration files in config/app.\*php contain settings and constants specific to each language or environment.

Scripts
===

Reference SQL and ad-hoc database helpers live in [`core/scripts/sql/`](../scripts/sql/) — see [sql/README.md](../scripts/sql/README.md). Those files informed [migrations.md](migrations.md); use CakePHP migrations for production schema changes.

Server-side PHP utilities (card export, content migration) live in [`core/scripts/php/`](../scripts/php/) — see [php/README.md](../scripts/php/README.md).

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
