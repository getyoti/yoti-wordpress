#!/bin/bash -x
#####################
# this script puts the latest SDK in the working plugin directory
#####################

NAME="yoti-for-wordpress-edge.zip"
SDK_TAG=$1
DEFAULT_SDK_TAG="2.0.0"

if [ "$SDK_TAG" = "" ]; then
    SDK_TAG=$DEFAULT_SDK_TAG
fi

echo "Pulling PHP SDK TAG $SDK_TAG.zip ..."

curl https://github.com/getyoti/yoti-php-sdk/archive/$SDK_TAG.zip -O -L
unzip $SDK_TAG.zip -d sdk
mv sdk/yoti-php-sdk-$SDK_TAG/src/* sdk
rm -rf sdk/yoti-php-sdk-$SDK_TAG

if [ ! -d "./yoti" ]; then
    echo "ERROR: Must be in directory containing ./yoti folder"
    exit
fi

cp -R sdk "./yoti/sdk"
zip -r "$NAME" "./yoti"

rm -rf sdk
echo "Fetched PHP SDK TAG $SDK_TAG."
echo ""
