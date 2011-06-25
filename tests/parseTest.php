<?php

class parseTest extends PHPUnit_Framework_TestCase {

    public function test_Baseline() {

        $api_object = new \Transit\Bart();

        $this->assertTrue(true, "Bootstrap");

        $test_file = __DIR__ . '/resources/etd24TH.xml';

        $this->assertTrue(file_exists($test_file), "has test xml file $test_file");

        $xml = file_get_contents($test_file);

        $array = $api_object->xmlToArray($xml);

        $report = $api_object->extractArrivals($array);

        $this->assertEquals(2,count($report), "Two directions returned");

        $this->assertEquals(2, $report['South'][0]['minutes'], "Next train");

        $this->assertEquals("SFO/Millbrae", $report['South'][1]['destination'], "Destination");

    }

}