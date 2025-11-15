#!/bin/bash

## Build framework release branch & distributables

# Setup variables
. admin/release-config

echo -e "${BOLD}${COLOR}CodeIgniter4 release builder${NORMAL}"
echo '----------------------------'

#---------------------------------------------------
# Check arguments
echo -e "${BOLD}Checking arguments...${NORMAL}"

if [ $# -lt 1 ]; then
    echo -e "${BOLD}Usage: admin/release version# pre-release-qualifier${NORMAL}"
    exit 1
fi

#---------------------------------------------------
# Create the release branch
echo -e "${BOLD}Creating $which $branch to $action ${NORMAL}"

git checkout develop
git branch -d $branch &>/dev/null    # remove the branch if there
git checkout -b $branch
composer update

#---------------------------------------------------
# Update version dependencies
echo -e "${BOLD}Updating version dependencies${NORMAL}"

function check_unique {
    count=`grep -c '$1' < $2 | wc -l`
    if [ $count -ne 1 ]; then
        echo -e "${BOLD}${COLOR}$2 has ${count} occurrences of '$1'${NORMAL}"
        exit 1
    fi
}

# Make sure there is only one line to affect in each file
check_unique "const CI_VERSION" 'system/CodeIgniter.php'
check_unique "release =" 'user_guide_src/source/conf.py'
check_unique "|release|" 'user_guide_src/source/changelogs/index.rst'
check_unique "Release Date.*Not Released" 'user_guide_src/source/changelogs/index.rst'

# CI_VERSION definition in system/CodeIgniter.php
sed -i "/const CI_VERSION/s/'.*'/'${RELEASE}'/" system/CodeIgniter.php

# release substitution variable in user_guide_src/source/conf.py
sed -i "/release =/s/'.*'/'${RELEASE}'/" user_guide_src/source/conf.py

# version & date in user_guide_src/source/index.rst
sed -i "/|release|/s/|.*|/${RELEASE}/" user_guide_src/source/changelogs/index.rst
sed -i "/Release Date/s/Not Released/$(date +'%B %d, %Y')/" user_guide_src/source/changelogs/index.rst
sed -i "/|version|/s/|version|/${RELEASE}/" user_guide_src/source/changelogs/index.rst

# version & date in user_guide_src/source/next.rst
sed -i "/Release Date/s/Not Released/$(date +'%B %d, %Y')/" user_guide_src/source/changelogs/next.rst
sed -i "/|version|/s/|version|/${RELEASE}/" user_guide_src/source/changelogs/next.rst

# establish version-specific changelog
sed -i "|changelogs/next|s|changeslog/next|changelogs/v{$RELEASE}|" user_guide_src/source/changelogs/index.rst
mv user_guide_src/source/changelogs/next.rst user_guide_src/source/changelogs/v${RELEASE}.rst
touch user_guide_src/source/changelogs/next.rst
cp admin/next.rst user_guide_src/source/changelogs/next.rst

#---------------------------------------------------
# Setup the distribution folders
echo -e "${BOLD}Building distribution folders${NORMAL}"

function setup_repo {
	echo -e "${BOLD}... $1${NORMAL}"
    if [ -d dist/$1 ]; then
        rm -rf dist/$1
    fi
    mkdir dist/$1
    cd dist/$1
    git init
    git remote add origin ${CI_ORG}/$1.git
    git fetch
	git checkout master
	git checkout -b $branch
	cd $CI_DIR
}

if [ -d dist ]; then
    rm -rf dist/
fi
mkdir dist

setup_repo userguide

#---------------------------------------------------
# Housekeeping - make sure writable is flushed of test files
# at least, test files that crop up on my system :-/
rm -f writable/cache/H*
rm -f writable/cache/d*
rm -f writable/cache/s*
rm -f writable/debugbar/debug*
rm -f writable/logs/log*

#---------------------------------------------------
# Generate the user guide
echo -e "${BOLD}Generate the user guide${NORMAL}"

cd user_guide_src

# make the UG 
rm -rf build/*
echo -e "${BOLD}... HTML version${NORMAL}"
make html
touch build/html/.nojekyll
echo -e "${BOLD}... epub version${NORMAL}"
make epub

cd ${CI_DIR}

# add changelog preamble
file=user_guide_src/source/changelogs/index.rst
sed -i "4 a Version |version|" $file
sed -i "5 a ====================================================" $file
sed -i "6 G" $file
sed -i "7 a Release Date: Not Released" $file
sed -i "8 G" $file
sed -i "9 a **Next release of CodeIgniter4**" $file
sed -i "10 G" $file
sed -i "11 G" $file
sed -i "12 a :doc:\`See all the changes. </changelogs/next>\`" $file
sed -i "13 G" $file

#---------------------------------------------------
echo -e "${BOLD}Commit the release branch${NORMAL}"
git add .
git commit -S -m "Release ${RELEASE}"

#---------------------------------------------------
# Build the distributables

. admin/release-userguide

#---------------------------------------------------
# Done for now
echo -e "${BOLD}Your $branch branch is ready to inspect.${NORMAL}"
echo -e "${BOLD}Follow the directions in workflow.md to continue.${NORMAL}"
