<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Errors code used to checking WIDID keyword
 * @class ErrorCodeRESE
 * @brief List all error code for WIDID
 */
class ErrorCodeWID
{
    /**
     * folder reference must be reference existing workflow
     */
    const WID0001 = 'WID "%s" workflow is not found in attribute "%s"';
    /**
     * folder reference must be a workflow document
     */
    const WID0002 = 'WID "%s" is not a workflow in attribute "%s"';
}
