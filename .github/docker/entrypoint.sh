#!/bin/sh
# Ensures /var/www/html/.env exists on container boot.
# Seeds from .env.example on first run; preserves existing file on restarts.
set -e

ENV_FILE="/var/www/html/.env"
ENV_EXAMPLE="/var/www/html/.env.example"

if [ ! -f "${ENV_FILE}" ]; then
    if [ -f "${ENV_EXAMPLE}" ]; then
        cp "${ENV_EXAMPLE}" "${ENV_FILE}"
    else
        touch "${ENV_FILE}"
    fi
fi
