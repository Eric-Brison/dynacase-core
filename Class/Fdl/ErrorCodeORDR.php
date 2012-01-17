<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
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
     * the reference family must begin with a letter and must contains only alphanum characters
     */
    const ORDR0001 = 'syntax error family reference "%s" for ORDER';
    /**
     * the reference family must exists
     */
    const ORDR0002 = 'family reference "%s" not exists for ORDER';
    /**
     * the reference family must be a family
     */
    const ORDR0003 = 'family reference "%s" is not a family';
    /**
     * must have create privilege to import thid kind of document
     */
    const ORDR0004 = 'insufficient privileges to import ORDER of "%s" family ';
    /**
     * the ORDER cannot be imported because family is not completed
     */
    const ORDR0005 = 'family error detected "%s" for the ORDER  : %s';
    /**
     * when define ORDER the family reference is required
     */
    const ORDR0006 = 'family reference is empty for ORDER';
    /**
     * need define attribute
     */
    const ORDR0100 = 'attribute "%s" is not a part of "%s" family for ORDER';
}
