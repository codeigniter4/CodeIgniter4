#!/bin/bash
set -o pipefail

if [[ -z $1 ]]; then
    echo "validate-version requires a version identifier"
    exit 1
fi

FILES=("system/CodeIgniter.php" "user_guide_src/source/conf.py")
LENGTH="${#FILES[@]}"

for FILE in "${FILES[@]}"; do
    COUNT="$((COUNT + $(grep -c "$FILE" -e "'$1'")))"
done

if [[ $COUNT -ne $LENGTH ]]; then
    echo "CodeIgniter version is not updated to v"$1""
    exit 1
fi

echo "CodeIgniter version is updated to v"$1""
