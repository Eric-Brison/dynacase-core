<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Errors code used to checking CVIDID keyword
 * @class ErrorCodeRESE
 * @brief List all error code for CVIDID
 */
class ErrorCodeCVID
{
    /**
     * @errorCode
     * folder reference must be reference existing view control
     */
    const CVID0001 = 'CVID "%s" view control is not found in attribute "%s"';
    /**
     * @errorCode
     * folder reference must be a view control document
     */
    const CVID0002 = 'CVID "%s" is not a view control in attribute "%s"';
}
