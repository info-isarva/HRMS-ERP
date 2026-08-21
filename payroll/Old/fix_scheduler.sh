#!/bin/bash

#################################################################
# HRMS Scheduled Notifications - Quick Fix Script
# Run this to automatically fix common scheduler issues
#################################################################

echo "=============================================="
echo "HRMS Scheduled Notifications - Auto Fix"
echo "=============================================="
echo ""

cd /home/hrmsdev.isarva.in/public_html/payroll

# Step 1: Clear caches
echo "[Step 1/5] Clearing caches..."
php artisan config:clear
php artisan cache:clear
echo "✓ Caches cleared"
echo ""

# Step 2: Verify command exists
echo "[Step 2/5] Verifying command..."
if php artisan list 2>/dev/null | grep -q "notifications:process-scheduled"; then
    echo "✓ Command is registered"
else
    echo "✗ Command NOT found - something is wrong with the installation"
    exit 1
fi
echo ""

# Step 3: Test the command
echo "[Step 3/5] Testing command execution..."
php artisan notifications:process-scheduled
echo "✓ Command executed successfully"
echo ""

# Step 4: Setup cron job
echo "[Step 4/5] Setting up cron job..."
CRON_JOB="* * * * * cd /home/hrmsdev.isarva.in/public_html/payroll && php artisan schedule:run >> /dev/null 2>&1"

if (crontab -l 2>/dev/null | grep -q "schedule:run"); then
    echo "✓ Cron job already configured"
else
    echo "Adding cron job..."
    (crontab -l 2>/dev/null; echo "$CRON_JOB") | crontab -
    echo "✓ Cron job added"
fi
echo ""

# Step 5: Verify cron
echo "[Step 5/5] Verifying cron service..."
if sudo service cron status > /dev/null 2>&1; then
    echo "✓ Cron service is running"
else
    echo "Starting cron service..."
    sudo service cron start
    echo "✓ Cron service started"
fi
echo ""

echo "=============================================="
echo "✓ Auto fix complete!"
echo "=============================================="
echo ""
echo "Your scheduled notifications should now:"
echo "  1. Activate automatically at their scheduled time"
echo "  2. Process every minute via the cron job"
echo ""
echo "To verify it's working:"
echo "  1. Check the logs: tail -f storage/logs/laravel.log"
echo "  2. Run diagnostics: bash diagnose_scheduler.sh"
echo ""
