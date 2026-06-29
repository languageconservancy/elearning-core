#!/bin/bash
# Copies core/backend to the local Apache document root (MAMP/XAMPP).
# Lives alongside deploy.sh and other core/scripts tooling.
#
# Required:
# - ELEARNING_WWW_PATH in scripts/local-dev-vars.sh (language repo root)
#   or exported in the environment (WWW_PATH accepted as legacy fallback)

readonly SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
readonly CORE_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"
readonly BACKEND_DIR="${CORE_DIR}/backend"
readonly PLATFORM_REPO_DIR="$(cd "${SCRIPT_DIR}/../.." && pwd)"
readonly LOCAL_DEV_VARS="${PLATFORM_REPO_DIR}/scripts/local-dev-vars.sh"

if [ -f "${LOCAL_DEV_VARS}" ]; then
    # shellcheck source=/dev/null
    source "${LOCAL_DEV_VARS}"
fi

SERVER_DIR="${ELEARNING_WWW_PATH:-${WWW_PATH}}"

if [ -z "${SERVER_DIR}" ]; then
    echo "❗Error: ELEARNING_WWW_PATH is not set."
    echo "Copy scripts/local-dev-vars.example.sh to scripts/local-dev-vars.sh in your language repo"
    echo "and set ELEARNING_WWW_PATH to your Apache document root (parent folder of backend/)."
    exit 1
fi

BACKEND_FILES_TO_RSYNC="config info src templates webroot index.php info.php .htaccess web.config vendor"

echo -e "Rsyncing the following files\nfrom ${BACKEND_DIR} to ${SERVER_DIR}:\n${BACKEND_FILES_TO_RSYNC}"

mkdir -p "${SERVER_DIR}/backend"
cd "${BACKEND_DIR}"
rsync -a ${BACKEND_FILES_TO_RSYNC} --exclude="config/.env" "${SERVER_DIR}/backend/"
rsync -a info "${SERVER_DIR}/"

rm -rf "${SERVER_DIR}/backend/tmp/cache/models/"*
rm -rf "${SERVER_DIR}/backend/tmp/cache/persistent/"*
rm -rf "${SERVER_DIR}/backend/tmp/cache/views/"*

echo -e "✅ Local server backend synced successfully"
