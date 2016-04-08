<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Utils\CSVFormatDetector;

class Sample
{
    public $str = null;
    public $count = 0;
    public $weight = 1;
    public $score = 0;
    
    public function __construct($str, $count, $weight = 1)
    {
        $this->str = $str;
        $this->count = $count;
        $this->weight = $this->count * $weight;
    }
}

class SampleAccumulator
{
    /**
     * @var Sample[]
     */
    public $samples = array();
    
    public function add($str, $count, $weight = 1)
    {
        $this->samples[] = new Sample($str, $count, $weight);
    }
    
    public function updateScores()
    {
        $totalWeight = 0;
        foreach ($this->samples as & $sample) {
            $totalWeight+= $sample->weight;
        }
        unset($sample);
        foreach ($this->samples as & $sample) {
            if ($totalWeight == 0) {
                $sample->score = 0;
            } else {
                $sample->score = $sample->weight / $totalWeight;
            }
        }
        unset($sample);
    }
    
    public function getMergedSamples()
    {
        $this->updateScores();
        $this->sortByScore($this->samples);
        $mergedSamples = array();
        foreach ($this->samples as & $sample) {
            if (!isset($mergesSamples[$sample->str])) {
                $mergesSamples[$sample->str] = array();
            }
            if (isset($mergedSamples[$sample->str]->str)) {
                $mergedSamples[$sample->str]->count+= $sample->count;
                $mergedSamples[$sample->str]->score+= $sample->score;
            } else {
                $mergedSamples[$sample->str] = $sample;
            }
        }
        unset($sample);
        $this->sortByScore($mergedSamples);
        return array_values($mergedSamples);
    }
    /**
     * Return sample with highest confidence score or NULL if there is no samples
     *
     * @return null|Sample
     *
     */
    public function getCandidate($minConfidence = 0)
    {
        $samples = $this->getMergedSamples();
        $samples = array_filter($samples, function (Sample & $sample) use ($minConfidence)
        {
            return ($sample->score >= $minConfidence);
        });
        if (count($samples) <= 0) {
            return null;
        }
        return $samples[0];
    }
    protected function sortByScore(&$samples)
    {
        uasort($samples, function (Sample $a, Sample $b)
        {
            if ($a->score == $b->score) {
                return 0;
            }
            return ($a->score < $b->score) ? 1 : -1;
        });
    }
    
    public function dump($merged = true)
    {
        if ($merged) {
            $samples = $this->getMergedSamples();
        } else {
            $this->updateScores();
            $samples = $this->samples;
        }
        foreach ($samples as & $sample) {
            printf("\t{[%s], %s, %s}\n", $sample->str, $sample->count, $sample->score);
        }
        unset($sample);
    }
}
/**
 * Class Detector
 *
 * Try to detect CSV separator (either ";" or ",") and enclosure (either "'" or '"') by applying some rules to detect
 * patterns and count scores when there is a match: highest score >= 75% wins!
 *
 * Not bulletproof, but it seems to perform correctly (i.e. as expected) with CSV from dynacase-core and tests.
 *
 * Observed confidence scores being either 0% or > 90% (no score observed in-between).
 *
 * @package Dcp\Utils\CSVFormatDetector
 */
class Detector
{
    public $debug;
    public $separators;
    public $enclosures;
    
