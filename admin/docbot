#!/bin/bash

# Rebuild and deploy CodeIgniter4 under-development user guide
#
# This is a quick way to test user guide changes, and if they
# look good, to push them to the gh-pages branch of the
# development repository.
#
# This is not meant for updating the "stable" user guide.

UPSTREAM=https://github.com/codeigniter4/CodeIgniter4.git

# Prepare the nested repo clone folder
cd user_guide_src
rm -rf build/*
mkdir build/html

# Get ready for git
cd build/html
git init
git remote add origin $UPSTREAM
git fetch 
git checkout gh-pages
git reset --hard origin/gh-pages
rm -r *

# Make the new user guide
cd ../..
make html

# All done?
if [ $# -lt 1 ]; then
  exit 0
fi

# Optionally update the remote repo
if [ $1 = "deploy" ]; then
  cd build/html
  git add .
  git commit -S -m "Docbot synching"
  git push -f origin gh-pages
fi