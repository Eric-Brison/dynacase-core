<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp;

if (! defined("ALTSEPCHAR")) {
    define("ALTSEPCHAR", ' --- ');
}
class WriteCsv
{
    
    static public $enclosure = '';
    static public $separator = ';';
    static public $encoding = "utf-8";
    /**
     * @param resource $handler
     * @param array $data
     */
    static function fput($handler, array $data)
    {
        
        if (empty(self::$enclosure)) {
            //str_replace(SEPCHAR, ALTSEPCHAR
            $cleanData = array_map(function ($item)
            {
                return str_replace(array(
                    "\n",
                    self::$separator,
                    "\r"
                ) , array(
                    "\\n",
                    ALTSEPCHAR,
                    ""
                ) , $item);
            }
            , $data);
            $s = implode(self::$separator, $cleanData);
            if (self::$encoding === "iso8859-15") {
                $s = utf8_decode($s);
            }
            fputs($handler, $s . "\n");
        } else {
            if (self::$encoding === "iso8859-15") {
                $data = array_map(function ($cell)
                {
                    return utf8_decode($cell);
                }
                , $data);
            }
            fputcsv($handler, $data, self::$separator, self::$enclosure);
        }
    }
}
