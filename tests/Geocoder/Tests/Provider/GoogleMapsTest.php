<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\GoogleMaps;

class GoogleMapsTest extends TestCase
{
    /**
     * @var string
     */
    private $testAPIKey = 'fake_key';

    public function testGetName()
    {
        $provider = new GoogleMaps($this->getMockAdapter($this->never()));
        $this->assertEquals('google_maps', $provider->getName());
    }

    /**
     * @expectedException Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "http://maps.googleapis.com/maps/api/geocode/json?address=foobar".
     */
    public function testGetGeocodedData()
    {
        $provider = new GoogleMaps($this->getMockAdapter());
        $provider->geocode('foobar');
    }

    /**
     * @expectedException Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "http://maps.googleapis.com/maps/api/geocode/json?address=".
     */
    public function testGetGeocodedDataWithNull()
    {
        $provider = new GoogleMaps($this->getMockAdapter());
        $provider->geocode(null);
    }

    /**
     * @expectedException Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "http://maps.googleapis.com/maps/api/geocode/json?address=".
     */
    public function testGetGeocodedDataWithEmpty()
    {
        $provider = new GoogleMaps($this->getMockAdapter());
        $provider->geocode('');
    }

    /**
     * @expectedException Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The GoogleMaps provider does not support IP addresses, only street addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv4()
    {
        $provider = new GoogleMaps($this->getMockAdapter($this->never()));
        $provider->geocode('127.0.0.1');
    }

    /**
     * @expectedException Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The GoogleMaps provider does not support IP addresses, only street addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv6()
    {
        $provider = new GoogleMaps($this->getMockAdapter($this->never()));
        $provider->geocode('::1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The GoogleMaps provider does not support IP addresses, only street addresses.
     */
    public function testGetGeocodedDataWithRealIp()
    {
        $provider = new GoogleMaps($this->getAdapter());
        $provider->geocode('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "http://maps.googleapis.com/maps/api/geocode/json?address=10%20avenue%20Gambetta%2C%20Paris%2C%20France".
     */
    public function testGetGeocodedDataWithAddressGetsNullContent()
    {
        $provider = new GoogleMaps($this->getMockAdapterReturns(null));
        $provider->geocode('10 avenue Gambetta, Paris, France');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "http://maps.googleapis.com/maps/api/geocode/json?address=10%20avenue%20Gambetta%2C%20Paris%2C%20France".
     */
    public function testGetGeocodedDataWithAddressGetsEmptyContent()
    {
        $provider = new GoogleMaps($this->getMockAdapterReturns('{"status":"OK"}'));
        $provider->geocode('10 avenue Gambetta, Paris, France');
    }

    /**
     * @expectedException \Geocoder\Exception\QuotaExceeded
     * @expectedExceptionMessage Daily quota exceeded http://maps.googleapis.com/maps/api/geocode/json?address=10%20avenue%20Gambetta%2C%20Paris%2C%20France
     */
    public function testGetGeocodedDataWithQuotaExceeded()
    {
        $provider = new GoogleMaps($this->getMockAdapterReturns('{"status":"OVER_QUERY_LIMIT"}'));
        $provider->geocode('10 avenue Gambetta, Paris, France');
    }

    public function testGetGeocodedDataWithRealAddress()
    {
        $provider = new GoogleMaps($this->getAdapter(), 'fr-FR', 'Île-de-France');
        $results  = $provider->geocode('10 avenue Gambetta, Paris, France');

        $this->assertCount(1, $results);

        $result = $results[0]->toArray();
        $this->assertEquals(48.8630462, $result['latitude'], '', 0.001);
        $this->assertEquals(2.3882487, $result['longitude'], '', 0.001);
        $this->assertArrayHasKey('south', $result['bounds']);
        $this->assertArrayHasKey('west', $result['bounds']);
        $this->assertArrayHasKey('north', $result['bounds']);
        $this->assertArrayHasKey('east', $result['bounds']);
        $this->assertEquals(48.8630462, $result['bounds']['south'], '', 0.001);
        $this->assertEquals(2.3882487, $result['bounds']['west'], '', 0.001);
        $this->assertEquals(48.8630462, $result['bounds']['north'], '', 0.001);
        $this->assertEquals(2.3882487, $result['bounds']['east'], '', 0.001);
        $this->assertEquals(10, $result['streetNumber']);
        $this->assertEquals('Avenue Gambetta', $result['streetName']);
        $this->assertEquals(75020, $result['postalCode']);
        $this->assertEquals('Paris', $result['locality']);
        $this->assertEquals('Paris', $result['county']);
        $this->assertEquals('Île-de-France', $result['region']);
        $this->assertEquals('France', $result['country']);
        $this->assertEquals('FR', $result['countryCode']);

        // not provided
        $this->assertNull($result['timezone']);
    }

    public function testGetGeocodedDataWithRealAddressWithSsl()
    {
        $provider = new GoogleMaps($this->getAdapter(), null, null, true);
        $results  = $provider->geocode('10 avenue Gambetta, Paris, France');

        $this->assertCount(1, $results);

        $result = $results[0]->toArray();
        $this->assertEquals(48.8630462, $result['latitude'], '', 0.001);
        $this->assertEquals(2.3882487, $result['longitude'], '', 0.001);
        $this->assertArrayHasKey('south', $result['bounds']);
        $this->assertArrayHasKey('west', $result['bounds']);
        $this->assertArrayHasKey('north', $result['bounds']);
        $this->assertArrayHasKey('east', $result['bounds']);
        $this->assertEquals(48.8630462, $result['bounds']['south'], '', 0.001);
        $this->assertEquals(2.3882487, $result['bounds']['west'], '', 0.001);
        $this->assertEquals(48.8630462, $result['bounds']['north'], '', 0.001);
        $this->assertEquals(2.3882487, $result['bounds']['east'], '', 0.001);
        $this->assertEquals(10, $result['streetNumber']);
        $this->assertEquals('Avenue Gambetta', $result['streetName']);
        $this->assertEquals(75020, $result['postalCode']);
        $this->assertEquals('Paris', $result['locality']);
        $this->assertEquals('Paris', $result['county']);
        $this->assertEquals('Île-de-France', $result['region']);
        $this->assertEquals('France', $result['country']);
        $this->assertEquals('FR', $result['countryCode']);

        // not provided
        $this->assertNull($result['timezone']);
    }

    public function testGetGeocodedDataBoundsWithRealAddressForNonRooftopLocation()
    {
        $provider = new GoogleMaps($this->getAdapter());
        $results  = $provider->geocode('Paris, France');

        $this->assertCount(1, $results);

        $result = $results[0]->toArray();
        $this->assertNotNull($result['bounds']);
        $this->assertArrayHasKey('south', $result['bounds']);
        $this->assertArrayHasKey('west', $result['bounds']);
        $this->assertArrayHasKey('north', $result['bounds']);
        $this->assertArrayHasKey('east', $result['bounds']);
        $this->assertEquals(48.815573, $result['bounds']['south'], '', 0.0001);
        $this->assertEquals(2.224199, $result['bounds']['west'], '', 0.0001);
        $this->assertEquals(48.902145, $result['bounds']['north'], '', 0.0001);
        $this->assertEquals(2.4699209, $result['bounds']['east'], '', 0.0001);
    }

    public function testGetGeocodedDataWithRealAddressReturnsMultipleResults()
    {
        $provider = new GoogleMaps($this->getAdapter());
        $results  = $provider->geocode('Paris');

        $this->assertCount(5, $results);

        $results = array_map(function ($res) {
            return $res->toArray();
        }, $results);

        $this->assertEquals(48.856614, $results[0]['latitude'], '', 0.001);
        $this->assertEquals(2.3522219, $results[0]['longitude'], '', 0.001);
        $this->assertEquals('Paris', $results[0]['locality']);
        $this->assertEquals('France', $results[0]['country']);
        $this->assertEquals('FR', $results[0]['countryCode']);

        $this->assertEquals(33.6609389, $results[1]['latitude'], '', 0.001);
        $this->assertEquals(-95.555513, $results[1]['longitude'], '', 0.001);
        $this->assertEquals('Paris', $results[1]['locality']);
        $this->assertEquals('United States', $results[1]['country']);
        $this->assertEquals('US', $results[1]['countryCode']);

        $this->assertEquals(36.3020023, $results[2]['latitude'], '', 0.001);
        $this->assertEquals(-88.3267107, $results[2]['longitude'], '', 0.001);
        $this->assertEquals('Paris', $results[2]['locality']);
        $this->assertEquals('United States', $results[2]['country']);
        $this->assertEquals('US', $results[2]['countryCode']);

        $this->assertEquals(39.611146, $results[3]['latitude'], '', 0.001);
        $this->assertEquals(-87.6961374, $results[3]['longitude'], '', 0.001);
        $this->assertEquals('Paris', $results[3]['locality']);
        $this->assertEquals('United States', $results[3]['country']);
        $this->assertEquals('US', $results[3]['countryCode']);

        $this->assertEquals(38.2097987, $results[4]['latitude'], '', 0.001);
        $this->assertEquals(-84.2529869, $results[4]['longitude'], '', 0.001);
        $this->assertEquals('Paris', $results[4]['locality']);
        $this->assertEquals('United States', $results[4]['country']);
        $this->assertEquals('US', $results[4]['countryCode']);
    }

    /**
     * @expectedException Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "http://maps.googleapis.com/maps/api/geocode/json?address=1.000000%2C2.000000".
     */
    public function testGetReversedData()
    {
        $provider = new GoogleMaps($this->getMockAdapter());
        $provider->reverse(1, 2);
    }

    public function testGetReversedDataWithRealCoordinates()
    {
        $provider = new GoogleMaps($this->getAdapter());
        $result   = $provider->reverse(48.8631507, 2.388911);

        $result = $result[0]->toArray();
        $this->assertEquals(1, $result['streetNumber']);
        $this->assertEquals('Avenue Gambetta', $result['streetName']);
        $this->assertEquals(75020, $result['postalCode']);
        $this->assertEquals('Paris', $result['locality']);
        $this->assertEquals('Paris', $result['county']);
        $this->assertEquals('Île-de-France', $result['region']);
        $this->assertEquals('France', $result['country']);
        $this->assertEquals('FR', $result['countryCode']);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "http://maps.googleapis.com/maps/api/geocode/json?address=48.863151%2C2.388911".
     */
    public function testGetReversedDataWithCoordinatesGetsNullContent()
    {
        $provider = new GoogleMaps($this->getMockAdapterReturns(null));
        $provider->reverse(48.8631507, 2.388911);
    }

    public function testGetGeocodedDataWithCityDistrict()
    {
        $provider = new GoogleMaps($this->getAdapter());
        $results  = $provider->geocode('Kalbacher Hauptstraße 10, 60437 Frankfurt, Germany');

        $this->assertCount(1, $results);

        $result = $results[0]->toArray();
        $this->assertEquals('Kalbach-Riedberg', $result['subLocality']);
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage API key is invalid http://maps.googleapis.com/maps/api/geocode/json?address=10%20avenue%20Gambetta%2C%20Paris%2C%20France
     */
    public function testGetGeocodedDataWithInavlidApiKey()
    {
        $provider = new GoogleMaps($this->getMockAdapterReturns('{"error_message":"The provided API key is invalid.", "status":"REQUEST_DENIED"}'));
        $provider->geocode('10 avenue Gambetta, Paris, France');
    }

    public function testGetGeocodedDataWithRealValidApiKey()
    {
        if (!isset($_SERVER['GOOGLE_GEOCODING_KEY'])) {
            $this->markTestSkipped('You need to configure the GOOGLE_GEOCODING_KEY value in phpunit.xml');
        }

        $provider = new GoogleMaps($this->getAdapter(), null, null, true, $_SERVER['GOOGLE_GEOCODING_KEY']);

        $data = $provider->geocode('Columbia University');
        $data = $data[0];

        $this->assertNotNull($data['latitude']);
        $this->assertNotNull($data['longitude']);
        $this->assertNotNull($data['bounds']['south']);
        $this->assertNotNull($data['bounds']['west']);
        $this->assertNotNull($data['bounds']['north']);
        $this->assertNotNull($data['bounds']['east']);
        $this->assertEquals('New York', $data['locality']);
        $this->assertEquals('Manhattan', $data['subLocality']);
        $this->assertEquals('New York', $data['region']);
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage API key is invalid https://maps.googleapis.com/maps/api/geocode/json?address=Columbia%20University&key=fake_key
     */
    public function testGetGeocodedDataWithRealInvalidApiKey()
    {
        $provider = new GoogleMaps($this->getAdapter(), null, null, true, $this->testAPIKey);

        $provider->geocode('Columbia University');
    }
}
