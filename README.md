# NDistance — Geocoding & Driving Distance

Address geocoding + driving distance calculation using free APIs. No API key needed.

- **Nominatim** (OpenStreetMap) — address → lat/lng
- **OSRM** — driving distance & duration

## Installation

```bash
composer require jansuchanek/ndistance
```

## Usage

```php
use NDistance\DistanceCalculator;

$calc = new DistanceCalculator(
    userAgent: 'MyApp/1.0',
    defaultCountry: 'cz',
);

// Geocode address
$coords = $calc->geocode('Brno, Česko');
// ['lat' => 49.19, 'lng' => 16.61]

// Driving distance between coordinates
$route = $calc->drivingDistance(50.08, 14.42, 49.19, 16.61);
// ['distance' => 205.3, 'duration' => 125]  (km, minutes)

// Check delivery range
$result = $calc->checkDistance(50.08, 14.42, 'Brno', 250.0);
// ['ok' => true, 'distance' => 205.3, 'duration' => 125, ...]
```

## Requirements

- PHP >= 8.1
- ext-curl
