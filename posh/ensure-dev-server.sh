#!/bin/bash
# Restart POSH dev server if port 8001 is not listening.
# For root/admin cron (this account cannot install crontab):
#   */5 * * * * /home/hrmsdev.isarva.in/public_html/posh/ensure-dev-server.sh >> /home/hrmsdev.isarva.in/public_html/posh/storage/logs/ensure-dev-server.log 2>&1

set -euo pipefail
cd "$(dirname "$0")"

if ss -tln 2>/dev/null | grep -q ':8001 '; then
    exit 0
fi

echo "$(date -Is) port 8001 down — starting POSH dev server"
./start-dev-server.sh
