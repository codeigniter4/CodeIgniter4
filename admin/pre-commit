#!/bin/sh

PROJECT=`php -r "echo dirname(dirname(dirname(realpath('$0'))));"`
STAGED_PHP_FILES=`git diff --cached --name-only --diff-filter=ACMR HEAD | grep \\\\.php$`
STAGED_RST_FILES=`git diff --cached --name-only --diff-filter=ACMR HEAD | grep \\\\.rst$`

echo "Starting CodeIgniter precommit..."

if [ "$STAGED_PHP_FILES" != "" ]; then
    echo "Linting PHP code..."
    for FILE in $STAGED_PHP_FILES; do
        php -l -d display_errors=0 "$PROJECT/$FILE"

        if [ $? != 0 ]; then
            echo "Fix the error(s) before commit."
            exit 1
        fi

        FILES="$FILES $FILE"
    done
fi

if [ "$FILES" != "" ]; then
    echo "Running PHP CS Fixer..."

    # Run on whole codebase to skip on unnecessary filtering
    composer cs

    if [ $? != 0 ]; then
        echo "There are PHP files which are not following the coding standards. Please fix them before commit."
        exit 1
    fi
fi

if [ "$STAGED_RST_FILES" != "" ]; then
    echo "Checking for tabs in RST files"
    php ./utils/check_tabs_in_rst.php
fi

exit $?
