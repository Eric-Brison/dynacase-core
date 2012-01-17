<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Errors code used to checking METHOD keyword
 * @class ErrorCodeRESE
 * @brief List all error code for METHOD
 */
class ErrorCodeDFLT
{
    /**
     * default attribute must reference attribute (63 max alphanum characters)
     */
    const DFLT0001 = 'default attribute "%s" syntax error in "%s" family';
    /**
     * default attribute must reference attribute
     */
    const DFLT0002 = 'default attribute reference is empty in "%s" family';
    /**
     * DEFAULT method is not correctly defined
     */
    const DFLT0003 = 'the default "%s" reference method "%s" in "%s" family : %s';
    /**
     * error definition of method in DEFAULT key
     * @see ErrorCodeATTR::ATTR1260
     * @see ErrorCodeATTR::ATTR1263
     * @see ErrorCodeATTR::ATTR1261
     */
    const DFLT0004 = 'method error in family "%s" : %s';
    /**
     * unknow attribute found in DEFAULT key
     */
    const DFLT0005 = 'the default attribute reference "%s" is not found in "%s" family';
}