    public function __construct()
    {
        $this->reset();
    }
    /**
     * Reset detector's internal logic.
     */
    public function reset()
    {
        $this->debug = false;
        $this->separators = array(
            ',',
            ';'
        );
        $this->enclosures = array(
            "'",
            '"'
        );
    }
    /**
     * Print a debug message if debug is enable.
     *
     * @param $msg
     */
    protected function debug($msg)
    {
        if ($this->debug === true) {
            print $msg;
        }
    }
    /**
     * Detect the CSV separator and enclosure of the given CSV text data
     *
     * The returned array contains the detected separator and enclosure with their confidence score expressed as a
     * floating point value in the range [0, 1]:
     *
     * Example:
     *
     * array(
     *     'separator' => array(
     *         'char' => ';'
     *         'confidence' => 1
     *     ) ,
     *     'enclosure' => array(
     *         'char' => null,
     *         'confidence' => 0
     *     )
     * )
     *
     * null values indicates that the corresponding value could not be detected.
     *
     * @param $text
     * @return array
     */
    public function detect($text)
    {
        $result = array(
            'separator' => array(
                'char' => null,
                'confidence' => 0
            ) ,
            'enclosure' => array(
                'char' => null,
                'confidence' => 0
            )
        );
        
        if (!is_string($text)) {
            return $result;
        }
        /*
         * Detect separators
        */
        $stats = new SampleAccumulator();
        foreach ($this->separators as $sep) {
            $sepRE = preg_quote($sep, '/');
            foreach (array(
                // Trailing separators (minimum length of 2): e.g. xxx(;;)
                sprintf('/(%s{2,})$/ms', $sepRE) => array(
                    'count' => 1,
                    'length' => 1
                ) ,
                // Consecutive separators (minimum length of 2): e.g. xxx(;;)xxx
                sprintf('/(%s{2,})./ms', $sepRE) => array(
                    'count' => 1,
                    'length' => 1
                ) ,
                // Sequences of only separators and identifiers (with minimum identifiers length of 3)
                sprintf('/((?:%s\w{3,})+)/uims', $sepRE) => array(
                    'count' => 1,
                    'length' => 1
                )
            ) as $re => $weightMultiplier) {
                if (preg_match_all($re, $text, $m)) {
                    /*
                     * Add one for each pattern found:
                     * the more we match, the greater the score will get
                    */
                    $stats->add($sep, count($m[1]) , $weightMultiplier['count']);
                    foreach ($m[1] as $n) {
                        /*
                         * Add score with length of the found patterns:
                         * the longer we match, the greater the score will get
                        */
                        $stats->add($sep, strlen($n[1]) , $weightMultiplier['length']);
                    }
                }
            }
        }
        if ($this->debug) {
            $stats->dump();
        }
        /* Get best separator candidate (i.e. the one with the highest confidence score) */
        $candidate = $stats->getCandidate(0.75);
        if ($candidate === null) {
            $this->debug(sprintf("    Could not identify separator!\n"));
            return $result;
        }
        $this->debug(sprintf("    Identified separator [%s] with %.02f%% confidence (count %d).\n", $candidate->str, 100 * $candidate->score, $candidate->count));
        $this->debug(sprintf("\n"));
        $sepChar = $candidate->str;
        $result['separator']['char'] = $sepChar;
        $result['separator']['confidence'] = $candidate->score;
        /*
         * Detect enclosures
        */
        $stats = new SampleAccumulator();
        $sepRE = preg_quote($sepChar, '/');
        foreach ($this->enclosures as $enc) {
            $encRE = preg_quote($enc, '/');
            foreach (array(
                // Search for: xxx(;"xxx";)xxx
                sprintf('/(%s%s[^%s]+%s(?:%s|$))/ms', $sepRE, $encRE, $encRE, $encRE, $sepRE) ,
            ) as $re) {
                if (preg_match_all($re, $text, $m)) {
                    $stats->add($enc, count($m[1]));
                }
            }
        }
        if ($this->debug) {
            $stats->dump();
        }
        /* Get best enclosure candidate (i.e. the one with the highest confidence score) */
        $candidate = $stats->getCandidate(0.75);
        if ($candidate === null) {
            $this->debug(sprintf("    Could not identify enclosure!\n"));
            $this->debug(sprintf("\n"));
            return $result;
        }
        $this->debug(sprintf("    Identified enclosure [%s] with %.02f%% confidence (count %d).\n", $candidate->str, 100 * $candidate->score, $candidate->count));
        $this->debug(sprintf("\n"));
        $encChar = $candidate->str;
        $result['enclosure']['char'] = $encChar;
        $result['enclosure']['confidence'] = $candidate->score;
        return $result;
    }
}
