#!/bin/bash
NAME="yoti-wordpress-1.1.5-edge.zip"
SDK_RELATIVE_PATH="sdk"
curl https://github.com/getyoti/yoti-php-sdk/archive/master.zip -O -L
unzip master.zip -d sdk
mv sdk/yoti-php-sdk-master/src/* sdk
rm -rf sdk/yoti-php-sdk-master

if [ ! -d "./yoti" ]; then
    echo "ERROR: Must be in directory containing ./yoti folder"
    exit
fi

if [ ! -d "$SDK_RELATIVE_PATH" ]; then
    "ERROR: Could not find SDK in $SDK_RELATIVE_PATH"
    exit
fi

echo "Packing plugin ..."

# move sdk symlink (used in symlink-plugin-to-site.sh)
sym_exist=0
if [ -L "./yoti/sdk" ]; then
    mv "./yoti/sdk" "./__sdk-sym";
    sym_exist=1
fi

cp -R "$SDK_RELATIVE_PATH" "./yoti/sdk"
cp README.md "./yoti"
cp LICENSE "./yoti"
zip -r "$NAME" "./yoti"
rm -rf "./yoti/sdk"

# move symlink back
if [ $sym_exist ]; then
    mv "./__sdk-sym" "./yoti/sdk"
fi
rm -rf sdk
rm "./yoti/README.md"
rm "./yoti/LICENSE"
echo "Plugin packed. File $NAME created."
echo ""





