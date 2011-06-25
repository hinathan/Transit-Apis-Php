<?php
/**
 * An API for BART station data
 *
 * Pretty trivial
 *
 * PHP version 5.3
 *
 * @category  Default
 * @package   Transit
 * @author    Nathan Schmidt <nschmidt@gmail.com>
 * @copyright 2011 Nathan Schmidt
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version   CVS: unused
 * @link      None
 *
 */
namespace Transit;
/**
 * Bart
 *
 * @category Default
 * @package  Bart
 * @author   Nathan Schmidt <nschmidt@gmail.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php BSD License
 * @link     None
 *
 */
class Bart
{
    const STATION = '24TH';
    /* This is the 'key for everybody' provided by BART */
    const API_KEY = 'MW9S-E7SL-26DU-VV8V';
    const URL_TEMPLATE = 'http://api.bart.gov/api/etd.aspx?cmd=etd&orig=%s&key=%s';
    /* Max results per station */
    protected $numResults = 2;

    /**
    * For a given station id, fetch the next trains coming and going
    *
    * @param string $station The BART-provided station id
    *
    * @return array The finished arrays of arrivals
    *
    */
    public function getArrivals($station = self::STATION)
    {
        $url = sprintf(self::URL_TEMPLATE, $station, self::API_KEY);
        $payload = file_get_contents($url);
        $array = $this->xmlToArray($payload);
        return $this->extractArrivals($array);
    }

    /**
    * Dump the provided xml into a php array
    *
    * @param string $xml XML string
    *
    * @return array An array representation of $xml
    *
    */
    public function xmlToArray($xml)
    {
        $xmlTree = simplexml_load_string($xml);
        $phpTree = json_decode(json_encode((array) $xmlTree), true);
        return $phpTree;
    }

    /**
    * Extract arrivals report from the passed array
    *
    * @param array $array array derived from an XML document
    *
    * @return array A parsed array of arrivals
    *
    */
    public function extractArrivals($array)
    {

        if (!isset($array['station']['etd'])) {
            throw new \Exception("Unknown response payload from BART API");
        }

        $etds = $array['station']['etd'];

        $mezzanine = array();

        foreach ($etds as $destinationInfo) {

            if (!isset($destinationInfo['destination'])) {
                throw new \Exception("Unknown destination from BART API");
            }

            $destination = $destinationInfo['destination'];

            $estimates = $destinationInfo['estimate'];

            // just one result means no array of result hashses,
            // instead there's just a single top-level hash.

            if (!isset($estimates[0])) {
                $estimates = array($estimates);
            }

            foreach ($estimates as $arrival) {
                if ($arrival['minutes'] == 'Arrived') {
                    continue;
                }
                $info = array('destination' => $destination);
                foreach (array('minutes', 'length', 'direction') as $key) {
                    $info[$key] = $arrival[$key];
                }
                $mezzanine[$arrival['direction']][] = $info;
            }
        }

        $final = array();

        foreach ($mezzanine as $direction=>&$arrivalSet) {
            $sortFunc = function ($a, $b) {
                return $a['minutes'] >= $b['minutes'];
            };
            uasort($arrivalSet, $sortFunc);
            $arrivalSet = array_slice($arrivalSet, 0, $this->numResults);

            $final[$direction] = $arrivalSet;
        }

        return $final;
    }
}
