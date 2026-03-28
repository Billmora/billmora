#!/bin/sh
set -e

# Persistent path in the storage volume
ENV_STORAGE_DIR="/var/www/html/storage/app/config"
ENV_STORAGE_FILE="${ENV_STORAGE_DIR}/.env"
ENV_EXAMPLE="/var/www/html/.env.example"
# Link path in the app root
ENV_ROOT_LINK="/var/www/html/.env"

# 1. Ensure the persistent storage directory exists
mkdir -p "${ENV_STORAGE_DIR}"

# 2. Seed .env from example if it doesn't exist in the persistent storage
if [ ! -f "${ENV_STORAGE_FILE}" ]; then
    if [ -f "${ENV_EXAMPLE}" ]; then
        cp "${ENV_EXAMPLE}" "${ENV_STORAGE_FILE}"
    else
        touch "${ENV_STORAGE_FILE}"
    fi
fi

# 3. Remove .env if it was erroneously created as a directory (common on failed mounts)
if [ -d "${ENV_ROOT_LINK}" ]; then
    rm -rf "${ENV_ROOT_LINK}"
fi

# 4. Symlink the root .env to the persistent storage file
ln -sf "${ENV_STORAGE_FILE}" "${ENV_ROOT_LINK}"
