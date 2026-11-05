#!/usr/bin/env bash

# Exercise the scheduler in an isolated project with a lightweight PHPUnit stub.
set -eu

script_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
test_root="$(mktemp -d)"
trap 'rm -rf "$test_root"' EXIT

mkdir -p "$test_root/.github/scripts" "$test_root/vendor/bin" \
    "$test_root/tests/system/First" "$test_root/tests/system/Second"
cp "$script_dir/run-random-tests.sh" "$test_root/.github/scripts/"
printf '%s\n' First Second > "$test_root/.github/scripts/random-tests-config.txt"

cat > "$test_root/vendor/bin/phpunit" <<'PHPUNIT'
#!/usr/bin/env bash
printf '%s\n' "$1" >> calls.log
sleep 0.05
PHPUNIT
chmod +x "$test_root/vendor/bin/phpunit"

# A deadline makes a scheduler deadlock fail instead of hanging the CI job.
for max_jobs in 1 2; do
    : > "$test_root/calls.log"
    if ! GITHUB_ACTIONS=true timeout --kill-after=1s 5s \
        bash "$test_root/.github/scripts/run-random-tests.sh" \
        --max-jobs "$max_jobs" --repeat 2 --timeout 0 \
        > "$test_root/output.log" 2>&1; then
        cat "$test_root/output.log"
        echo "Scheduler failed with --max-jobs $max_jobs" >&2
        exit 1
    fi

    for component in First Second; do
        count=$(grep -c "^tests/system/$component$" "$test_root/calls.log" || true)
        if [[ "$count" -ne 2 ]]; then
            cat "$test_root/output.log"
            echo "Expected two runs of $component, got $count" >&2
            exit 1
        fi
    done
done

echo 'Random test scheduler checks passed.'
