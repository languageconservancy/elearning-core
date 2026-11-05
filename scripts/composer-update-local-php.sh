#!/bin/bash
# Runs Composer on core/backend (default: update) using the PHP binary bundled with
# the local Apache stack (MAMP/XAMPP) pointed to by ELEARNING_WWW_PATH,
# instead of whatever `php`/`composer` happen to resolve to on PATH.
#
# Required:
# - ELEARNING_WWW_PATH in scripts/local-dev-vars.sh (language repo root)
#   or exported in the environment (WWW_PATH accepted as legacy fallback)

set -euo pipefail

readonly SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
readonly CORE_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"
readonly BACKEND_DIR="${CORE_DIR}/backend"
readonly PLATFORM_REPO_DIR="$(cd "${SCRIPT_DIR}/../.." && pwd)"
readonly LOCAL_DEV_VARS="${PLATFORM_REPO_DIR}/scripts/local-dev-vars.sh"

if [ -f "${LOCAL_DEV_VARS}" ]; then
    # shellcheck source=/dev/null
    source "${LOCAL_DEV_VARS}"
fi

WWW_DIR="${ELEARNING_WWW_PATH:-${WWW_PATH:-}}"

if [ -z "${WWW_DIR}" ]; then
    echo "❗Error: ELEARNING_WWW_PATH is not set."
    echo "Run: npm run core init-language-repo-vars"
    echo "Or copy core/scripts/examples/local-dev-vars.example.sh to scripts/local-dev-vars.sh in your language repo"
    echo "and set ELEARNING_WWW_PATH to your Apache document root (parent folder of backend/)."
    exit 1
fi

# ELEARNING_WWW_PATH may point at htdocs itself or a subfolder of it (e.g.
# .../htdocs/elearning), so walk up looking for the MAMP/XAMPP install root
# that owns that document root (the folder containing bin/php).
STACK_ROOT="${WWW_DIR}"
while [ "${STACK_ROOT}" != "/" ] && [ ! -e "${STACK_ROOT}/bin/php" ]; do
    STACK_ROOT="$(dirname "${STACK_ROOT}")"
done

if [ ! -e "${STACK_ROOT}/bin/php" ]; then
    echo "❗Error: Couldn't find a bin/php folder above ${WWW_DIR}."
    echo "Expected a MAMP or XAMPP install root above your ELEARNING_WWW_PATH."
    exit 1
fi

if [ -d "${STACK_ROOT}/bin/php/php" ]; then
    # MAMP: bin/php/php is a symlink MAMP repoints at whichever PHP version
    # is currently selected in MAMP's preferences (e.g. php8.3.30).
    PHP_BIN="${STACK_ROOT}/bin/php/php/bin/php"
else
    # XAMPP: a single bundled PHP binary directly under bin/.
    PHP_BIN="${STACK_ROOT}/bin/php"
fi

if [ ! -x "${PHP_BIN}" ]; then
    echo "❗Error: PHP binary not found or not executable at ${PHP_BIN}"
    exit 1
fi

echo "Using PHP from ${STACK_ROOT}:"
"${PHP_BIN}" -v
"${PHP_BIN}" -r 'if (PHP_VERSION_ID < 80300) { fwrite(STDERR, "Error: The backend requires PHP >=8.3; select PHP 8.3 or newer in your local stack.\n"); exit(1); }'

# Bypass Composer's shebang and keep PHP subprocesses on the same runtime.
export PATH="$(dirname "${PHP_BIN}"):${PATH}"
if [ -f "${STACK_ROOT}/bin/php/composer" ]; then
    COMPOSER_BIN="${STACK_ROOT}/bin/php/composer"
else
    COMPOSER_BIN="$(command -v composer || true)"
fi

if [ -z "${COMPOSER_BIN}" ] || [ ! -r "${COMPOSER_BIN}" ]; then
    echo "❗Error: composer not found on PATH or at ${STACK_ROOT}/bin/php/composer"
    exit 1
fi

if [ "$#" -eq 0 ]; then
    set -- update --no-interaction
fi

echo -e "\nRunning composer $* in ${BACKEND_DIR}"
cd "${BACKEND_DIR}"
"${PHP_BIN}" "${COMPOSER_BIN}" "$@"

echo -e "✅ composer finished using $("${PHP_BIN}" -r 'echo PHP_VERSION;')"
