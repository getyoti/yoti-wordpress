#!/bin/bash -x
#####################
# this script puts the latest SDK in the working plugin directory
#####################

NAME="yoti-for-wordpress-edge.zip"
SDK_TAG=$1
DEFAULT_SDK_TAG="2.3.0"
BASE_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )/.."
PLUGIN_DIR="$BASE_DIR/yoti"

if [ "$SDK_TAG" = "" ]; then
    SDK_TAG=$DEFAULT_SDK_TAG
fi

echo "Pulling PHP SDK TAG $SDK_TAG.zip ..."

curl https://github.com/getyoti/yoti-php-sdk/archive/$SDK_TAG.zip -O -L
unzip $SDK_TAG.zip -d sdk
mv sdk/yoti-php-sdk-$SDK_TAG/src/* sdk
rm -rf sdk/yoti-php-sdk-$SDK_TAG

if [ ! -d "$BASE_DIR" ]; then
    echo "ERROR: Must be in directory containing ./yoti folder"
    exit
fi

rm -fr "$PLUGIN_DIR/sdk"
mv sdk "$PLUGIN_DIR/sdk"
zip -r "$NAME" "$PLUGIN_DIR"

echo "Fetched PHP SDK TAG $SDK_TAG."
echo ""
