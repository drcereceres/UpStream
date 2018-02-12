#!/bin/bash

PLUGIN_NAME=$1
PLUGIN_SLUG=$2
NEW_VERSION=$3
BUILDS_DIR=$4

# Creates the packages path if it doesn't exists.
echo "Checking /builds directory..."
if [ ! -d $BUILDS_DIR ]; then
  echo "  Creating /builds directory...";
  mkdir -p $BUILDS_DIR;
fi

NEW_PACKAGE_NAME="$PLUGIN_NAME-v$NEW_VERSION"
NEW_PACKAGE_FOLDER_NAME="$PLUGIN_SLUG"

cd $BUILDS_DIR
rm -rf "$NEW_PACKAGE_NAME.zip"
rm -rf $NEW_PACKAGE_FOLDER_NAME
cd ..
cp -rf src "builds/$NEW_PACKAGE_FOLDER_NAME"
cd "builds"

echo "Removing nasty system files..."
FILES_BACKLIST=(".DS_Store" ".AppleDouble" ".LSOverride" "Icon" "._*" ".DocumentRevisions-V100" ".fseventsd" ".Spotlight-V100" ".TemporaryItems" ".Trashes" ".VolumeIcon.icns" ".com.apple.timemachine.donotpresent" ".AppleDB" ".AppleDesktop" ".apdisk")
for blackListedFileName in "${FILES_BACKLIST[@]}"
do
  find "./$NEW_PACKAGE_FOLDER_NAME" -name "$blackListedFileName" -type f -delete
done

echo "Creating \"$NEW_PACKAGE_FOLDER_NAME.zip\" file..."
zip -q -r "$NEW_PACKAGE_FOLDER_NAME.zip" $NEW_PACKAGE_FOLDER_NAME

echo "Removing temporary folder..."
rm -rf $NEW_PACKAGE_FOLDER_NAME

echo "Renaming \"$NEW_PACKAGE_FOLDER_NAME.zip\" to \"$NEW_PACKAGE_NAME.zip\""
mv $NEW_PACKAGE_FOLDER_NAME.zip $NEW_PACKAGE_NAME.zip

echo "Build \"v$NEW_VERSION\" complete!"
