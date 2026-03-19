<?php

declare(strict_types=1);

namespace NDistance;

/**
 * Geocoding + driving distance calculation using free APIs.
 *
 * - Nominatim (OpenStreetMap) for address → lat/lng
 * - OSRM (Open Source Routing Machine) for driving distance/duration
 *
 * No API key required. Respects Nominatim usage policy.
 */
class DistanceCalculator
{
	public function __construct(
		private string $userAgent = 'NDistance/1.0',
		private string $defaultCountry = 'cz',
	) {}


	/**
	 * Geocode an address to coordinates.
	 *
	 * @return array{lat: float, lng: float}|null
	 */
	public function geocode(string $address): ?array
	{
		$query = urlencode($address);
		$url = "https://nominatim.openstreetmap.org/search?q={$query}&format=json&limit=1&countrycodes={$this->defaultCountry}";

		$json = $this->curlGet($url);
		if (!$json) {
			return null;
		}

		$data = json_decode($json, true);
		if (empty($data)) {
			return null;
		}

		return [
			'lat' => (float) $data[0]['lat'],
			'lng' => (float) $data[0]['lon'],
		];
	}


	/**
	 * Calculate driving distance between two coordinates.
	 *
	 * @return array{distance: float, duration: int}|null  distance in km, duration in minutes
	 */
	public function drivingDistance(float $fromLat, float $fromLng, float $toLat, float $toLng): ?array
	{
		$url = "https://router.project-osrm.org/route/v1/driving/{$fromLng},{$fromLat};{$toLng},{$toLat}?overview=false";

		$json = $this->curlGet($url);
		if (!$json) {
			return null;
		}

		$data = json_decode($json, true);
		if (empty($data['routes'])) {
			return null;
		}

		return [
			'distance' => round($data['routes'][0]['distance'] / 1000, 1),
			'duration' => (int) round($data['routes'][0]['duration'] / 60),
		];
	}


	/**
	 * Check if destination address is within max distance from origin.
	 *
	 * @return array{ok: bool, distance: float, duration: int, destLat: float, destLng: float, message: string}|null
	 */
	public function checkDistance(
		float $originLat,
		float $originLng,
		string $destinationAddress,
		float $maxDistanceKm,
	): ?array {
		$dest = $this->geocode($destinationAddress);
		if (!$dest) {
			return null;
		}

		$route = $this->drivingDistance($originLat, $originLng, $dest['lat'], $dest['lng']);
		if (!$route) {
			return null;
		}

		$ok = $route['distance'] <= $maxDistanceKm;

		return [
			'ok' => $ok,
			'distance' => $route['distance'],
			'duration' => $route['duration'],
			'destLat' => $dest['lat'],
			'destLng' => $dest['lng'],
			'message' => $ok
				? "Doručení: {$route['distance']} km, ~{$route['duration']} min autem ✓"
				: "Bohužel, na tuto adresu nedoručujeme ({$route['distance']} km). Maximum je {$maxDistanceKm} km.",
		];
	}


	private function curlGet(string $url): ?string
	{
		$ch = curl_init($url);
		curl_setopt_array($ch, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 5,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_USERAGENT => $this->userAgent,
			CURLOPT_SSL_VERIFYPEER => true,
		]);
		$result = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		return ($result !== false && $httpCode === 200) ? $result : null;
	}
}
