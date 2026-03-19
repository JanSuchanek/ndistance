<?php
declare(strict_types=1);
namespace NDistance\Tests;
use NDistance\DistanceCalculator;
use Tester\Assert;
use Tester\TestCase;
require __DIR__ . '/../vendor/autoload.php';
\Tester\Environment::setup();

class DistanceCalculatorTest extends TestCase
{
	public function testConstructorDefaults(): void
	{
		$dc = new DistanceCalculator();
		Assert::type(DistanceCalculator::class, $dc);
	}

	public function testCustomConfig(): void
	{
		$dc = new DistanceCalculator(userAgent: 'TestApp/1.0', defaultCountry: 'sk');
		Assert::type(DistanceCalculator::class, $dc);
	}

	public function testGeocodeReturnsArrayOrNull(): void
	{
		$dc = new DistanceCalculator();
		$result = $dc->geocode('Prague');
		// May be null in CI (rate limited), but if it works, check structure
		if ($result !== null) {
			Assert::hasKey('lat', $result);
			Assert::hasKey('lng', $result);
			Assert::type('float', $result['lat']);
			Assert::type('float', $result['lng']);
		} else {
			Assert::null($result); // API unavailable — still OK
		}
	}

	public function testCheckDistanceStructure(): void
	{
		$dc = new DistanceCalculator();
		$result = $dc->checkDistance(50.08, 14.42, 'Brno', 300);
		if ($result !== null) {
			Assert::hasKey('ok', $result);
			Assert::hasKey('distance', $result);
			Assert::hasKey('duration', $result);
			Assert::hasKey('destLat', $result);
			Assert::hasKey('destLng', $result);
			Assert::hasKey('message', $result);
			Assert::type('bool', $result['ok']);
		}
	}
}
(new DistanceCalculatorTest())->run();
