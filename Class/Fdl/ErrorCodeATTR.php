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
     * Attribute isTitle is Y or N
     */
    const ATTR0400 = 'invalid value "%s" for isTitle in attribute "%s"';
    /**
     * Attribute isTitle must not be Y for structured attributes
     */
    const ATTR0401 = 'isTitle cannot be set for structured attribute "%s"';
    /**
     * Attribute isAbstract is Y or N
     */
    const ATTR0500 = 'invalid value "%s" for isAbstract in attribute "%s"';
    /**
     * Attribute isAbstract must not be Y for structured attributes
     */
    const ATTR0501 = 'isAbstract cannot be set for structured attribute "%s"';
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
    /**
     * The attribute's order must be a number
     */
    const ATTR0700 = 'the order "%s" must be a number in attribute "%s"';
    /**
     * The attribute's order is required on no-set attribute
     */
    const ATTR0702 = 'the order is required in attribute "%s"';
    /**
     * The attribute's visibility must be defined
     */
    const ATTR0800 = 'the visibility is required in attribute "%s"';
    /**
     * The attribute's visibility is limited to defined visibilities (H,R,...)
     */
    const ATTR0801 = 'the visibility "%s" in attribute "%s" must be one of %s';
    /**
     * The U visibility can be applied only on array attribute
     */
    const ATTR0802 = 'the U visibility is reserved to array, in attribute "%s"';
}
