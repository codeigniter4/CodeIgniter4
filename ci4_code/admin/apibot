#!/bin/bash

# Rebuild and deploy CodeIgniter4 under-development user guide
#
# This is a quick way to test user guide changes, and if they
# look good, to push them to the gh-pages branch of the
# development repository.
#
# This is not meant for updating the "stable" user guide.

UPSTREAM=https://github.com/codeigniter4/api.git

# Prepare the nested repo clone folder
rm -rf build/api*
mkdir -p build/api/docs

# Get ready for git
cd build/api
git init
git remote add origin $UPSTREAM
git fetch
git checkout master
git reset --hard origin/master
rm -r docs/*

# Make the new user guide
cd ../..
phpdoc
cp -R api/build/* build/api/docs

# All done?
if [ $# -lt 1 ]; then
  exit 0
fi

# Optionally update the remote repo
if [ $1 = "deploy" ]; then
  cd build/api
  git add .
  git commit -S -m "APIbot synching"
  git push -f origin master
fi
