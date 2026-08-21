<?php

namespace App\Services;

use App\Exceptions\GpsSessionException;
use App\Models\Employee;
use App\Models\EmployeeFieldEvent;
use App\Models\EmployeeGpsPing;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class GpsTrackingService
{
    public const DISPLAY_TIMEZONE = 'Asia/Kolkata';

    public function __construct(private GpsRouteMatchingService $routeMatching)
    {
    }

    public function getTrackingDay(Employee $employee, Carbon $date): array
    {
        $trackDate = $date->timezone(self::DISPLAY_TIMEZONE)->toDateString();

        $pings = EmployeeGpsPing::query()
            ->where('employee_id', $employee->id)
            ->whereDate('track_date', $trackDate)
            ->orderBy('recorded_at')
            ->get();

        $events = EmployeeFieldEvent::query()
            ->where('employee_id', $employee->id)
            ->whereDate('track_date', $trackDate)
            ->orderByRaw('COALESCE(check_in_at, check_out_at) ASC')
            ->get();

        $route = $pings->map(fn (EmployeeGpsPing $ping) => [
            'lat' => (float) $ping->latitude,
            'lng' => (float) $ping->longitude,
            'recorded_at' => $this->toApiTimestamp($ping->recorded_at),
            'recorded_at_label' => $this->formatTimeLabel($ping->recorded_at),
            'altitude' => $ping->altitude,
            'speed' => $ping->speed,
            'accuracy' => $ping->accuracy,
        ])->values()->all();

        $displayRoute = $this->routeMatching->buildDisplayRoute($employee->id, $trackDate, $route);

        $timeline = $this->buildTimeline($events);
        $summary = $this->buildSummary($events, $pings);
        $markers = $this->buildMarkers($events, $pings);
        $openOffice = $this->findOpenOfficeEvent($events);
        $openVisit = $this->findOpenVisitEvent($events);

        return [
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->name,
                'employee_id' => $employee->employee_id,
                'designation' => $employee->designation,
            ],
            'date' => $trackDate,
            'summary' => $summary,
            'route' => $route,
            'route_display' => $displayRoute['points'],
            'route_matched' => $displayRoute['matched'],
            'route_display_source' => $displayRoute['source'],
            'timeline' => $timeline,
            'markers' => $markers,
            'open_office' => $openOffice ? $this->serializeFieldEvent($openOffice) : null,
            'open_visit' => $openVisit ? $this->serializeFieldEvent($openVisit) : null,
        ];
    }

    public function storePings(Employee $employee, ?int $userId, array $pings, string $source = 'mobile'): int
    {
        $stored = 0;

        foreach ($pings as $ping) {
            $recordedAt = $this->parseInstant($ping['recorded_at']);

            EmployeeGpsPing::create([
                'employee_id' => $employee->id,
                'user_id' => $userId,
                'latitude' => $ping['latitude'],
                'longitude' => $ping['longitude'],
                'altitude' => $ping['altitude'] ?? null,
                'accuracy' => $ping['accuracy'] ?? null,
                'speed' => $ping['speed'] ?? null,
                'bearing' => $ping['bearing'] ?? null,
                'track_date' => $recordedAt->toDateString(),
                'recorded_at' => $recordedAt,
                'source' => $source,
            ]);

            $stored++;
        }

        return $stored;
    }

    public function storeFieldEvent(Employee $employee, ?int $userId, array $data): EmployeeFieldEvent
    {
        $checkInAt = isset($data['check_in_at']) ? $this->parseInstant($data['check_in_at']) : null;
        $checkOutAt = isset($data['check_out_at']) ? $this->parseInstant($data['check_out_at']) : null;
        $trackDate = $data['track_date'] ?? ($checkInAt?->toDateString() ?? $checkOutAt?->toDateString() ?? now()->toDateString());

        return EmployeeFieldEvent::create([
            'employee_id' => $employee->id,
            'user_id' => $userId,
            'event_type' => $data['event_type'],
            'place_name' => $data['place_name'] ?? null,
            'address' => $data['address'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'track_date' => $trackDate,
            'check_in_at' => $checkInAt,
            'check_out_at' => $checkOutAt,
            'travel_distance_km' => $data['travel_distance_km'] ?? null,
            'travel_duration_minutes' => $data['travel_duration_minutes'] ?? null,
            'metadata' => $data['metadata'] ?? null,
        ]);
    }

    public function checkIn(Employee $employee, ?int $userId, array $data): EmployeeFieldEvent
    {
        $now = isset($data['check_in_at']) ? $this->parseInstant($data['check_in_at']) : now(self::DISPLAY_TIMEZONE);
        $eventType = $data['event_type'] ?? EmployeeFieldEvent::TYPE_VISIT;
        $trackDate = $now->toDateString();

        $this->assertCanCheckIn($employee, $eventType, $trackDate);

        $placeName = $this->resolvePlaceName(
            $eventType,
            (float) $data['latitude'],
            (float) $data['longitude'],
            $data['place_name'] ?? 'Site visit'
        );
        $address = $data['address'] ?? null;

        if ($this->isWithinOfficeGeofence((float) $data['latitude'], (float) $data['longitude'])) {
            $address = $address ?: config('gps.office_geofence.address');
        }

        return $this->storeFieldEvent($employee, $userId, [
            'event_type' => $eventType,
            'place_name' => $placeName,
            'address' => $address,
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'check_in_at' => $now,
            'track_date' => $trackDate,
            'metadata' => $data['metadata'] ?? null,
        ]);
    }

    public function checkOut(Employee $employee, EmployeeFieldEvent $event, array $data = []): EmployeeFieldEvent
    {
        $this->assertCanCheckOut($employee, $event);

        $checkOutAt = isset($data['check_out_at']) ? $this->parseInstant($data['check_out_at']) : now(self::DISPLAY_TIMEZONE);
        $event->check_out_at = $checkOutAt;

        if (isset($data['address']) && ! $event->address) {
            $event->address = $data['address'];
        }

        $event->save();

        return $event->fresh();
    }

    public function assertCanCheckIn(Employee $employee, string $eventType, string $trackDate): void
    {
        if ($eventType === EmployeeFieldEvent::TYPE_OFFICE) {
            if ($this->findOpenOfficeForDate($employee, $trackDate)) {
                throw new GpsSessionException('Already signed in at office. Sign out first.');
            }

            return;
        }

        if ($eventType === EmployeeFieldEvent::TYPE_VISIT) {
            if (! $this->findOpenOfficeForDate($employee, $trackDate)) {
                throw new GpsSessionException('Sign in at office before recording a visit.');
            }

            if ($this->findOpenVisitForDate($employee, $trackDate)) {
                throw new GpsSessionException('Already checked in at a visit. Check out from visit first.');
            }
        }
    }

    public function assertCanCheckOut(Employee $employee, EmployeeFieldEvent $event): void
    {
        if ($event->employee_id !== $employee->id) {
            throw new GpsSessionException('Event not found for this employee.', 404);
        }

        if (! $this->isOpenFieldEvent($event)) {
            throw new GpsSessionException('This event is already closed.');
        }

        if (! in_array($event->event_type, [EmployeeFieldEvent::TYPE_OFFICE, EmployeeFieldEvent::TYPE_VISIT], true)) {
            throw new GpsSessionException('Invalid event type for check-out.');
        }
    }

    public function findOpenOfficeForDate(Employee $employee, string $trackDate): ?EmployeeFieldEvent
    {
        return EmployeeFieldEvent::query()
            ->where('employee_id', $employee->id)
            ->where('event_type', EmployeeFieldEvent::TYPE_OFFICE)
            ->whereDate('track_date', $trackDate)
            ->whereNotNull('check_in_at')
            ->whereNull('check_out_at')
            ->orderByDesc('check_in_at')
            ->first();
    }

    public function findOpenVisitForDate(Employee $employee, string $trackDate): ?EmployeeFieldEvent
    {
        return EmployeeFieldEvent::query()
            ->where('employee_id', $employee->id)
            ->where('event_type', EmployeeFieldEvent::TYPE_VISIT)
            ->whereDate('track_date', $trackDate)
            ->whereNotNull('check_in_at')
            ->whereNull('check_out_at')
            ->orderByDesc('check_in_at')
            ->first();
    }

    public function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return round($earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a)), 2);
    }

    public function routeDistanceKm(Collection $pings): float
    {
        if ($pings->count() < 2) {
            return 0;
        }

        $total = 0.0;
        $previous = null;

        foreach ($pings as $ping) {
            if ($previous) {
                $total += $this->distanceKm(
                    (float) $previous->latitude,
                    (float) $previous->longitude,
                    (float) $ping->latitude,
                    (float) $ping->longitude
                );
            }
            $previous = $ping;
        }

        return round($total, 1);
    }

    private function buildSummary(Collection $events, Collection $pings): array
    {
        $travelMinutes = (int) $events
            ->where('event_type', EmployeeFieldEvent::TYPE_TRAVEL)
            ->sum('travel_duration_minutes');

        $distanceKm = round(
            (float) $events->where('event_type', EmployeeFieldEvent::TYPE_TRAVEL)->sum('travel_distance_km'),
            1
        );

        $visits = $events
            ->where('event_type', EmployeeFieldEvent::TYPE_VISIT)
            ->whereNotNull('check_out_at')
            ->count();

        if ($distanceKm <= 0 && $pings->count() >= 2) {
            $distanceKm = $this->routeDistanceKm($pings);
        }

        if ($travelMinutes <= 0 && $pings->count() >= 2) {
            $first = $pings->first()->recorded_at;
            $last = $pings->last()->recorded_at;
            $travelMinutes = max(1, (int) $first->diffInMinutes($last));
        }

        return [
            'travel_time_minutes' => $travelMinutes,
            'travel_time_label' => $this->formatDuration($travelMinutes),
            'distance_km' => $distanceKm,
            'distance_label' => number_format($distanceKm, 1).' km',
            'visits' => $visits,
        ];
    }

    private function buildTimeline(Collection $events): array
    {
        $items = [];

        foreach ($events as $event) {
            if ($event->event_type === EmployeeFieldEvent::TYPE_TRAVEL) {
                $start = $event->check_in_at ?? $event->check_out_at;
                $end = $event->check_out_at ?? $start;

                $items[] = [
                    'type' => 'travel',
                    'title' => 'Travel',
                    'distance_km' => (float) ($event->travel_distance_km ?? 0),
                    'duration_minutes' => (int) ($event->travel_duration_minutes ?? 0),
                    'detail' => sprintf(
                        '%s km · %s',
                        number_format((float) ($event->travel_distance_km ?? 0), 1),
                        $this->formatDuration((int) ($event->travel_duration_minutes ?? 0))
                    ),
                    'time_range' => $this->formatTimeRange($start, $end),
                    'start_at' => $this->toApiTimestamp($start),
                    'end_at' => $this->toApiTimestamp($end),
                    'occurred_at' => $this->toApiTimestamp($start),
                    'lat' => $event->latitude,
                    'lng' => $event->longitude,
                ];

                continue;
            }

            if (! in_array($event->event_type, [EmployeeFieldEvent::TYPE_OFFICE, EmployeeFieldEvent::TYPE_VISIT], true)) {
                continue;
            }

            $isOffice = $event->event_type === EmployeeFieldEvent::TYPE_OFFICE;
            $type = $isOffice ? 'office' : 'visit';
            $placeTitle = $event->place_name ?? ($isOffice ? 'Office' : 'Site visit');
            $sessionOpen = $this->isOpenFieldEvent($event);

            $base = [
                'event_id' => $event->id,
                'type' => $type,
                'place_name' => $event->place_name,
                'address' => $event->address,
                'lat' => $event->latitude,
                'lng' => $event->longitude,
                'check_in_at' => $this->toApiTimestamp($event->check_in_at),
                'check_out_at' => $event->check_out_at ? $this->toApiTimestamp($event->check_out_at) : null,
            ];

            if ($event->check_in_at) {
                $items[] = array_merge($base, [
                    'action' => 'check_in',
                    'title' => $isOffice
                        ? $this->officeDisplayName($event).' — Office in'
                        : $placeTitle.' — Visit in',
                    'status' => $this->formatCheckInStatusLabel($event),
                    'occurred_at' => $this->toApiTimestamp($event->check_in_at),
                    'is_open' => $sessionOpen,
                ]);
            }

            if ($event->check_out_at) {
                $items[] = array_merge($base, [
                    'action' => 'check_out',
                    'title' => $isOffice
                        ? $this->officeDisplayName($event).' — Office out'
                        : $placeTitle.' — Visit out',
                    'status' => $this->formatCheckOutStatusLabel($event),
                    'occurred_at' => $this->toApiTimestamp($event->check_out_at),
                    'is_open' => false,
                ]);
            }
        }

        return collect($items)
            ->sortBy(fn (array $item) => strtotime($item['occurred_at'] ?? $item['start_at'] ?? 'now'))
            ->values()
            ->all();
    }

    private function findOpenOfficeEvent(Collection $events): ?EmployeeFieldEvent
    {
        return $events
            ->filter(fn (EmployeeFieldEvent $event) => $event->event_type === EmployeeFieldEvent::TYPE_OFFICE && $this->isOpenFieldEvent($event))
            ->sortByDesc(fn (EmployeeFieldEvent $event) => $event->check_in_at?->timestamp ?? 0)
            ->first();
    }

    private function findOpenVisitEvent(Collection $events): ?EmployeeFieldEvent
    {
        return $events
            ->filter(fn (EmployeeFieldEvent $event) => $event->event_type === EmployeeFieldEvent::TYPE_VISIT && $this->isOpenFieldEvent($event))
            ->sortByDesc(fn (EmployeeFieldEvent $event) => $event->check_in_at?->timestamp ?? 0)
            ->first();
    }

    private function isOpenFieldEvent(EmployeeFieldEvent $event): bool
    {
        return in_array($event->event_type, [EmployeeFieldEvent::TYPE_OFFICE, EmployeeFieldEvent::TYPE_VISIT], true)
            && $event->check_in_at
            && ! $event->check_out_at;
    }

    public function serializeFieldEvent(EmployeeFieldEvent $event): array
    {
        $isOffice = $event->event_type === EmployeeFieldEvent::TYPE_OFFICE;
        $isOpen = $this->isOpenFieldEvent($event);

        return [
            'event_id' => $event->id,
            'type' => $isOffice ? 'office' : ($event->event_type === EmployeeFieldEvent::TYPE_VISIT ? 'visit' : $event->event_type),
            'place_name' => $event->place_name,
            'address' => $event->address,
            'check_in_at' => $this->toApiTimestamp($event->check_in_at),
            'check_out_at' => $event->check_out_at ? $this->toApiTimestamp($event->check_out_at) : null,
            'is_open' => $isOpen,
            'lat' => $event->latitude,
            'lng' => $event->longitude,
        ];
    }

    private function formatCheckInStatusLabel(EmployeeFieldEvent $event): string
    {
        $time = $this->formatTimeLabel($event->check_in_at);
        $isOffice = $event->event_type === EmployeeFieldEvent::TYPE_OFFICE;

        return $isOffice
            ? 'Signed in at '.$this->officeDisplayName($event)." at {$time}"
            : "Visit started at {$time}";
    }

    private function formatCheckOutStatusLabel(EmployeeFieldEvent $event): string
    {
        $time = $this->formatTimeLabel($event->check_out_at);
        $isOffice = $event->event_type === EmployeeFieldEvent::TYPE_OFFICE;

        return $isOffice
            ? 'Signed out at '.$this->officeDisplayName($event)." at {$time}"
            : "Visit ended at {$time}";
    }

    private function officeDisplayName(EmployeeFieldEvent $event): string
    {
        $name = trim((string) ($event->place_name ?? ''));

        return $name !== ''
            ? $name
            : (string) config('gps.office_geofence.name', 'Isarva Infotech Pvt Ltd');
    }

    private function formatEventStatusLabels(EmployeeFieldEvent $event): array
    {
        $labels = [];

        if ($event->check_in_at) {
            $labels[] = $this->formatCheckInStatusLabel($event);
        }

        if ($event->check_out_at) {
            $labels[] = $this->formatCheckOutStatusLabel($event);
        }

        return $labels;
    }

    private function formatEventStatusLabel(EmployeeFieldEvent $event): ?string
    {
        $labels = $this->formatEventStatusLabels($event);

        return $labels[0] ?? null;
    }

    public function resolvePlaceName(string $eventType, float $latitude, float $longitude, string $requestedName): string
    {
        if ($this->isWithinOfficeGeofence($latitude, $longitude)) {
            return (string) config('gps.office_geofence.name', 'ISARVA Office');
        }

        if ($eventType === EmployeeFieldEvent::TYPE_OFFICE) {
            return 'Office';
        }

        return $requestedName !== '' ? $requestedName : 'Site visit';
    }

    public function isWithinOfficeGeofence(float $latitude, float $longitude): bool
    {
        if (! config('gps.office_geofence.enabled', true)) {
            return false;
        }

        $officeLat = (float) config('gps.office_geofence.latitude');
        $officeLng = (float) config('gps.office_geofence.longitude');
        $radiusMeters = (int) config('gps.office_geofence.radius_meters', 250);

        if ($officeLat === 0.0 && $officeLng === 0.0) {
            return false;
        }

        $distanceKm = $this->distanceKm($latitude, $longitude, $officeLat, $officeLng);

        return ($distanceKm * 1000) <= $radiusMeters;
    }

    private function buildMarkers(Collection $events, Collection $pings): array
    {
        $markers = [];

        foreach ($events as $event) {
            if (! $event->latitude || ! $event->longitude) {
                continue;
            }

            $markers[] = [
                'lat' => (float) $event->latitude,
                'lng' => (float) $event->longitude,
                'type' => $event->event_type,
                'label' => $event->place_name,
            ];
        }

        if ($markers === [] && $pings->isNotEmpty()) {
            $first = $pings->first();
            $last = $pings->last();

            $markers[] = [
                'lat' => (float) $first->latitude,
                'lng' => (float) $first->longitude,
                'type' => 'start',
                'label' => 'Start',
            ];

            if ($pings->count() > 1) {
                $markers[] = [
                    'lat' => (float) $last->latitude,
                    'lng' => (float) $last->longitude,
                    'type' => 'end',
                    'label' => 'End',
                ];
            }
        }

        return $markers;
    }

    private function formatDuration(int $minutes): string
    {
        if ($minutes < 60) {
            return $minutes.' minute'.($minutes === 1 ? '' : 's');
        }

        $hours = intdiv($minutes, 60);
        $remainder = $minutes % 60;

        if ($remainder === 0) {
            return $hours.' hour'.($hours === 1 ? '' : 's');
        }

        return sprintf('%d hr %d min', $hours, $remainder);
    }

    private function formatTimeRange(?Carbon $start, ?Carbon $end): ?string
    {
        if (! $start || ! $end) {
            return null;
        }

        return $this->formatTimeLabel($start).' - '.$this->formatTimeLabel($end);
    }

    public function parseInstant(string|Carbon $value): Carbon
    {
        $instant = $value instanceof Carbon
            ? $value->copy()
            : Carbon::parse($value);

        return $instant->timezone(self::DISPLAY_TIMEZONE);
    }

    public function toApiTimestamp(?Carbon $value): ?string
    {
        if (! $value) {
            return null;
        }

        return $this->parseInstant($value)->toIso8601String();
    }

    public function formatTimeLabel(?Carbon $value): ?string
    {
        if (! $value) {
            return null;
        }

        return $this->parseInstant($value)->format('g:i A');
    }
}
