---
name: init-language-repo-vars
description: >-
  Create scripts/local-dev-vars.sh and scripts/deploy-vars.sh in a language
  repo from core/templates when missing. Use after cloning a language repo,
  bootstrapping a new platform, or when sync-local-backend or deploy fails
  because var files are missing. Also when the user asks about deploy-vars,
  local-dev-vars, ELEARNING_WWW_PATH setup, or init-language-repo-vars.
---

# Init language repo vars

## When to run

- Fresh clone of a language repo (`elearning-<app-name>/`)
- New platform forked from template without `scripts/deploy-vars.sh` yet
- `sync-local-backend` errors: `ELEARNING_WWW_PATH is not set`
- User asks how to set up `scripts/local-dev-vars.sh` or `scripts/deploy-vars.sh`

## Command

From **language repo root**:

```bash
npm run core init-language-repo-vars
```

VS Code task: **eLearning: Init language repo vars**

## What it does

Copies from `core/scripts/examples/` to `scripts/` in the language repo **only if the target file does not exist**:

| Template | Creates |
|----------|---------|
| `core/scripts/examples/local-dev-vars.example.sh` | `scripts/local-dev-vars.sh` |
| `core/scripts/examples/deploy-vars.example.sh` | `scripts/deploy-vars.sh` |

Never overwrites existing files.

## After running

1. Edit `scripts/local-dev-vars.sh` — set `ELEARNING_WWW_PATH` (MAMP/XAMPP htdocs path)
2. Edit `scripts/deploy-vars.sh` if created — set platform-specific deploy paths; **commit** to language repo
3. Continue setup per [developing.md](../../docs/getting-started/developing.md)

Full reference: [language-repo-vars.md](../../docs/getting-started/language-repo-vars.md)
