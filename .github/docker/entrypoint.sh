#!/bin/sh
set -e

# Persistent path in the storage volume
ENV_STORAGE_DIR="/var/www/html/storage/app/config"
ENV_STORAGE_FILE="${ENV_STORAGE_DIR}/.env"
ENV_EXAMPLE="/var/www/html/.env.example"
# Link path in the app root
ENV_ROOT_LINK="/var/www/html/.env"

# 1. Ensure the persistent storage directory exists and fix volume ownership
mkdir -p "${ENV_STORAGE_DIR}"
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 2. Seed .env from example if it doesn't exist in the persistent storage
if [ ! -f "${ENV_STORAGE_FILE}" ]; then
    if [ -f "${ENV_EXAMPLE}" ]; then
        cp "${ENV_EXAMPLE}" "${ENV_STORAGE_FILE}"
        echo "[billmora] .env created from .env.example"
    else
        touch "${ENV_STORAGE_FILE}"
        echo "[billmora] empty .env created"
    fi
fi

# 3. Ensure proper permissions for the original file
chown www-data:www-data "${ENV_STORAGE_FILE}"
chmod 664 "${ENV_STORAGE_FILE}"

# 4. Cleanup and Symlink
if [ -d "${ENV_ROOT_LINK}" ]; then
    rm -rf "${ENV_ROOT_LINK}"
    echo "[billmora] cleaned up old .env directory"
fi

ln -sf "${ENV_STORAGE_FILE}" "${ENV_ROOT_LINK}"
chown -h www-data:www-data "${ENV_ROOT_LINK}"
echo "[billmora] .env symlinked to storage successfully"
