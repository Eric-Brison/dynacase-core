<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Error codes used to checking manage user/group/role
 * @class ErrorCodeACCT
 * @brief List all error code for user/group/tole mamnagement
 * @see ErrorCode
 */
class ErrorCodeFMTC
{
    /**
     * the property used by formatProperties must a property like "title".
     * @see FormatCollection::addProperty()
     */
    const FMTC0001 = 'The document property "%s" is not know. Cannot format document list';
    /**
     * array, tab and frame type attributes cannot be formated
     * @see FormatCollection::addAttribute()
     */
    const FMTC0002 = 'Structured attribute "%s" cannot be formated.';
}
