#!/usr/bin/env bash
set -euo pipefail

# Static guardrails against common SQL injection footguns in Yii2/PHP codebases.
# This is intentionally conservative: it flags patterns that are almost always unsafe
# when user-controlled values can reach SQL text.

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

USE_RG=1
if ! command -v rg >/dev/null 2>&1; then
  USE_RG=0
fi

if [[ "$USE_RG" -eq 0 ]]; then
  PHP_BIN=""
  if command -v php >/dev/null 2>&1; then
    PHP_BIN="$(command -v php)"
  elif command -v php.exe >/dev/null 2>&1; then
    PHP_BIN="$(command -v php.exe)"
  fi

  if [[ -z "${PHP_BIN}" ]]; then
    echo "error: ripgrep (rg) is not installed, and php/php.exe is not available to run tools/sql-injection-audit.php" >&2
    exit 2
  fi

  echo "note: ripgrep (rg) not found; running portable PHP scanner (${PHP_BIN} tools/sql-injection-audit.php)" >&2
  exec "${PHP_BIN}" tools/sql-injection-audit.php
fi

EXCLUDES=(
  --glob '!vendor/**'
  --glob '!runtime/**'
  --glob '!web/assets/**'
  --glob '!node_modules/**'
  --glob '!.git/**'
  --glob '!tools/**'
)

# Allow explicit exceptions on the same line (use sparingly).
ALLOW='sql-audit:allow'

fail=0

run_check () {
  local name="$1"; shift
  echo "==> ${name}"

  local tmp
  tmp="$(mktemp)"

  # shellcheck disable=SC2068
  set +e
  rg "${EXCLUDES[@]}" "$@" . >"$tmp"
  local rc=$?
  set -e

  # rg exits 1 when there are no matches; treat as OK.
  if [[ "$rc" -ne 0 && "$rc" -ne 1 ]]; then
    echo "rg failed (exit $rc) while running: $*" >&2
    cat "$tmp" >&2 || true
    rm -f "$tmp"
    exit "$rc"
  fi

  local filtered
  filtered="$(grep -v "${ALLOW}" "$tmp" || true)"
  rm -f "$tmp"

  if [[ -n "${filtered}" ]]; then
    echo "${filtered}"
    echo
    echo "FAILED: ${name}"
    echo "Fix the matches above, or add '${ALLOW}' on the same line if truly justified."
    echo
    fail=1
  else
    echo "OK: ${name}"
  fi
  echo
}

# 1) Dynamic SQL passed into Yii command builders.
run_check "createCommand(\\$var) / createCommand(\\$sql)" \
  -n \
  --pcre2 \
  -e 'createCommand\(\s*\$'

# 2) String WHERE / AND / OR fragments (high risk of concatenation / interpolation).
run_check "where/andWhere/orWhere with string conditions" \
  -n \
  --pcre2 \
  -e '->(where|andWhere|orWhere)\(\s*["\x27]'

# 3) SQL keywords concatenated immediately after a quoted SQL fragment.
run_check "SQL string literal concatenation (\"SELECT...\" .)" \
  -n \
  --pcre2 \
  -e '["\x27](?:SELECT|INSERT|UPDATE|DELETE)\b[^"\x27]*["\x27]\s*\.'

# 4) Superglobals interpolated into SQL-ish strings.
run_check "Superglobals inside quoted SQL-ish strings" \
  -n \
  --pcre2 \
  -e '["\x27][^"\x27]*\$_(GET|POST|REQUEST)\b[^"\x27]*["\x27]'

if [[ "$fail" -ne 0 ]]; then
  echo "NOTE: If a match is a false positive, add '${ALLOW}' on that line."
  exit 1
fi

echo "All SQL injection guardrail checks passed."
