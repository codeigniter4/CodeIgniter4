#!/bin/bash

## Build user guide distributable

# Setup variables
. admin/release-config
TARGET=dist/userguide
cd $TARGET
git checkout $branch

#---------------------------------------------------
echo -e "${BOLD}Build the user guide distributable${NORMAL}"

cp -rf ${CI_DIR}/user_guide_src/build/html/* docs
cp -rf ${CI_DIR}/user_guide_src/build/epub/CodeIgniter4.epub ./CodeIgniter${RELEASE}.epub

#---------------------------------------------------
# And finally, get ready for merging
echo -e "${BOLD}Assemble the pieces...${NORMAL}"
git add .
git commit -S -m "Release ${RELEASE}"
git checkout master
git merge $branch

cd $CI_DIR

#---------------------------------------------------
# Done for now
echo -e "${BOLD}Distributable user guide ready..${NORMAL}"
