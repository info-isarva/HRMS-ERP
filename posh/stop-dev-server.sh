#!/bin/bash
cd "$(dirname "$0")"
PID_FILE=storage/posh-dev-server.pid

if [[ -f "$PID_FILE" ]]; then
    pid=$(cat "$PID_FILE")
    if kill -0 "$pid" 2>/dev/null; then
        kill "$pid" 2>/dev/null || true
        sleep 1
        kill -9 "$pid" 2>/dev/null || true
    fi
    rm -f "$PID_FILE"
fi

# artisan serve may leave a child PHP process on :8001
if command -v fuser >/dev/null 2>&1; then
    fuser -k 8001/tcp 2>/dev/null || true
fi

echo "POSH dev server stopped (port 8001)"
