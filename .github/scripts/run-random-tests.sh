#!/usr/bin/env bash

################################################################################
# CodeIgniter4 - Random Test Execution Verification
#
# Verifies that tests for each component pass when run in random order.
# Reads a list of components from a config file and tests each with parallel
# execution while respecting a configurable concurrency limit.
#
# Usage: ./run-random-tests.sh [options]
# Options:
#   -q, --quiet                  Suppress debug output
#   -c, --component COMPONENT    Test single COMPONENT (overrides config file)
#   -n, --max-jobs MAX_JOBS      Limit concurrent test jobs (auto-detect if omitted)
#   -r, --repeat REPEAT          Repeat full component run REPEAT times
#   -t, --timeout TIMEOUT        Per-component TIMEOUT in seconds (0 disables, default: 300)
#   -h, --help                   Show this help message
#
# Examples:
#   ./run-random-tests.sh --repeat 10
#   ./run-random-tests.sh --component Database --repeat 5
#   ./run-random-tests.sh --repeat 10 --max-jobs 4 --quiet
################################################################################

set -u
export LC_NUMERIC=C
trap 'kill "${bg_pids[@]:-}" 2>/dev/null; wait 2>/dev/null' EXIT INT TERM

################################################################################
# CONFIGURATION & INITIALIZATION
################################################################################

# Color codes for terminal output
readonly RED='\033[0;31m'
readonly BOLD_RED='\033[1;31m'
readonly GREEN='\033[0;32m'
readonly YELLOW='\033[0;33m'
readonly BOLD_YELLOW='\033[1;33m'
readonly BLUE='\033[0;34m'
readonly BOLD='\033[1m'
readonly RESET='\033[0m'

# Script paths
readonly script_dir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
readonly project_root="$( cd "$script_dir/../.." && pwd )"
readonly config_file="$script_dir/random-tests-config.txt"
readonly results_dir="$project_root/build/random-tests"

# Runtime variables
quiet=""
component=""
max_jobs=""
repeat_count=1
timeout_seconds=300
first_result=true
declare -a bg_pids=()

# Counters
completed=0
passed=0
failed=0
skipped=0
total=0

# Component state tracking
declare -a displayed_components=()
declare -a failed_components=()
declare -a skipped_components=()

################################################################################
# UTILITY FUNCTIONS
################################################################################

is_quiet() {
    [[ "$quiet" == "--quiet" || "$quiet" == "-q" ]]
}

should_show_spinner() {
    if ! is_quiet; then
        return 1
    fi

    if [[ -n "${GITHUB_ACTIONS:-}" ]]; then
        return 1
    fi

    return 0
}

show_spinner() {
    local spinner_marker="$results_dir/run_random_tests_$$.spinner"
    touch "$spinner_marker"

    local spinner_chars=('⠋' '⠙' '⠹' '⠸' '⠼' '⠴' '⠦' '⠧' '⠇' '⠏')
    local spinner_index=0

    echo -ne "\033[?25l" >&2

    (
        while [[ -f "$spinner_marker" ]]; do
            echo -ne "\033[2K\r${BLUE}${spinner_chars[$((spinner_index % 10))]} Running tests in parallel...${RESET}" >&2
            ((spinner_index++))
            sleep 0.1
        done
        echo -ne "\033[2K\r" >&2
    ) &

    echo "$!" > "${spinner_marker}.pid"
}

stop_spinner() {
    local spinner_marker="$results_dir/run_random_tests_$$.spinner"

    if [[ ! -f "$spinner_marker" ]]; then
        echo -ne "\033[?25h" >&2
        return
    fi

    rm -f "$spinner_marker"
    echo -ne "\033[2K\r" >&2

    if [[ -f "${spinner_marker}.pid" ]]; then
        kill "$(cat "${spinner_marker}.pid")" 2>/dev/null || true
        rm -f "${spinner_marker}.pid"
    fi

    wait 2>/dev/null
    echo -ne "\033[?25h" >&2
}

print_header() {
    echo -e "${BLUE}==============================================================================${RESET}"
    echo -e "${BLUE}$1${RESET}"
    echo -e "${BLUE}==============================================================================${RESET}"
}

