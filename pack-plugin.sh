#!/bin/bash
NAME="yoti-connect-wordpress-1.1.2-edge.zip"
SDK_RELATIVE_PATH="sdk"
curl https://github.com/getyoti/php/archive/master.zip -O -L
unzip master.zip -d sdk
mv sdk/php-master/src/* sdk
rm -rf sdk/php-master

if [ ! -d "./yoti-connect" ]; then
    echo "ERROR: Must be in directory containing ./yoti-connect folder"
    exit
fi

if [ ! -d "$SDK_RELATIVE_PATH" ]; then
    "ERROR: Could not find SDK in $SDK_RELATIVE_PATH"
    exit
fi

echo "Packing plugin ..."

# move sdk symlink (used in symlink-plugin-to-site.sh)
sym_exist=0
if [ -L "./yoti-connect/sdk" ]; then
    mv "./yoti-connect/sdk" "./__sdk-sym";
    sym_exist=1
fi

cp -R "$SDK_RELATIVE_PATH" "./yoti-connect/sdk"
cp README.md "./yoti-connect"
cp LICENSE "./yoti-connect"
zip -r "$NAME" "./yoti-connect"
rm -rf "./yoti-connect/sdk"

# move symlink back
if [ $sym_exist ]; then
    mv "./__sdk-sym" "./yoti-connect/sdk"
fi
rm -rf sdk
rm "./yoti-connect/README.md"
rm "./yoti-connect/LICENSE"
echo "Plugin packed. File $NAME created."
echo ""





