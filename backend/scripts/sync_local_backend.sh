#!/bin/bash
# This script is used to update the local backend of the MAMP server.
# It copies files from core/backend to the web root of the local MAMP server.
#
# Required environment variables:
# - WWW_PATH
#    - This is the path to the web root of the local MAMP server

# The server directory is the web root of the local MAMP server
SERVER_DIR=${WWW_PATH}

# Display error to user if WWW_PATH is not set
if [ -z "${WWW_PATH}" ]; then
    echo "❗Error: WWW_PATH is not set. Please set the WWW_PATH environment variable to the path to the web root of the local MAMP server."
    exit 1
fi

# Get the directory where this script is located
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Go up one level to get the backend directory
BACKEND_REPO_DIR="$(dirname "$SCRIPT_DIR")"

# The files to rsync are the ones that are needed to run the backend
BACKEND_FILES_TO_RSYNC="config info src templates webroot index.php info.php .htaccess web.config vendor"

echo -e "Rsyncing the following files\nfrom ${BACKEND_REPO_DIR} to ${SERVER_DIR}:\n${BACKEND_FILES_TO_RSYNC}"

# Create the backend directory if it doesn't exist
mkdir -p ${SERVER_DIR}/backend
cd ${BACKEND_REPO_DIR}
rsync -a ${BACKEND_FILES_TO_RSYNC} ${SERVER_DIR}/backend/
rsync -a info ${SERVER_DIR}/

# Clear the cache directories
rm -rf ${SERVER_DIR}/backend/tmp/cache/models/*
rm -rf ${SERVER_DIR}/backend/tmp/cache/persistent/*
rm -rf ${SERVER_DIR}/backend/tmp/cache/views/*

echo -e "✅ Local server backend synced successfully"
