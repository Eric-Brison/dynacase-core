<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Errors code used to checking CFLDID keyword
 * @class ErrorCodeRESE
 * @brief List all error code for CFLDID
 * @see ErrorCode
 */
class ErrorCodeCFLD
{
    /**
     * @errorCode
     * folder reference must be reference existing search
     */
    const CFLD0001 = 'CFLDID "%s" search is not found in attribute "%s"';
    /**
     * @errorCode
     * folder reference must be a search document
     */
    const CFLD0002 = 'CFLDID "%s" is not a search in attribute "%s"';
}
