#!/bin/bash

#################################################################
# HRMS Scheduled Notifications - Quick Diagnostic Script
# Run this to identify why your 2:30 PM notification isn't firing
#################################################################

echo "=============================================="
echo "HRMS Scheduled Notifications Diagnostics"
echo "=============================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Change to payroll directory
cd /home/hrmsdev.isarva.in/public_html/payroll

echo -e "${BLUE}[1/8] Checking Server Time${NC}"
echo "─────────────────────────────────────"
date
echo ""

echo -e "${BLUE}[2/8] Checking System Timezone${NC}"
echo "─────────────────────────────────────"
timedatectl | grep -E "Time zone|Local time"
echo ""

echo -e "${BLUE}[3/8] Checking Laravel Timezone Config${NC}"
echo "─────────────────────────────────────"
LARAVEL_TZ=$(grep APP_TIMEZONE .env | cut -d= -f2)
echo "APP_TIMEZONE in .env: $LARAVEL_TZ"
php artisan tinker --quiet <<'EOF'
echo "Laravel config timezone: " . config('app.timezone') . "\n";
EOF
echo ""

echo -e "${BLUE}[4/8] Checking Cron Service Status${NC}"
echo "─────────────────────────────────────"
if sudo service cron status > /dev/null 2>&1; then
    echo -e "${GREEN}✓ Cron service is running${NC}"
else
    echo -e "${RED}✗ Cron service is NOT running${NC}"
    echo "  Fix: sudo service cron start"
fi
echo ""

echo -e "${BLUE}[5/8] Checking Crontab Entry${NC}"
echo "─────────────────────────────────────"
CRON_ENTRY=$(crontab -l 2>/dev/null | grep "schedule:run\|artisan schedule" | head -1)
if [ -z "$CRON_ENTRY" ]; then
    echo -e "${RED}✗ No scheduler cron job found${NC}"
    echo "  Add this to crontab:"
    echo "  * * * * * cd /home/hrmsdev.isarva.in/public_html/payroll && php artisan schedule:run >> /dev/null 2>&1"
else
    echo -e "${GREEN}✓ Cron job found:${NC}"
    echo "  $CRON_ENTRY"
fi
echo ""

echo -e "${BLUE}[6/8] Checking if Command is Registered${NC}"
echo "─────────────────────────────────────"
if php artisan list 2>/dev/null | grep -q "notifications:process-scheduled"; then
    echo -e "${GREEN}✓ Command is registered${NC}"
else
    echo -e "${RED}✗ Command NOT registered${NC}"
fi
echo ""

echo -e "${BLUE}[7/8] Checking Scheduled Notifications in Database${NC}"
echo "─────────────────────────────────────"
php artisan tinker --quiet <<'EOF'
use App\Models\ManualNotification;
use Carbon\Carbon;

$scheduled = ManualNotification::where('status', 'scheduled')->get();
echo "Total scheduled notifications: " . $scheduled->count() . "\n";

if ($scheduled->count() > 0) {
    echo "\nScheduled notifications:\n";
    foreach ($scheduled as $n) {
        $now = Carbon::now();
        $shouldActivate = $n->start_date->lte($now);
        echo "  - ID: " . $n->id . "\n";
        echo "    Title: " . substr($n->title, 0, 40) . "...\n";
        echo "    Scheduled for: " . $n->start_date . "\n";
        echo "    Current time: " . $now . "\n";
        echo "    Should activate now? " . ($shouldActivate ? "YES ✓" : "NO ✗") . "\n";
        echo "\n";
    }
} else {
    echo "No scheduled notifications found.\n";
}
EOF
echo ""

echo -e "${BLUE}[8/8] Testing Command Execution${NC}"
echo "─────────────────────────────────────"
echo "Running: php artisan notifications:process-scheduled"
php artisan notifications:process-scheduled
echo ""

echo "=============================================="
echo "Diagnostics Complete"
echo "=============================================="
echo ""
echo -e "${YELLOW}If all checks show ✓ (green), your scheduler is working.${NC}"
echo -e "${YELLOW}If any show ✗ (red), follow the fix instructions above.${NC}"
echo ""
echo "For detailed troubleshooting, see:"
echo "  /home/hrmsdev.isarva.in/public_html/SCHEDULED_NOTIFICATIONS_FIX.md"
