<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Errors code used to checking METHOD keyword
 * @class ErrorCodeDFLT
 * @brief List all error code for METHOD
 * @see ErrorCode
 */
class ErrorCodeDFLT
{
    /**
     * @errorCode default attribute must reference attribute (63 max alphanum characters)
     */
    const DFLT0001 = 'default attribute "%s" syntax error in "%s" family';
    /**
     * @errorCode default attribute must reference attribute
     */
    const DFLT0002 = 'default attribute reference is empty in "%s" family';
    /**
     * @errorCode DEFAULT method is not correctly defined
     */
    const DFLT0003 = 'the default "%s" reference method "%s" in "%s" family : %s';
    /**
     * @errorCode error definition of method in DEFAULT key
     * @see ErrorCodeATTR::ATTR1260
     * @see ErrorCodeATTR::ATTR1263
     * @see ErrorCodeATTR::ATTR1261
     */
    const DFLT0004 = 'method error attribute "%s" in family "%s" : %s';
    /**
     * @errorCode unknow attribute found in DEFAULT key
     */
    const DFLT0005 = 'the default attribute reference "%s" is not found in "%s" family';
    /**
     * @errorCode for array default values must be json valide encoded
     */
    const DFLT0006 = 'the default array attribute reference "%s" is not json encoded "%s" for "%s" family';
    /**
     * @errorCode for array default values must be json valide encoded or method call
     */
    const DFLT0007 = 'the default array attribute reference "%s" is not json encoded or method no return a valid array "%s" for "%s" family';
    /**
     * @errorCode when use default for array attribute the value must be an array of array
     */
    const DFLT0008 = 'the default array attribute reference "%s" not return a valid array ( "%s" return "%s") for "%s" family';
    /**
     * @errorCode when use default fot array attribute the value must be an array of array. Somes returns row are invalid
     */
    const DFLT0009 = 'the default array attribute reference "%s" not return a valid array ( "%s" return "%s") for "%s" family : "%s"';
}
