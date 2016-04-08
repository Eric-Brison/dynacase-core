<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Errors code used to checking METHOD keyword
 * @class ErrorCodeINIT
 * @brief List all error code for METHOD
 * @see ErrorCode
 */
class ErrorCodeINIT
{
    /**
     * @errorCode initial parameter value must reference attribute (63 max alphanum characters)
     */
    const INIT0001 = 'initial parameter value "%s" syntax error in "%s" family';
    /**
     * @errorCode initial parameter value must reference attribute
     */
    const INIT0002 = 'initial parameter value reference is empty in "%s" family';
    /**
     * @errorCode INITIAL method is not correctly defined
     */
    const INIT0003 = 'the initial parameter value  "%s" reference method "%s" in "%s" family : %s';
    /**
     * @errorCode error definition of method in INITIAL key or in already set parameter
     * @see ErrorCodeATTR::ATTR1260
     * @see ErrorCodeATTR::ATTR1263
     * @see ErrorCodeATTR::ATTR1261
     */
    const INIT0004 = 'method error parameter "%s" in family "%s" : %s';
    /**
     * @errorCode unknow attribute found in INITIAL key or in already set parameter
     */
    const INIT0005 = 'the parameter value reference "%s" is not found in "%s" family';
    /**
     * @errorCode unknow attribute found in INITIAL key or in already set parameter
     */
    const INIT0006 = 'the parameter value reference "%s" is not a parameter but an attribut in "%s" family';
}
