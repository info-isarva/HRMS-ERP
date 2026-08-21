<?php

return [
    /*
    | When a check-in is within this radius of the office coordinates, place_name
    | is replaced with office_name (e.g. instead of a raw geocode like "Mysore Division").
    */
    'office_geofence' => [
        'enabled' => env('GPS_OFFICE_GEOFENCE_ENABLED', true),
        'latitude' => (float) env('GPS_OFFICE_LAT', 12.9548),
        'longitude' => (float) env('GPS_OFFICE_LNG', 74.8863),
        'radius_meters' => (int) env('GPS_OFFICE_RADIUS_M', 250),
        'name' => env('GPS_OFFICE_NAME', 'Isarva Infotech Pvt Ltd'),
        'address' => env('GPS_OFFICE_ADDRESS', 'Near HP Petrol Pump, Main Road, Airport Rd, Bajpe, Mangaluru 574142'),
    ],

    /*
    | Road-aligned route display (free hosted API — no extra server needed).
    | Sign up: https://openrouteservice.org/dev/#/signup
    */
    'route_matching' => [
        'enabled' => env('GPS_ROUTE_MATCHING_ENABLED', true),
        'api_key' => env('OPENROUTESERVICE_API_KEY'),
        'api_url' => env('OPENROUTESERVICE_API_URL', 'https://api.openrouteservice.org'),
        'profile' => env('GPS_ROUTE_MATCHING_PROFILE', 'driving-car'),
        'max_waypoints' => (int) env('GPS_ROUTE_MATCHING_MAX_WAYPOINTS', 40),
        'max_accuracy_meters' => (float) env('GPS_ROUTE_MATCHING_MAX_ACCURACY_M', 80),
        'max_jump_meters' => (float) env('GPS_ROUTE_MATCHING_MAX_JUMP_M', 400),
        'max_jump_seconds' => (int) env('GPS_ROUTE_MATCHING_MAX_JUMP_SEC', 30),
        'cache_hours' => (int) env('GPS_ROUTE_MATCHING_CACHE_HOURS', 12),
        'timeout_seconds' => (int) env('GPS_ROUTE_MATCHING_TIMEOUT_SEC', 20),
    ],
];
