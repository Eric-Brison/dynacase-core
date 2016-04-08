<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Errors code used when import ORDER
 * @class ErrorCodeORDR
 * @brief List all error code for ORDR
 */
class ErrorCodeORDR
{
    /**
     * @errorCode
     * the reference family must begin with a letter and must contains only alphanum characters
     */
    const ORDR0001 = 'syntax error family reference "%s" for ORDER';
    /**
     * @errorCode
     * the reference family must exists
     */
    const ORDR0002 = 'family reference "%s" not exists for ORDER';
    /**
     * @errorCode
     * the reference family must be a family
     */
    const ORDR0003 = 'family reference "%s" is not a family';
    /**
     * @errorCode
     * must have create privilege to import thid kind of document
     */
    const ORDR0004 = 'insufficient privileges to import ORDER of "%s" family ';
    /**
     * @errorCode
     * the ORDER cannot be imported because family is not completed
     */
    const ORDR0005 = 'family error detected "%s" for the ORDER  : %s';
    /**
     * @errorCode
     * when define ORDER the family reference is required
     */
    const ORDR0006 = 'family reference is empty for ORDER';
    /**
     * @errorCode
     * need define attribute
     */
    const ORDR0100 = 'Invalid ORDER attribute: attribute "%s" is not an attribute of "%s" family';
}
