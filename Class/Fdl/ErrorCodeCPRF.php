<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Errors code used to checking CPRFID keyword
 * @class ErrorCodeRESE
 * @brief List all error code for CPRFID
 * @see ErrorCode
 */
class ErrorCodeCPRF
{
    /**
     * @errorCode
     * folder reference must be reference existing profil
     */
    const CPRF0001 = 'CPROFID "%s" profil is not found in attribute "%s"';
    /**
     * @errorCode
     * folder reference must be a profil document
     */
    const CPRF0002 = 'CPROFID "%s" is not a profil in attribute "%s"';
}
