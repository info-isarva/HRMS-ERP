<?php

namespace App\Support;

use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class EmployeeBirthdaySupport
{
    /**
     * Active employees whose date_of_birth matches the given calendar day (month-day).
     *
     * @return Collection<int, Employee>
     */
    public static function employeesWithBirthdayOn(Carbon $date): Collection
    {
        $targetMonthDay = $date->format('m-d');

        return Employee::query()
            ->active()
            ->currentlyActive()
            ->get()
            ->filter(function (Employee $employee) use ($targetMonthDay) {
                $dob = self::parseDateOfBirth($employee);

                return $dob && $dob->format('m-d') === $targetMonthDay;
            })
            ->values();
    }

    public static function parseDateOfBirth(Employee $employee): ?Carbon
    {
        $additionalData = $employee->additional_data;
        if (is_string($additionalData)) {
            $additionalData = json_decode($additionalData, true);
        }

        if (! is_array($additionalData) || empty($additionalData['date_of_birth'])) {
            return null;
        }

        try {
            return Carbon::parse($additionalData['date_of_birth']);
        } catch (\Throwable) {
            return null;
        }
    }
}