print_success() {
    echo -e "${GREEN}✓ $1${RESET}"
}

print_error() {
    echo -e "${RED}✗ $1${RESET}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${RESET}"
}

print_debug() {
    if ! is_quiet; then
        echo -e "${BLUE}🔧 $1${RESET}"
    fi
}

inflect() {
    local count=$1
    local singular=$2
    local plural=${3:-${singular}s}

    if [[ "$count" -eq 1 ]]; then
        echo "$singular"
        return
    fi

    echo "$plural"
}

generate_phpunit_random_seed() {
    local seed=$(date +%s)

    if [[ ! "$seed" =~ ^[0-9]+$ ]]; then
        echo 1
        return
    fi

    echo $((seed + (RANDOM % 1000)))
}

extract_test_order() {
    local events_file="$1"

    if [[ ! -f "$events_file" ]]; then
        return
    fi

    while IFS= read -r line; do
        if [[ "$line" =~ ^Test\ Prepared\ \((.*)\)$ ]]; then
            echo "${BASH_REMATCH[1]}"
        fi
    done < "$events_file"
}

get_failed_test_predecessor() {
    local events_file="$1"

    if [[ ! -f "$events_file" ]]; then
        return
    fi

    local failed_test=""
    while IFS= read -r line; do
        if [[ "$line" =~ ^Test\ Failed\ \((.*)\)$ ]] \
            || [[ "$line" =~ ^Test\ Errored\ \((.*)\)$ ]] \
            || [[ "$line" =~ ^Test\ Considered\ Risky\ \((.*)\)$ ]] \
            || [[ "$line" =~ ^Test\ Triggered\ Warning\ \((.*)\)$ ]] \
            || [[ "$line" =~ ^Test\ Triggered\ PHP\ Warning\ \((.*)\)$ ]] \
            || [[ "$line" =~ ^Test\ Triggered\ PHP\ Notice\ \((.*)\)$ ]] \
            || [[ "$line" =~ ^Test\ Triggered\ PHP\ Deprecation\ \((.*)\)$ ]] \
            || [[ "$line" =~ ^Test\ Triggered\ PHP\ Unit\ Deprecation\ \((.*)\)$ ]]; then
            failed_test="${BASH_REMATCH[1]}"
            break
        fi
    done < "$events_file"

    if [[ -z "$failed_test" ]]; then
        return
    fi

    local previous_test=""
    while IFS= read -r line; do
        if [[ "$line" =~ ^Test\ Prepared\ \((.*)\)$ ]]; then
            local current_test="${BASH_REMATCH[1]}"
            if [[ "$current_test" == "$failed_test" ]]; then
                echo "$failed_test|$previous_test"
                return
            fi
            previous_test="$current_test"
        fi
    done < "$events_file"

    echo "$failed_test|"
}

print_result() {
    local type=$1 completed=$2 total=$3 component=$4 elapsed_str=$5
    local padded=$(printf "%${#total}d" "$completed")
    local color symbol

    case "$type" in
        success) color=$GREEN; symbol="✓" ;;
        failure) color=$RED; symbol="✗" ;;
        warning) color=$YELLOW; symbol="⚠" ;;
    esac

    echo -e "${BOLD_YELLOW}[${padded}/${total}]${RESET} ${color}${symbol}${RESET} Component: ${BLUE}$component${RESET} ${YELLOW}($elapsed_str)${RESET}"
}

format_elapsed_time() {
    local elapsed=$1

    if ! [[ "$elapsed" =~ ^[0-9]+$ ]]; then
        echo "N/A"
        return
    fi

    if [[ $elapsed -ge 60000 ]]; then
        printf "%dm %.2fs" "$((elapsed / 60000))" "$(echo "scale=2; ($elapsed % 60000) / 1000" | bc)"
    elif [[ $elapsed -ge 1000 ]]; then
        printf "%.2fs" "$(echo "scale=2; $elapsed / 1000" | bc)"
    else
        echo "${elapsed}ms"
    fi
}

