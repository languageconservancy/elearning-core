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

### Vocab Builder authoring and review

Apply [the Vocab Builder migration](../../backend/config/Migrations/20261105000000_AddVocabBuilderExercises.php)
and [target-mode migration](../../backend/config/Migrations/20261106000000_AddVocabBuilderTargetMode.php)
before serving the updated app; see [migration instructions](migrations.md).
Create/name an exercise as usual, select **Vocab Builder** as its exercise type,
then select an activity and its supported word/letter version. Attach the exercise to units
using the existing learning-path editor.

The editor only shows fields needed by that version. Each response has a role
menu: target/correct response or other response/distractor. Exactly one is the
target; selecting a new target updates the other response menus automatically.
Word versions use searchable word cards; letter versions use letter text
(including multi-character letters) and pronunciation audio, without requiring
word cards. Use the file-library pickers for audio and Coloring line art.
Fill Letter uses one target word card plus letter responses, not a word card for
every distractor. Word-only activities do not request letter attachments.
Library word/translation strings are plain text; card HTML formatting is removed
from activity content without changing the tracked source card.
Fill Letter inserts brackets around the first occurrence of the letter by default;
the optional marked-word field can choose a different occurrence without changing
the underlying word. No automatic alphabet/phoneme inference is performed.

Minimum distinct content:

| Activity / version | Required content |
| --- | --- |
| Bird / word | 2 distinct word cards with text, audio and images |
| Coloring / word | Target word card with text and audio, plus word line art |
| Coloring / letter | Target letter, pronunciation audio and letter line art |
| Fill Letter / word | 1 target word card with text, audio and image; 3 distinct letters with audio. The target word contains its missing letter |
| Match Sound / word | 3 distinct word cards with text and audio; no letter audio or word images |
| Match Sound / letter | 3 distinct letters with audio; no word cards |
| Memory / letter | 8 distinct letters with audio, supporting all offered 8/12/16-card grids |
| Select Grid / letter | 2 distinct letters with audio |
| Word List / word | 1 or more word cards with text, audio and images; explicit Continue |

Only native library variants are offered: Bird and Word List use words; Fill
Letter fills a missing letter in a word; Memory and Select Grid use letters.
Coloring and Match Sound offer separate word and letter versions.

The `exercises` table stores `exercise_type = vocab-builder`, `vbl_activity` and
`vbl_mode` (`word`/`letter`) and the opt-in `vbl_review_enabled` flag.
The additive mode migration leaves existing exercises unchanged: a null mode
defaults to letters for Memory/Select Grid and words for other activities.
`exercise_options` stores nullable card references,
`P`/`O` target/content roles, `vbl_letter`, letter media and line-art file IDs.
The existing `text_option` stores an optional marked word. A save validates the
whole pool and atomically replaces exercise/options and updates unit-card content.

The [shared payload builder](../../backend/src/Lib/VocabBuilder.php) produces one
tracked question per authored activity, with its target option and optional card.
Letter-only targets have a null card ID and an empty experienced-card list, but
still save completion against the exercise and target option.
The `vocab_builder` object contains `activity`, `mode`, `content`, `config`, `card_ids` and
`target_word_index` within the selected letter's words. The index disambiguates
cards with identical word text and lets the frontend restore the target's object
identity after JSON serialization (required by Coloring). It is null for letter targets.
Both the lesson exercise API and unit-download API use this builder, so authored
content and asset URLs are identical. Invalid/missing media causes an explicit API
error rather than a broken success response.

Generated word review is **opt-in per word exercise** and uses only exercises assigned to a
unit. At most one generated Vocab Builder activity is placed in each review batch,
keeping the existing batch limit and legacy activities for the other slots.
The target must already be in the learner's eligible review queue, and unit
review only uses pools assigned to that unit. An authored distractor can become a
review target. Coloring candidates without line art are ineligible, and Word List
is always excluded. New letter-only pools have no word cards to add to word review;
the editor hides that option. Existing card-backed letter pools remain readable.
Invalid authored pools are logged and skipped. Completion
counts as correct practice for review unlocks but awards no measured skill XP,
because the library does not expose incorrect-answer scoring.

The [focused backend tests](../../backend/tests/TestCase/Lib/VocabBuilderTest.php)
cover all nine supported activity/version combinations, version-specific
content thresholds, cardless letter targets, attachments, multi-character letters, transaction
rollback, migration reversal, queue/unit review eligibility and API/download parity:

```bash
# From core/backend, using a PHP 8.3 runtime compatible with installed dependencies
php vendor/bin/phpunit --no-configuration --do-not-cache-result \
  --bootstrap tests/bootstrap.php tests/TestCase/Lib/VocabBuilderTest.php
```

The migration/persistence tests use isolated in-memory SQLite databases; they do
not migrate or write to the configured application database.

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
