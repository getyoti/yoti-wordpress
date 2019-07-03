#!/bin/bash
NAME="yoti-wordpress-edge.zip"

SDK_TAG=$1
BIN_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
BASE_DIR="$BIN_DIR/.."

# Ensure SDK is checked out.
$BIN_DIR/checkout-sdk.sh $1

echo "Packing plugin ..."

cd $BASE_DIR
cp "./LICENSE" "./yoti"
zip -r "$NAME" "./yoti/"
rm "./yoti/LICENSE"
cd -

echo "Plugin packed. File $NAME created."
echo ""
