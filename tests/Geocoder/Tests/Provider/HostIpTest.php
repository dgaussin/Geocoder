<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\HostIp;

class HostIpTest extends TestCase
{
    public function testGetName()
    {
        $provider = new HostIp($this->getMockAdapter($this->never()));
        $this->assertEquals('host_ip', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The HostIp provider does not support Street addresses.
     */
    public function testGetGeocodedDataWithNull()
    {
        $provider = new HostIp($this->getMockAdapter($this->never()));
        $provider->geocode(null);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The HostIp provider does not support Street addresses.
     */
    public function testGetGeocodedDataWithEmpty()
    {
        $provider = new HostIp($this->getMockAdapter($this->never()));
        $provider->geocode('');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The HostIp provider does not support Street addresses.
     */
    public function testGetGeocodedDataWithAddress()
    {
        $provider = new HostIp($this->getMockAdapter($this->never()));
        $provider->geocode('10 avenue Gambetta, Paris, France');
    }

    public function testGetGeocodedDataWithLocalhostIPv4()
    {
        $provider = new HostIp($this->getMockAdapter($this->never()));
        $result   = $provider->geocode('127.0.0.1');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertArrayNotHasKey('latitude', $result);
        $this->assertArrayNotHasKey('longitude', $result);
        $this->assertArrayNotHasKey('postalCode', $result);
        $this->assertArrayNotHasKey('timezone', $result);

        $this->assertEquals('localhost', $result['locality']);
        $this->assertEquals('localhost', $result['region']);
        $this->assertEquals('localhost', $result['county']);
        $this->assertEquals('localhost', $result['country']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The HostIp provider does not support IPv6 addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv6()
    {
        $provider = new HostIp($this->getMockAdapter($this->never()));
        $provider->geocode('::1');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "http://api.hostip.info/get_json.php?ip=88.188.221.14&position=true".
     */
    public function testGetGeocodedDataWithRealIPv4GetsNullContent()
    {
        $provider = new HostIp($this->getMockAdapterReturns(null));
        $provider->geocode('88.188.221.14');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "http://api.hostip.info/get_json.php?ip=88.188.221.14&position=true".
     */
    public function testGetGeocodedDataWithRealIPv4GetsEmptyContent()
    {
        $provider = new HostIp($this->getMockAdapterReturns(''));
        $provider->geocode('88.188.221.14');
    }

    public function testGetGeocodedDataWithRealIPv4()
    {
        $provider = new HostIp($this->getAdapter());
        $result   = $provider->geocode('88.188.221.14');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(45.5333, $result['latitude'], '', 0.0001);
        $this->assertEquals(2.6167, $result['longitude'], '', 0.0001);
        $this->assertNull($result['postalCode']);
        $this->assertEquals('Aulnat', $result['locality']);
        $this->assertNull($result['region']);
        $this->assertEquals('FRANCE', $result['country']);
        $this->assertEquals('FR', $result['countryCode']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The HostIp provider does not support IPv6 addresses.
     */
    public function testGetGeocodedDataWithRealIPv6()
    {
        $provider = new HostIp($this->getAdapter());
        $provider->geocode('::ffff:88.188.221.14');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The HostIp provider is not able to do reverse geocoding.
     */
    public function testGetReverseData()
    {
        $provider = new HostIp($this->getMockAdapter($this->never()));
        $provider->reverse(1, 2);
    }

    public function testGetGeocodedDataWithAnotherIp()
    {
        $provider = new HostIp($this->getAdapter());
        $result   = $provider->geocode('33.33.33.22');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
    }
}
