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
class ErrorCodeMTHD
{
    /**
     * method file must exists in FDL directory
     */
    const MTHD0001 = 'method file "%s" is not found in family "%s"';
    /**
     * syntax error in method file. Use begin-method-ignore en end-method-ignore comment tag
     */
    const MTHD0002 = 'error in method file "%s" in family "%s" : %s';
}
