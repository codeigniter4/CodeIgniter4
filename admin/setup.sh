#!/bin/sh

# Install a pre-commit hook that
# automatically runs php-cs-fixer to lint code
mkdir -p .git/hooks
cp admin/pre-commit .git/hooks/pre-commit
chmod +x .git/hooks/pre-commit
