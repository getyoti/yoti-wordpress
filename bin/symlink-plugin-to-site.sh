#!/bin/bash
#####################
# this script symlinks to the plugin dirs to the correct places in wordpress in order to build
#####################
BASE=$1

if [ ! -d "$BASE" ]; then
    echo "$BASE not found."
    echo "$0 <wordpress site>"
    exit
fi

target="$PWD/yoti"
link="$BASE/wp/plugins/yoti"

# if link already exists then don't create
if [ ! -L "$link" ]; then

    # if already installed plugin then move old dir
    if [ -d "$link" ]; then mv "$link" "$link.old"; fi

    # create link
    ln -s "$target" "$link"
fi

# add sdk
target=$(realpath ../../src)
link="$link/sdk"
if [ ! -L "$link" ]; then
    ln -s "$target" "$link"
fi