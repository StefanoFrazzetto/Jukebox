<?php

require_once __DIR__.'/../JukeboxTestClass.php';

use Lib\System;

final class DeviceTest extends JukeboxTestClass
{
    /** @var System $device the device class instance */
    private $device;

    public function setUp()
    {
        $this->device = new System();
    }

    public function testFixApacheIni()
    {
        $method = $this->getPrivateMethod('fixApacheConfig');

        $this->assertTrue($method->invokeArgs($this->device, []));
    }

    public function testFixPermissions()
    {
        $method = $this->getPrivateMethod('fixPermissions');

        $this->assertTrue($method->invokeArgs($this->device, []));
    }

    public function testSoftReset()
    {
        $this->assertTrue($this->device->softReset());
    }

    public function testHardReset()
    {
        $this->assertTrue($this->device->hardReset());
    }

    /**
     * Return a private method making it public.
     *
     * @param string $name the method name
     *
     * @return ReflectionMethod the method
     */
    private static function getPrivateMethod($name)
    {
        $class = new ReflectionClass(System::class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }
}
