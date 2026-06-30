# Documentation

Index for eLearning Core documentation. The [main README](../README.md) is the GitHub landing page — overview, quick start, and links into the guides below.

## Getting started

| Doc | Description |
|-----|-------------|
| [Developing on a platform](getting-started/developing.md) | Daily workflow, VS Code workspace, tasks, and troubleshooting |
| [Local server setup](getting-started/local-server-setup.md) | MAMP or XAMPP — Apache, PHP 7.4, MySQL, document root, `ELEARNING_WWW_PATH` |
| [Language repo vars](getting-started/language-repo-vars.md) | `local-dev-vars.sh`, `deploy-vars.sh` — templates in core, copies in language repo |
| [Main README](../README.md) | Overview, quick start, npm commands, repo structure |

## Backend

| Doc | Description |
|-----|-------------|
| [Backend overview](backend/README.md) | Admin panel, API, config, tests, coding standards, git hooks |
| [Database migrations](backend/migrations.md) | CakePHP migrations — run, rollback; reference SQL in [scripts/sql/](scripts/sql/README.md) |

## Frontend

| Doc | Description |
|-----|-------------|
| [Frontend overview](frontend/README.md) | Angular app, multi-platform builds, Capacitor, tests |
| [Social login setup](frontend/social-login.md) | Facebook, Google, and Apple login configuration |
| [Default assets (legacy)](frontend/default-assets.md) | Historical language-asset layout reference |

## Demo environment

| Doc | Description |
|-----|-------------|
| [Demo database & assets](demo/README.md) | Importing `elearning_demo_db.sql`, webroot assets, MAMP `my.cnf` |

## Build scripts

| Doc | Description |
|-----|-------------|
| [Template generator utility](scripts/template-generator.md) | Shared Handlebars template generation (`template-generator.js`) |
| [Index.html templates](scripts/index-html-templates.md) | Generating `index.html` from `index-meta.json` |
| [Platform assets system](scripts/platform-assets.md) | Copying platform assets and generating Android/iOS files |

## Utility scripts

| Doc | Description |
|-----|-------------|
| [PHP scripts](scripts/php/README.md) | Server-side utilities — card export, content migration (`scripts/php/`) |
| [SQL scripts](scripts/sql/README.md) | Reference SQL and ad-hoc queries (`scripts/sql/`) |

## Other

| Doc | Description |
|-----|-------------|
| [Changelog](../CHANGELOG.md) | Release history |

## AI / agent skills

Cursor skills with workflow reference (not end-user docs):

- [Daily development](../.cursor/skills/elearning-daily-dev/SKILL.md)
- [Bootstrap new platform](../.cursor/skills/bootstrap-new-platform/SKILL.md)
- [Init language repo vars](../.cursor/skills/init-language-repo-vars/SKILL.md)
