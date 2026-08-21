<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\EmployeeFieldEvent;
use App\Models\EmployeeGpsPing;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class EmployeeGpsTrackingSeeder extends Seeder
{
    public function run(): void
    {
        $employee = $this->resolvePrimaryEmployee();
        $today = Carbon::today();

        $this->seedMangaloreDay($employee, $today);
        $this->seedMangaloreDay($employee, Carbon::parse('2026-03-18'));
    }

    private function resolvePrimaryEmployee(): Employee
    {
        return Employee::query()->updateOrCreate(
            ['employee_id' => 'MNG-001'],
            [
                'name' => 'Rahul Shetty',
                'email' => 'rahul.shetty@mangalore.local',
                'designation' => 'Field Executive — Mangaluru',
                'status' => 'active',
                'financial_year' => '2025-2026',
            ]
        );
    }

    private function seedMangaloreDay(Employee $employee, Carbon $trackDate): void
    {
        EmployeeGpsPing::query()
            ->where('employee_id', $employee->id)
            ->whereDate('track_date', $trackDate->toDateString())
            ->delete();

        EmployeeFieldEvent::query()
            ->where('employee_id', $employee->id)
            ->whereDate('track_date', $trackDate->toDateString())
            ->delete();

        $day = $trackDate->toDateString();

        // Real Mangalore-area coordinates
        $office = ['lat' => 12.87112, 'lng' => 74.84208, 'alt' => 48.2];           // Bejai, Kadri Road
        $kankanady = ['lat' => 12.85948, 'lng' => 74.84262, 'alt' => 32.5];        // Kankanady
        $pumpwell = ['lat' => 12.90524, 'lng' => 74.85718, 'alt' => 41.0];         // Pumpwell junction
        $cityCentre = ['lat' => 12.87158, 'lng' => 74.85642, 'alt' => 38.7];       // City Centre Mall, Bendoorwell
        $hampankatta = ['lat' => 12.86981, 'lng' => 74.84335, 'alt' => 15.8];      // Hampankatta circle

        EmployeeFieldEvent::create([
            'employee_id' => $employee->id,
            'event_type' => EmployeeFieldEvent::TYPE_OFFICE,
            'place_name' => 'office',
            'address' => '3rd Floor, Empire Arcade, Kadri Road, Bejai, Mangaluru 575004',
            'latitude' => $office['lat'],
            'longitude' => $office['lng'],
            'track_date' => $day,
            'check_in_at' => $trackDate->copy()->setTime(8, 55),
            'check_out_at' => $trackDate->copy()->setTime(9, 12),
        ]);

        $this->seedRoutePings($employee, $day, $office, $kankanady, $trackDate->copy()->setTime(9, 12), 9, 52.0);
        $this->seedRoutePings($employee, $day, $kankanady, $pumpwell, $trackDate->copy()->setTime(9, 42), 11, 38.0);
        $this->seedRoutePings($employee, $day, $pumpwell, $cityCentre, $trackDate->copy()->setTime(10, 18), 8, 42.0);
        $this->seedRoutePings($employee, $day, $cityCentre, $hampankatta, $trackDate->copy()->setTime(10, 52), 6, 28.0);
        $this->seedRoutePings($employee, $day, $hampankatta, $office, $trackDate->copy()->setTime(11, 8), 7, 35.0);

        EmployeeFieldEvent::create([
            'employee_id' => $employee->id,
            'event_type' => EmployeeFieldEvent::TYPE_VISIT,
            'place_name' => 'Retail distributor',
            'address' => 'Shop 12, Kankanady Market Road, Near Jyothi Circle, Mangaluru 575002',
            'latitude' => $kankanady['lat'],
            'longitude' => $kankanady['lng'],
            'track_date' => $day,
            'check_in_at' => $trackDate->copy()->setTime(9, 28),
            'check_out_at' => $trackDate->copy()->setTime(9, 42),
        ]);

        EmployeeFieldEvent::create([
            'employee_id' => $employee->id,
            'event_type' => EmployeeFieldEvent::TYPE_VISIT,
            'place_name' => 'Construction site',
            'address' => 'NH-66 Service Road, Pumpwell, Mangaluru 575002',
            'latitude' => $pumpwell['lat'],
            'longitude' => $pumpwell['lng'],
            'track_date' => $day,
            'check_in_at' => $trackDate->copy()->setTime(10, 2),
            'check_out_at' => $trackDate->copy()->setTime(10, 18),
        ]);

        EmployeeFieldEvent::create([
            'employee_id' => $employee->id,
            'event_type' => EmployeeFieldEvent::TYPE_VISIT,
            'place_name' => 'City Centre Mall',
            'address' => 'City Centre Mall, Bendoorwell Main Road, Mangaluru 575002',
            'latitude' => $cityCentre['lat'],
            'longitude' => $cityCentre['lng'],
            'track_date' => $day,
            'check_in_at' => $trackDate->copy()->setTime(10, 38),
            'check_out_at' => $trackDate->copy()->setTime(10, 52),
        ]);

        EmployeeFieldEvent::create([
            'employee_id' => $employee->id,
            'event_type' => EmployeeFieldEvent::TYPE_OFFICE,
            'place_name' => 'office',
            'address' => '3rd Floor, Empire Arcade, Kadri Road, Bejai, Mangaluru 575004',
            'latitude' => $office['lat'],
            'longitude' => $office['lng'],
            'track_date' => $day,
            'check_in_at' => $trackDate->copy()->setTime(11, 18),
            'check_out_at' => $trackDate->copy()->setTime(17, 30),
        ]);

        EmployeeFieldEvent::create([
            'employee_id' => $employee->id,
            'event_type' => EmployeeFieldEvent::TYPE_TRAVEL,
            'place_name' => 'Travel',
            'latitude' => $kankanady['lat'],
            'longitude' => $kankanady['lng'],
            'track_date' => $day,
            'check_in_at' => $trackDate->copy()->setTime(9, 12),
            'check_out_at' => $trackDate->copy()->setTime(9, 28),
            'travel_distance_km' => 2.1,
            'travel_duration_minutes' => 16,
        ]);

        EmployeeFieldEvent::create([
            'employee_id' => $employee->id,
            'event_type' => EmployeeFieldEvent::TYPE_TRAVEL,
            'place_name' => 'Travel',
            'latitude' => $pumpwell['lat'],
            'longitude' => $pumpwell['lng'],
            'track_date' => $day,
            'check_in_at' => $trackDate->copy()->setTime(9, 42),
            'check_out_at' => $trackDate->copy()->setTime(10, 2),
            'travel_distance_km' => 5.4,
            'travel_duration_minutes' => 20,
        ]);

        EmployeeFieldEvent::create([
            'employee_id' => $employee->id,
            'event_type' => EmployeeFieldEvent::TYPE_TRAVEL,
            'place_name' => 'Travel',
            'latitude' => $cityCentre['lat'],
            'longitude' => $cityCentre['lng'],
            'track_date' => $day,
            'check_in_at' => $trackDate->copy()->setTime(10, 18),
            'check_out_at' => $trackDate->copy()->setTime(10, 38),
            'travel_distance_km' => 3.2,
            'travel_duration_minutes' => 20,
        ]);

        EmployeeFieldEvent::create([
            'employee_id' => $employee->id,
            'event_type' => EmployeeFieldEvent::TYPE_TRAVEL,
            'place_name' => 'Travel',
            'latitude' => $office['lat'],
            'longitude' => $office['lng'],
            'track_date' => $day,
            'check_in_at' => $trackDate->copy()->setTime(10, 52),
            'check_out_at' => $trackDate->copy()->setTime(11, 18),
            'travel_distance_km' => 2.8,
            'travel_duration_minutes' => 26,
        ]);
    }

    private function seedRoutePings(
        Employee $employee,
        string $day,
        array $from,
        array $to,
        Carbon $startAt,
        int $pointCount,
        float $baseAltitude
    ): void {
        for ($i = 0; $i <= $pointCount; $i++) {
            $ratio = $pointCount === 0 ? 1 : $i / $pointCount;
            $lat = $from['lat'] + (($to['lat'] - $from['lat']) * $ratio) + (sin($i * 1.3) * 0.00012);
            $lng = $from['lng'] + (($to['lng'] - $from['lng']) * $ratio) + (cos($i * 0.9) * 0.00010);
            $altFrom = $from['alt'] ?? $baseAltitude;
            $altTo = $to['alt'] ?? $baseAltitude;
            $altitude = $altFrom + (($altTo - $altFrom) * $ratio) + (sin($i) * 1.2);

            EmployeeGpsPing::create([
                'employee_id' => $employee->id,
                'latitude' => round($lat, 6),
                'longitude' => round($lng, 6),
                'altitude' => round($altitude, 1),
                'accuracy' => round(6.5 + ($i % 3), 1),
                'speed' => $i === 0 || $i === $pointCount ? 0 : round(18 + ($i * 2.8), 1),
                'bearing' => round(35 + ($i * 14), 1),
                'track_date' => $day,
                'recorded_at' => $startAt->copy()->addMinutes($i * 2),
                'source' => 'seed',
            ]);
        }
    }
}
