<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Errors code used to checking application access
 * @class ErrorCodeATTR
 * @brief List all error code for access
 */
class ErrorCodeATTR
{
    /**
     * Attribute identicator is limit to 63 alphanum characters
     */
    const ATTR0100 = 'syntax error for attribute "%s"';
    /**
     * Attribute identificator can set as a reserved word postgresql reserved words
     */
    const ATTR0101 = 'attribute identificator "%s" use a reserved word';
    /**
     * Attribute identificator is required
     */
    const ATTR0102 = 'attribute identificator is not set';
    /**
     * Attribute identificator can set as a reserved word like doc properties
     */
    const ATTR0103 = 'attribute identificator "%s" use a property identificator';
    /**
     * Attribute identicator is limit to 63 alphanum characters
     */
    const ATTR0200 = 'syntax error for structure "%s" attribute "%s"';
    /**
     * Attribute structure identificator is required
     */
    const ATTR0201 = 'attribute structure is not set for attribute "%s"';
    /**
     * Attribute structure must reference other attribute
     */
    const ATTR0202 = 'attribute structure is same as attribute "%s"';
    /**
     * Attribute type is required
     */
    const ATTR0600 = 'type is not defined for attribute "%s"';
    /**
     * Attribute type is not available
     */
    const ATTR0601 = 'unrecognized attribute type "%s" (attribute "%s"), type is one of %s';
    /**
     * Attribute type is required
     */
    const ATTR0602 = 'syntax error for type "%s" in attribute "%s"';
}
