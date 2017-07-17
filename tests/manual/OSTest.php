<?php

require_once '../JukeboxTestClass.php';

use Lib\OS;

final class OSTest extends JukeboxTestClass
{
    public function testStopService()
    {
        // TODO Check exec status manually.
//        $this->assertTrue(OS::stopService('fail2ban'));
    }

    public function testStartService()
    {
        //        $this->assertTrue(OS::startService('fail2ban'));
    }

    public function testServiceRunning()
    {
        $this->assertTrue(OS::isServiceRunning('apache2'));
    }
}
