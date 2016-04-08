<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Errors code used to checking METHOD keyword
 * @class ErrorCodeRESE
 * @brief List all error code for METHOD
 * @see ErrorCode
 */
class ErrorCodeMTHD
{
    /**
     * @errorCode method file must exists in FDL directory
     */
    const MTHD0001 = 'Method file "%s" is not found in family "%s"';
    /**
     * @errorCode syntax error in method file. Use begin-method-ignore en end-method-ignore comment tag
     */
    const MTHD0002 = 'Error in method file "%s" in family "%s" : %s';
    /**
     * @errorCode Conflict with hook method old (deprecated) name and new name
     */
    const MTHD0003 = 'Error in method file : conflicted hook "%s" (deprecated) / "%s" (new name). Cannot be together declared';
}