################################################################################
# CONFIGURATION & VALIDATION
################################################################################

parse_arguments() {
    while [[ $# -gt 0 ]]; do
        case "$1" in
            -h|--help)
                show_help
                exit 0
                ;;
            -q|--quiet)
                quiet="--quiet"
                shift
                ;;
            -c|--component)
                if [[ $# -lt 2 ]]; then
                    print_error "Missing value for --component"
                    exit 1
                fi

                component="$2"
                shift 2
                ;;
            -n|--max-jobs)
                if [[ $# -lt 2 ]]; then
                    print_error "Missing value for --max-jobs"
                    exit 1
                fi

                if [[ ! "$2" =~ ^[1-9][0-9]*$ ]]; then
                    print_error "--max-jobs must be a positive integer"
                    exit 1
                fi

                max_jobs="$2"
                shift 2
                ;;
            -r|--repeat)
                if [[ $# -lt 2 ]]; then
                    print_error "Missing value for --repeat"
                    exit 1
                fi

                if [[ ! "$2" =~ ^[1-9][0-9]*$ ]]; then
                    print_error "--repeat must be a positive integer"
                    exit 1
                fi

                repeat_count="$2"
                shift 2
                ;;
            -t|--timeout)
                if [[ $# -lt 2 ]]; then
                    print_error "Missing value for --timeout"
                    exit 1
                fi

                if [[ ! "$2" =~ ^[0-9]+$ ]]; then
                    print_error "--timeout must be a non-negative integer"
                    exit 1
                fi

                timeout_seconds="$2"
                shift 2
                ;;
            *)
                print_error "Unknown option '$1'"
                exit 1
                ;;
        esac
    done
}

show_help() {
    echo "CodeIgniter4 - Random Test Execution Verification"
    echo ""
    echo -e "${YELLOW}Usage:${RESET}"
    echo -e "  ${GREEN}$0${RESET} [options]"
    echo ""
    echo -e "${YELLOW}Options:${RESET}"
    echo -e "  ${GREEN}-q, --quiet${RESET}                 Suppress debug output"
    echo -e "  ${GREEN}-c, --component COMPONENT${RESET}   Test single ${GREEN}COMPONENT${RESET} (overrides config file)"
    echo -e "  ${GREEN}-n, --max-jobs MAX_JOBS${RESET}     Limit concurrent test jobs (auto-detect if omitted)"
    echo -e "  ${GREEN}-r, --repeat REPEAT${RESET}         Repeat full component run ${GREEN}REPEAT${RESET} times"
    echo -e "  ${GREEN}-t, --timeout TIMEOUT${RESET}       Per-component ${GREEN}TIMEOUT${RESET} in seconds (0 disables, default: 300)"
    echo -e "  ${GREEN}-h, --help${RESET}                  Show this help message"
}

auto_detect_max_jobs() {
    if command -v nproc &>/dev/null; then
        nproc
        return
    fi

    if command -v sysctl &>/dev/null; then
        sysctl -n hw.ncpu 2>/dev/null
        return
    fi

    echo 4
}

check_required_tools() {
    local -a missing_tools=()
    local -a required_tools=(date find sort sed grep bc)
    local tool

    for tool in "${required_tools[@]}"; do
        if ! command -v "$tool" >/dev/null 2>&1; then
            missing_tools+=("$tool")
        fi
    done

    if [[ $timeout_seconds -gt 0 ]]; then
        if ! command -v timeout >/dev/null 2>&1; then
            if ! command -v gtimeout >/dev/null 2>&1; then
                if ! command -v pgrep >/dev/null 2>&1; then
                    missing_tools+=("pgrep")
                fi

                if ! command -v pkill >/dev/null 2>&1; then
                    missing_tools+=("pkill")
                fi
            fi
        fi
    fi

    if [[ ! -x "$project_root/vendor/bin/phpunit" ]]; then
        print_error "PHPUnit executable not found or not executable: $project_root/vendor/bin/phpunit"
        echo "Run composer install before running this script."
        exit 1
    fi

    if [[ ${#missing_tools[@]} -gt 0 ]]; then
        print_error "Missing required command(s): ${missing_tools[*]}"
        echo "Install the missing tools and re-run this script."
        exit 1
    fi
}

verify_config() {
    print_debug "Verifying configuration file: $config_file"

    if [[ ! -f "$config_file" ]]; then
        print_error "Configuration file not found: $config_file"
        echo "Please create $config_file with a list of components, one per line."
        exit 1
    fi
}

validate_component_name() {
    local component="$1"

    # Only allow alphanumeric characters, hyphens, and underscores
    # Reject path traversal attempts and command injection characters
    if [[ ! "$component" =~ ^[a-zA-Z0-9_-]+$ ]]; then
        return 1
    fi

    return 0
}

read_components() {
    declare -a components=()

    while IFS= read -r line; do
        line="${line#"${line%%[![:space:]]*}"}"
        line="${line%"${line##*[![:space:]]}"}"

        if [[ -z "$line" || "$line" =~ ^# ]]; then
            continue
        fi

        # Validate component name for security
        if ! validate_component_name "$line"; then
            print_warning "Skipping invalid/unsafe component name: $line"
            continue
        fi

        components+=("$line")
    done < "$config_file"

    echo "${components[@]}"
}

################################################################################
# TEST EXECUTION
################################################################################

run_component_tests() {
    local component=$1

    # Security: Validate component name before use
    if ! validate_component_name "$component"; then
        print_error "Security: Invalid component name rejected: $component"
        return 1
    fi

    local test_dir="tests/system/$component"
    local start_time=$(date +%s%N)

    print_debug "Running tests for: $component"

    if [[ ! -d "$test_dir" ]]; then
        local elapsed=$((($(date +%s%N) - $start_time) / 1000000))
        {
            echo "Exit code: 2"
            echo "Elapsed time: $elapsed"
        } > "$results_dir/random_test_result_${elapsed}_${component}.txt"
        return
    fi

    local output_file="$results_dir/random_test_output_${component}_$$.log"
    local events_file="$results_dir/random_test_events_${component}_$$.log"
    local random_seed=$(generate_phpunit_random_seed)
    local exit_code=0

    # Security: Use array to avoid eval and prevent command injection
    local -a phpunit_args=(
        "vendor/bin/phpunit"
        "$test_dir"
        "--colors=never"
        "--no-coverage"
        "--do-not-cache-result"
        "--order-by=random"
        "--random-order-seed=${random_seed}"
        "--log-events-text"
        "$events_file"
    )

    if [[ $timeout_seconds -gt 0 ]] && command -v timeout >/dev/null 2>&1; then
        (cd "$project_root" && timeout --kill-after=2s "${timeout_seconds}s" "${phpunit_args[@]}") > "$output_file" 2>&1
        exit_code=$?
    elif [[ $timeout_seconds -gt 0 ]] && command -v gtimeout >/dev/null 2>&1; then
        (cd "$project_root" && gtimeout --kill-after=2s "${timeout_seconds}s" "${phpunit_args[@]}") > "$output_file" 2>&1
        exit_code=$?
    else
        local timeout_marker="$output_file.timeout"
        (cd "$project_root" && "${phpunit_args[@]}") > "$output_file" 2>&1 &
        local test_pid=$!

        if [[ $timeout_seconds -gt 0 ]]; then
            # Watchdog: monitors test process and kills it after timeout
            # Uses 1-second sleep intervals to respond quickly when test finishes early
            (
                local elapsed=0
                while [[ $elapsed -lt $timeout_seconds ]]; do
                    sleep 1
                    elapsed=$((elapsed + 1))
                    kill -0 "$test_pid" 2>/dev/null || exit 0
                done

                if kill -0 "$test_pid" 2>/dev/null; then
                    touch "$timeout_marker"
                    local pids_to_kill=$(pgrep -P "$test_pid" 2>/dev/null)

                    kill -TERM "$test_pid" 2>/dev/null || true
                    if [[ -n "$pids_to_kill" ]]; then
                        echo "$pids_to_kill" | xargs kill -TERM 2>/dev/null || true
                    fi

                    sleep 2

                    if kill -0 "$test_pid" 2>/dev/null; then
                        kill -KILL "$test_pid" 2>/dev/null || true
                        if [[ -n "$pids_to_kill" ]]; then
                            echo "$pids_to_kill" | xargs kill -KILL 2>/dev/null || true
                        fi
                        # Security: Quote and escape test_dir for safe pattern matching
                        pkill -KILL -f "phpunit.*${test_dir//\//\\/}" 2>/dev/null || true
                    fi
                fi
            ) &
            disown $! 2>/dev/null || true
        fi

        wait "$test_pid" 2>/dev/null
        exit_code=$?

        if [[ -f "$timeout_marker" ]]; then
            exit_code=124
            rm -f "$timeout_marker"
        elif [[ $exit_code -eq 143 || $exit_code -eq 137 ]]; then
            exit_code=124
        fi
    fi

    local elapsed=$((($(date +%s%N) - $start_time) / 1000000))
    local result_file="$results_dir/random_test_result_${elapsed}_${component}.txt"
    local order_file="$results_dir/random_test_order_${elapsed}_${component}.txt"

    if [[ -f "$events_file" ]]; then
        extract_test_order "$events_file" > "$order_file"
    else
        echo "Execution order unavailable (events file not created)." > "$order_file"
    fi

    if [[ $exit_code -eq 0 ]]; then
        {
            echo "Exit code: 0"
            echo "Elapsed time: $elapsed"
        } > "$result_file"
        rm -f "$output_file" "$events_file" "$order_file"
    else
        local output=""
        if [[ -f "$output_file" ]]; then
            output=$(cat "$output_file")
        fi

        if [[ $exit_code -eq 124 ]]; then
            output+=$'\n\nTest timed out after '"${timeout_seconds}s"
        fi

        local predecessor_info=$'\nExecution order file: '"${order_file}"
        if [[ $exit_code -eq 124 ]]; then
            predecessor_info+=$'\nFailed test: (timeout before PHPUnit emitted failure event)'
            if [[ -f "$events_file" ]]; then
                local last_prepared_test=$(extract_test_order "$events_file" | tail -n 1)
                if [[ -n "$last_prepared_test" ]]; then
                    predecessor_info+=$'\nLast prepared test before timeout: '"${last_prepared_test}"
                else
                    predecessor_info+=$'\nLast prepared test before timeout: (unavailable)'
                fi
            else
                predecessor_info+=$'\nLast prepared test before timeout: (events file unavailable)'
            fi
            predecessor_info+=$'\nPrevious test: (unavailable due to timeout)'
        else
            if [[ -f "$events_file" ]]; then
                local predecessor_result=$(get_failed_test_predecessor "$events_file")
                if [[ -n "$predecessor_result" ]]; then
                    local previous_test=${predecessor_result#*|}
                    predecessor_info+=$'\nFailed test: '"${predecessor_result%%|*}"
                    if [[ -n "$previous_test" ]]; then
                        predecessor_info+=$'\nPrevious test: '"${previous_test}"
                    else
                        predecessor_info+=$'\nPrevious test: (none - failed test ran first)'
                    fi
                else
                    predecessor_info+=$'\nFailed test: (not detected from PHPUnit events log)'
                    predecessor_info+=$'\nPrevious test: (unavailable)'
                fi
            else
                predecessor_info+=$'\nFailed test: (events file unavailable)'
                predecessor_info+=$'\nPrevious test: (unavailable)'
            fi
        fi

        {
            echo "> ${phpunit_args[@]:0:7}"
            echo ""
            echo "$output"
            echo "$predecessor_info"
            echo ""
            echo "Exit code: 1"
            echo "Elapsed time: $elapsed"
        } > "$result_file"
        rm -f "$output_file" "$events_file"
    fi
}

cleanup_finished_pids() {
    local -a active=()
    for pid in "${bg_pids[@]:-}"; do
        if kill -0 "$pid" 2>/dev/null; then
            active+=("$pid")
        fi
    done
    bg_pids=("${active[@]:-}")
}

spawn_limited_job() {
    local component=$1

    cleanup_finished_pids

    while [[ ${#bg_pids[@]} -ge $max_jobs ]]; do
        sleep 0.05
        cleanup_finished_pids
    done

    run_component_tests "$component" &
    bg_pids+=($!)
}

process_result() {
    local component=$1
    local elapsed=$2
    local result_file="$results_dir/random_test_result_${elapsed}_${component}.txt"

    if [[ ! -f "$result_file" ]]; then
        return 1
    fi

    ((completed++))

    if [[ "$first_result" == true ]] && ! is_quiet; then
        echo ""
        first_result=false
    fi

    local status=$(grep "^Exit code:" "$result_file" | sed 's/Exit code: //')
    local elapsed_str=$(format_elapsed_time "$elapsed")

    case "$status" in
        0)
            ((passed++))
            print_result "success" "$completed" "$total" "$component" "$elapsed_str"
            rm -f "$result_file"
            ;;
        2)
            ((skipped++))
            skipped_components+=("$component")
            print_result "warning" "$completed" "$total" "$component" "$elapsed_str"
            rm -f "$result_file"
            ;;
        *)
            ((failed++))
            failed_components+=("$component")
            print_result "failure" "$completed" "$total" "$component" "$elapsed_str"
            ;;
    esac

    displayed_components+=("$component")

    return 0
}

get_completed_components() {
    # Returns unprocessed test results sorted by elapsed time (fastest first)
    # Filename format: random_test_result_${elapsed}_${component}.txt
    # Output format: "component|elapsed" (one per line)

    # Extract and sort files by elapsed time
    local -a entries=()

    while IFS= read -r file_path; do
        # Remove prefix: random_test_result_
        local temp=$(basename "$file_path")
        temp=${temp#random_test_result_}

        # Extract elapsed time (everything before first underscore after number)
        local elapsed=${temp%%_*}

        # Validate elapsed is numeric
        if [[ ! "$elapsed" =~ ^[0-9]+$ ]]; then
            continue
        fi

        # Extract component (everything after elapsed and underscore, before .txt)
        local listed_component=${temp#${elapsed}_}
        listed_component=${listed_component%.txt}

        entries+=("$elapsed|$listed_component")
    done < <(find "$results_dir" -maxdepth 1 -type f -name "random_test_result_*.txt" 2>/dev/null)

    # Sort entries by elapsed time numerically
    printf '%s\n' "${entries[@]}" | sort -t'|' -k1,1n |
    while IFS='|' read -r elapsed listed_component; do
        if [[ ! " ${displayed_components[*]:-} " =~ " ${listed_component} " ]]; then
            echo "$listed_component|$elapsed"
        fi
    done
}


print_summary() {
    local run_number=$1
    local pass_percent=0.00
    local fail_percent=0.00
    local skip_percent=0.00

    if [[ $total -gt 0 ]]; then
        pass_percent=$(printf "%.2f" "$(echo "scale=2; $passed * 100 / $total" | bc)")
        fail_percent=$(printf "%.2f" "$(echo "scale=2; $failed * 100 / $total" | bc)")
        skip_percent=$(printf "%.2f" "$(echo "scale=2; $skipped * 100 / $total" | bc)")
    fi

    echo ""
    print_header "Test Execution Summary"
    printf "%-20s %b\n" "Total $(inflect "$total" "Component" "Components"):" "${BLUE}$total${RESET}"
    printf "%-20s %b\n" "Passed:" "${GREEN}$passed${RESET} (${GREEN}${pass_percent}%${RESET})"
    printf "%-20s %b\n" "Failed:" "${RED}$failed${RESET} (${RED}${fail_percent}%${RESET})"
    printf "%-20s %b\n" "Skipped:" "${YELLOW}$skipped${RESET} (${YELLOW}${skip_percent}%${RESET})"
    printf "%-20s %b\n" "Completed Runs:" "${BLUE}$run_number${RESET} / ${BLUE}$repeat_count${RESET}"

    if [[ $failed -gt 0 ]]; then
        echo -e "\n${BOLD_RED}Failed $(inflect "$failed" "Component" "Components"):${RESET}"
        while IFS= read -r failed_component; do
            local result_file=$(find "$results_dir" -name "random_test_result_*_${failed_component}.txt" 2>/dev/null | head -n 1)

            if [[ -z "$result_file" ]]; then
                result_file="$results_dir/random_test_result_*_${failed_component}.txt"
            fi

            echo -e "  ${RED}✗${RESET} ${BOLD}$failed_component${RESET} ($result_file)"
        done < <(printf '%s\n' "${failed_components[@]}" | sort)
    fi

    if [[ $skipped -gt 0 ]]; then
        echo -e "\n${BOLD_YELLOW}Skipped $(inflect "$skipped" "Component" "Components"):${RESET}"
        while IFS= read -r skipped_component; do
            echo -e "  ${YELLOW}⚠${RESET} ${BOLD}$skipped_component${RESET} (no such directory: ${BOLD}tests/system/$skipped_component${RESET})"
        done < <(printf '%s\n' "${skipped_components[@]}" | sort)
    fi
}

################################################################################
# MAIN SCRIPT
################################################################################

main() {
    cd "$project_root" || exit 1

    parse_arguments "$@"
    check_required_tools

    if [[ -z "$max_jobs" ]]; then
        max_jobs=$(auto_detect_max_jobs)
    fi

    print_header "CodeIgniter4 - Random Test Execution Verification"
    echo ""

    declare -a components_array

    if [[ -n "$component" ]]; then
        # Single component specified via command line
        if ! validate_component_name "$component"; then
            print_error "Invalid component name: $component"
            echo "  Component name must contain only: alphanumeric, hyphens, underscores, forward slashes"
            echo "  Cannot contain: spaces, dots, consecutive slashes, or start with slash"
            exit 1
        fi
        print_debug "Testing single component specified via command line: $component\n"
        components_array=("$component")
    else
        # Read components from config file
        verify_config
        print_success "Configuration file: $config_file\n"
        components_array=($(read_components))
    fi

    total=${#components_array[@]}

    if [[ $total -eq 0 ]]; then
        if [[ -n "$component" ]]; then
            print_error "Component not found or inaccessible: $component"
        else
            print_warning "No components configured in $config_file"
            echo "Please add component names to the configuration file, one per line."
        fi
        exit 0
    fi

    print_debug "Found $total $(inflect "$total" "component") to test"
    print_debug "Max concurrent jobs: $max_jobs"
    print_debug "Per-component timeout: ${timeout_seconds}s"
    print_debug "Total runs: $repeat_count\n"

    local run=1
    while [[ $run -le $repeat_count ]]; do
        completed=0
        passed=0
        failed=0
        skipped=0
        displayed_components=()
        failed_components=()
        skipped_components=()
        bg_pids=()
        first_result=true

        if [[ $run -gt 1 ]]; then
            echo ""
        fi

        if [[ $repeat_count -gt 1 ]]; then
            print_header "Run $run/$repeat_count"
            echo ""
        fi

        print_debug "Setting up results directory: $results_dir\n"
        mkdir -p "$results_dir"
        rm -f "$results_dir"/*

        if ! should_show_spinner; then
            echo -e "${BLUE}Running tests in parallel...${RESET}\n"
        else
            show_spinner
        fi

        for next_component in "${components_array[@]}"; do
            spawn_limited_job "$next_component"
        done

        for finished_pid in "${bg_pids[@]:-}"; do
            wait "$finished_pid" 2>/dev/null || true
        done

        if should_show_spinner; then
            stop_spinner
        fi

        while IFS='|' read -r next_component next_elapsed; do
            process_result "$next_component" "$next_elapsed" || true
        done < <(get_completed_components)

        print_summary "$run"

        if [[ $failed -gt 0 || $skipped -gt 0 ]]; then
            exit 1
        fi

        ((run++))
    done

    echo ""
    if [[ -n "$component" ]]; then
        print_success "Component '$component' passed random execution tests!"
    else
        print_success "All components passed random execution tests!"
    fi
}

main "$@"
