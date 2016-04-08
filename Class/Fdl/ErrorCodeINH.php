<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Errors code used for input help
 * @class ErrorCodeINH
 * @see ErrorCode
 * @brief List all error code for input help access
 *
 * @see ErrorCode
 */
class ErrorCodeINH
{
    /**
     * @errorCode
     * structure must be an array of array
     */
    const INH0001 = 'structure error in result function "%s" defined in "%s" attribute';
    /**
     * @errorCode
     * the input help function rmust return only utf-8 encoding characters
     */
    const INH0002 = 'encoding error in result for "%s" function "%s" defined in "%s" attribute';
}
