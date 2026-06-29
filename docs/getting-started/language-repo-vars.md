# Language repo configuration files

Templates for machine-local and deploy settings live in **core**; each language repo keeps its own copies under **`scripts/`** at the repo root (next to `core/` and `platform/`).

## Where files live

| | Template (core, committed) | Your copy (language repo) | Git in language repo |
|---|---------------------------|---------------------------|----------------------|
| Local Apache root | `core/scripts/examples/local-dev-vars.example.sh` | `scripts/local-dev-vars.sh` | Gitignore |
| Deploy targets | `core/scripts/examples/deploy-vars.example.sh` | `scripts/deploy-vars.sh` | Commit |

**Do not** copy the `.example.sh` files into the language repo — only the targets without `.example` in the name.

## Create missing files (recommended)

From the language repo root:

```bash
npm run core init-language-repo-vars
```

Or in Cursor/VS Code: **Terminal → Run Task…** → **eLearning: Init language repo vars**.

This copies from `core/scripts/examples/` only when `scripts/local-dev-vars.sh` or `scripts/deploy-vars.sh` **do not exist**. Existing files are never overwritten.

## Manual copy

From the language repo root:

```bash
mkdir -p scripts
cp core/scripts/examples/local-dev-vars.example.sh scripts/local-dev-vars.sh
cp core/scripts/examples/deploy-vars.example.sh scripts/deploy-vars.sh   # new platforms only
```

## After creating

**`scripts/local-dev-vars.sh`** — set `ELEARNING_WWW_PATH` to your Apache document root. See [local-server-setup.md](local-server-setup.md#local-dev-variables).

**`scripts/deploy-vars.sh`** — set staging/production server users, document roots, and SSH hosts for **this** language app. Commit to the language repo. Secrets (passwords, API keys) belong in `platform/config/*/.env`, not here.

## Who reads these files

| File | Used by |
|------|---------|
| `scripts/local-dev-vars.sh` | `core/scripts/sync-local-backend.sh` |
| `scripts/deploy-vars.sh` | `core/scripts/deploy.sh` |

Both scripts resolve paths relative to the language repo root (`elearning-<app-name>/`).

## Same developer, many language repos

`ELEARNING_WWW_PATH` is usually the same on one machine (e.g. MAMP `htdocs`). You need a `scripts/local-dev-vars.sh` in each language repo clone — run `init-language-repo-vars` once per repo.

Deploy vars differ per platform; each language repo has its own committed `scripts/deploy-vars.sh`.

## AI / agent reference

[init-language-repo-vars SKILL.md](../../.cursor/skills/init-language-repo-vars/SKILL.md)
