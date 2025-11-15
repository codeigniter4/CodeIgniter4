#!/bin/bash

## Deploy codeigniter4/userguide

# Setup variables
SOURCE=$1
TARGET=$2
RELEASE=$3

# Check if RELEASE is empty
if [ -z "$RELEASE" ]; then
    echo "Error: \$RELEASE parameter is empty."
    exit 1
fi

VERSION=$(echo "$RELEASE" | cut -c 2-)

# Check if VERSION is empty
if [ -z "$VERSION" ]; then
    echo "Error: Failed to extract \$VERSION from \$RELEASE parameter '$RELEASE'."
    exit 1
fi

# Check if VERSION matches the format X.Y.Z
if ! [[ "$VERSION" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
    echo "Error: VERSION '$VERSION' does not match the expected format X.Y.Z."
    exit 1
fi

echo "Preparing for version $3"
echo "Merging files from $1 to $2"

# Prepare the source
cd $SOURCE
git checkout master
cd user_guide_src
make html
make epub

# Prepare the target
cd $TARGET
git checkout master
rm -rf docs

# Copy common files
cp -Rf ${SOURCE}/LICENSE ./

# Copy repo-specific files
cp -Rf ${SOURCE}/admin/userguide/. ./

# Copy files
cp -Rf ${SOURCE}/user_guide_src/build/html ./docs
cp -Rf ${SOURCE}/user_guide_src/build/epub/CodeIgniter.epub ./CodeIgniter${VERSION}.epub

# Ensure underscore prefixed files are published
touch ${TARGET}/docs/.nojekyll

# Commit the changes
git add .
git commit -m "Release ${RELEASE}"
git push
