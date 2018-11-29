#!/bin/bash

## Revert local repos to pre-release state
echo -e "${BOLD}${COLOR}CodeIgniter4 release revert${NORMAL}"
echo '---------------------------'

if [ $# -lt 2 ]; then
    echo "You forgot the magic word"
    exit 1
fi
if [ $1 != 'please' ]; then
    echo "What do you say?"
    exit 1
fi

. admin/release-config

echo -e "${BOLD}Reverting the main repository${NORMAL}"
git checkout master
git pull -f ${CI_ORG}/CodeIgniter4 master
git checkout develop
git pull -f${CI_ORG}/CodeIgniter4 develop

#---------------------------------------------------
# Phew!
echo -e "${BOLD}Congratulations - we have aborted liftoff${NORMAL}"
