<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * ErrorCodeVIDEXTRACTOR
 *
 * @see \Dcp\Core\vidExtractor\vidExtractor
 */
class ErrorCodeVIDEXTRACTOR
{
    /**
     * @errorCode First argument of getVidsFromRawDoc() must be of type array
     */
    const VIDEXTRACTOR0001 = "First argument of getVidsFromRawDoc() must be of type array (found '%s' instead).";
    /**
     * @errorCode Missing 'id' field in first argument of getVidsFromRawDoc()
     */
    const VIDEXTRACTOR0002 = "Missing 'id' field in first argument of getVidsFromRawDoc().";
    /**
     * @errorCode Could not find document with id
     */
    const VIDEXTRACTOR0003 = "Could not find document with id '%d'.";
    /**
     * @errorCode Attribute not found for family
     */
    const VIDEXTRACTOR0004 = "Attribute '%s' not found for family '%s'.";
}
