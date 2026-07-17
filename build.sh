#!/usr/bin/env bash
# Builds the plugin for production and packages the runtime files in a zip.
#
# Usage: $ bash ./build.sh
# Make sure to run this script from the root of the plugin directory.

set -euo pipefail

PLUGIN_NAME="dock26-cookies"

# Check if the script is being run from the root of the plugin directory
if [ ! -f "${PLUGIN_NAME}.php" ]; then
    echo "This script must be run from the root of the plugin directory."
    exit 1
fi

# Build the admin and frontend bundles
pnpm --dir admin install --frozen-lockfile
pnpm --dir admin build
pnpm --dir frontend install --frozen-lockfile
pnpm --dir frontend build

# Package only the runtime files
rm -f "${PLUGIN_NAME}.zip"
zip -r "${PLUGIN_NAME}.zip" \
    "${PLUGIN_NAME}.php" \
    includes \
    admin/dist \
    frontend/dist

echo "Build successful! Created ${PLUGIN_NAME}.zip"
