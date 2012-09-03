<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Errors code used to checking DFLDID keyword
 * @class ErrorCodeRESE
 * @brief List all error code for DFLDID
 * @see ErrorCode
 */
class ErrorCodeDFLD
{
    /**
     * @errorCode
     * folder reference must be reference existing folder
     */
    const DFLD0001 = 'DFLDID "%s" folder is not found in attribute "%s"';
    /**
     * @errorCode
     * folder reference must be a folder or "auto"
     */
    const DFLD0002 = 'DFLDID "%s" is not a folder in attribute "%s"';
}
