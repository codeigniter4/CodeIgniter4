#!/bin/sh -e

# Deploys the User Guide to the production
# website. Triggered by updates to the GitHub
# codeigniter4/userguide repo's master branch.
# See https://github.com/codeigniter4/userguide/blob/master/.github/workflows/deploy.yml

REPO="/opt/userguide"
SITE="/home/public_html/userguides/userguide4"

if [ "$(id -u)" = "0" ]; then
    echo "Cannot be run as root. Please run as the user for deployment."
    exit 1
fi

cd "$REPO"
git switch master
git pull

cp -R "$REPO/docs" "$SITE.new"
mv "$SITE" "$SITE.old"
mv "$SITE.new" "$SITE"
rm -rf "$SITE.old"
