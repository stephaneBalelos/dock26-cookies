# This script is used to build the plugin for production.
# It will package the theme files in a zip file

# Usage: $ bash ./build.sh
# Make sure to run this script from the root of the plugin directory

PLUGIN_NAME="dock26-cookies"

# Check if the script is being run from the root of the plugin directory
if [ ! -f "${PLUGIN_NAME}.php" ]; then
    echo "This script must be run from the root of the plugin directory."
    exit 1
fi

# Build the assets
pnpm build

# Create a zip file of the build directory
zip -r "${PLUGIN_NAME}.zip" . -x "node_modules/*"

# Check if the zip command was successful
if [ $? -eq 0 ]; then
    echo "Build successful! Created ${PLUGIN_NAME}.zip"
else
    echo "Build failed!"
    exit 1
fi

