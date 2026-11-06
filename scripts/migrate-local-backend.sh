#!/bin/bash

set -euo pipefail

readonly SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
readonly BACKEND_DIR="$(cd "${SCRIPT_DIR}/../backend" && pwd)"
readonly LOCAL_DEV_VARS="${SCRIPT_DIR}/../../scripts/local-dev-vars.sh"

if [ -f "${LOCAL_DEV_VARS}" ]; then
    # shellcheck source=/dev/null
    source "${LOCAL_DEV_VARS}"
fi

STACK_ROOT="${ELEARNING_WWW_PATH:-${WWW_PATH:-}}"
if [ -z "${STACK_ROOT}" ]; then
    echo "Error: Set ELEARNING_WWW_PATH in scripts/local-dev-vars.sh to your local Apache document root." >&2
    exit 1
fi

while [ "${STACK_ROOT}" != "/" ] && [ ! -e "${STACK_ROOT}/bin/php" ]; do
    STACK_ROOT="$(dirname "${STACK_ROOT}")"
done

if [ -d "${STACK_ROOT}/bin/php/php" ]; then
    PHP_BIN="${STACK_ROOT}/bin/php/php/bin/php"
else
    PHP_BIN="${STACK_ROOT}/bin/php"
fi

if [ ! -x "${PHP_BIN}" ] || [ -d "${PHP_BIN}" ]; then
    echo "Error: Could not find the selected local MAMP/XAMPP PHP runtime." >&2
    exit 1
fi

case "${1:-}" in
    status|migrate) ;;
    *)
        echo "Usage: bash scripts/migrate-local-backend.sh status|migrate [migration options]" >&2
        exit 1
        ;;
esac

export PATH="$(dirname "${PHP_BIN}"):${PATH}"
cd "${BACKEND_DIR}"

"${PHP_BIN}" -r '
if (PHP_VERSION_ID < 80300) {
    fwrite(STDERR, "Error: Select PHP 8.3 or newer in your local stack.\n");
    exit(1);
}
require "vendor/autoload.php";
require "config/bootstrap.php";
$config = \Cake\Datasource\ConnectionManager::get("default")->config();
$host = $config["host"] ?? "";
if (!in_array($host, ["localhost", "127.0.0.1", "::1"], true)) {
    fwrite(STDERR, "Error: Refusing migration: the default database host is not local.\n");
    exit(1);
}
if (empty($config["database"])) {
    fwrite(STDERR, "Error: No default database is configured.\n");
    exit(1);
}
printf("Using PHP %s; local database %s at %s\n", PHP_VERSION, $config["database"], $host);
'

exec "${PHP_BIN}" "${BACKEND_DIR}/bin/cake.php" migrations "$@" --connection default
