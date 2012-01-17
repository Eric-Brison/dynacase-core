<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Errors code used to checking CFLDID keyword
 * @class ErrorCodeRESE
 * @brief List all error code for CFLDID
 */
class ErrorCodeCFLD
{
    /**
     * folder reference must be reference existing search
     */
    const CFLD0001 = 'CFLDID "%s" search is not found in attribute "%s"';
    /**
     * folder reference must be a search document
     */
    const CFLD0002 = 'CFLDID "%s" is not a search in attribute "%s"';
}
