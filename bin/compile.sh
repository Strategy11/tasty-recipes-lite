#!/usr/bin/env bash

PLUGIN_NAME="tasty-recipes"
if [ -z "$1" ]
then
  ZIP_FILE_NAME="$PLUGIN_NAME"
else
  ZIP_FILE_NAME="$PLUGIN_NAME-$1"
fi

cd "$( dirname "$0" )"/../ && pwd

rm -f "$ZIP_FILE_NAME.zip"

echo "Copy plugin to temp"
mkdir -p "$PLUGIN_NAME-tmp/tasty-recipes/"
rsync -av . "$PLUGIN_NAME-tmp/$PLUGIN_NAME/" --exclude "$PLUGIN_NAME-tmp" --exclude tests --exclude .git --exclude .github --exclude composer.lock --quiet

echo "Move working directory to temp one"
cd "$PLUGIN_NAME-tmp/$PLUGIN_NAME"

echo "Start composer"
composer install

echo "Install npm packages"
npm install --silent

echo "Build assets"
npm run build --silent --force

echo "Build compressed file"
cd "../$PLUGIN_NAME/"
vendor/bin/wp dist-archive ./ "../$ZIP_FILE_NAME.zip" --allow-root

cd ../../
rm -rf "$PLUGIN_NAME-tmp"
