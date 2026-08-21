<?php

namespace App\Support;

use App\Models\User;
use Carbon\Carbon;

class WebLeaveSubmissionRestriction
{
    public static function isActive(): bool
    {
        if (! config('web_leave_restriction.enabled', false)) {
            return false;
        }

        $expiresAt = config('web_leave_restriction.expires_at');
        if (! $expiresAt) {
            return true;
        }

        try {
            return ! now()->startOfDay()->gt(Carbon::parse($expiresAt)->endOfDay());
        } catch (\Throwable) {
            return true;
        }
    }

    public static function restrictedEmails(): array
    {
        return config('web_leave_restriction.emails', []);
    }

    public static function isRestrictedEmail(?string $email): bool
    {
        if (! self::isActive() || $email === null || $email === '') {
            return false;
        }

        return in_array(strtolower(trim($email)), self::restrictedEmails(), true);
    }

    public static function blocksUser(?User $user): bool
    {
        return $user !== null && self::isRestrictedEmail($user->email);
    }

    public static function message(): string
    {
        $template = config(
            'web_leave_restriction.message',
            'Please use the HRMS mobile app to submit leave.'
        );

        $expiresAt = config('web_leave_restriction.expires_at');
        $dateLabel = $expiresAt;
        try {
            if ($expiresAt) {
                $dateLabel = Carbon::parse($expiresAt)->format('d M Y');
            }
        } catch (\Throwable) {
            // keep raw string
        }

        return str_replace(':date', (string) $dateLabel, $template);
    }
}
