#!/bin/bash
# Run once after enabling poshdev.isarva.in child domain (fixes HTTP 500 from storage permissions).
set -euo pipefail
cd "$(dirname "$0")"
chmod -R ug+rwx storage bootstrap/cache
chmod 755 .
echo "Permissions fixed. Test: https://poshdev.isarva.in/login"
