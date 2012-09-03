<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Errors code used when import KEYS
 * @class ErrorCodeKEYS
 * @brief List all error code for KEYS
 * @see ErrorCode
 */
class ErrorCodeKEYS
{
    /**
     * @errorCode
     * the reference family must begin with a letter and must contains only alphanum characters
     */
    const KEYS0001 = 'syntax error family reference "%s" for KEYS';
    /**
     * @errorCode
     * the reference family must exists
     */
    const KEYS0002 = 'family reference "%s" not exists for KEYS';
    /**
     * @errorCode
     * the reference family must be a family
     */
    const KEYS0003 = 'family reference "%s" is not a family';
    /**
     * @errorCode
     * must have create privilege to import thid kind of document
     */
    const KEYS0004 = 'insufficient privileges to import KEYS of "%s" family ';
    /**
     * @errorCode
     * the KEYS cannot be imported because family is not completed
     */
    const KEYS0005 = 'family error detected "%s" for the KEYS  : %s';
    /**
     * @errorCode
     * when define KEYS the family reference is required
     */
    const KEYS0006 = 'family reference is empty for KEYS';
    /**
     * @errorCode
     * need define attribute
     */
    const KEYS0100 = 'attribute "%s" is not a part of "%s" family for KEYS';
    /**
     * @errorCode
     * need define attribute at least one attribute
     */
    const KEYS0101 = 'no attribute found in KEYS "%s"';
    /**
     * @errorCode
     * Defined attribute must not exceed 2 references
     */
    const KEYS0102 = 'two many attributes "%s" found in KEYS "%s"';
}
