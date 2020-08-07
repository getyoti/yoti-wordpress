#!/bin/sh

PLUGIN_SLUG="yoti-wordpress-edge"
BIN_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PROJECT_PATH="$BIN_DIR/.."
BUILD_PATH="${PROJECT_PATH}/build"
DEST_PATH="$BUILD_PATH/$PLUGIN_SLUG"

echo "Generating build directory..."
rm -rf "$BUILD_PATH"
mkdir -p "$DEST_PATH"

echo "Installing PHP prod dependencies..."
composer install --no-dev || exit "$?"

echo "Syncing files..."
rsync -rc --exclude-from="$PROJECT_PATH/.distignore" "$PROJECT_PATH/" "$DEST_PATH/" --delete --delete-excluded

echo "Restoring PHP dev dependencies..."
composer install || exit "$?"

echo "Generating zip file..."
cd "$BUILD_PATH" || exit
zip -q -r "${PLUGIN_SLUG}.zip" "$PLUGIN_SLUG/"
echo "$BUILD_PATH/${PLUGIN_SLUG}.zip file created"

echo ""
