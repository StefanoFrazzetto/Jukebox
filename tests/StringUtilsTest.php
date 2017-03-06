<?php

require_once 'JukeboxTestClass.php';

use Lib\StringUtils;

class StringUtilsTest extends JukeboxTestClass
{
    public function testCleanString()
    {
        $string = '[Created_By_Stefano!]';
        $check = 'Created_By_Stefano';
        $this->assertEquals(StringUtils::cleanString($string), $check);
    }

    public function testContains()
    {
        $content = 'This is a test string';
        $check = 'test';

        $this->assertTrue(StringUtils::contains($content, $check));
    }

    public function testArrayContains()
    {
        $content = ['string' => 'This is a test string'];
        $check = 'test';

        $this->assertTrue(StringUtils::arrayContains($content, $check));
    }
}
