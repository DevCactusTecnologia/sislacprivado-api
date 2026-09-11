#!/usr/bin/env bash
set -euo pipefail

: "${BASE_URL:?BASE_URL é obrigatório}"
: "${BEARER_TOKEN:?BEARER_TOKEN é obrigatório para validar o Supabase Auth}"

BASE_URL="${BASE_URL%/}"

expect_status() {
    local expected="$1"
    local path="$2"
    shift 2

    local status
    status="$(curl --silent --show-error --output /dev/null --write-out '%{http_code}' \
        --connect-timeout 5 --max-time 15 "$@" "${BASE_URL}${path}")"

    if [[ "$status" != "$expected" ]]; then
        echo "${path}: esperado HTTP ${expected}, recebido ${status}." >&2
        exit 1
    fi

    echo "${path}: HTTP ${status}"
}

expect_status 200 /up
expect_status 200 /api/health
expect_status 401 /api/me
expect_status 200 /api/me -H "Authorization: Bearer ${BEARER_TOKEN}"
