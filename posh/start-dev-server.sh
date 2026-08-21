#!/bin/bash
# Dev POSH server (port 8001) — use while poshdev.isarva.in vhost is not ready.
# Mac /etc/hosts:  139.84.143.214  poshdev.isarva.in
#
# Why it stops: this is a manual `php artisan serve` process (not OpenLiteSpeed).
# It dies on server reboot, OOM kill, or if never restarted after deploy.
# Permanent fix: install deploy/systemd/posh-dev-server.service (see docs/posh/PHASE-1.md).

set -euo pipefail
cd "$(dirname "$0")"
PHP="${PHP:-/usr/local/lsws/lsphp82/bin/php}"
PID_FILE=storage/posh-dev-server.pid
LOG_DIR=storage/logs
LOG_FILE="$LOG_DIR/dev-server.log"

mkdir -p "$LOG_DIR"

if ss -tln 2>/dev/null | grep -q ':8001 '; then
    echo "POSH dev server already listening on :8001"
    exit 0
fi

if [[ -f "$PID_FILE" ]]; then
    old_pid=$(cat "$PID_FILE")
    if kill -0 "$old_pid" 2>/dev/null; then
        kill "$old_pid" 2>/dev/null || true
        sleep 1
    fi
    rm -f "$PID_FILE"
fi

nohup "$PHP" artisan serve --host=0.0.0.0 --port=8001 >>"$LOG_FILE" 2>&1 &
echo $! >"$PID_FILE"
sleep 1

if ss -tln 2>/dev/null | grep -q ':8001 '; then
    echo "POSH dev server started on http://0.0.0.0:8001 (PID $(cat "$PID_FILE"))"
else
    echo "Failed to bind port 8001 — see $LOG_FILE" >&2
    exit 1
fi
