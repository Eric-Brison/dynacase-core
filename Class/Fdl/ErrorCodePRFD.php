<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Error codes used to checking profid
 * @class ErrorCodePRFD
 * @see ErrorCode
 * @brief List all error code to check family security
 * It is triggered by 'PROFID' keywords
 */
class ErrorCodePRFD
{
    /**
     * the profil id must reference a document
     */
    const PRFD0001 = 'profil identificator %s is not found';
    /**
     * the profil id must reference a family profil or family itself
     */
    const PRFD0002 = 'profil %s is not a family profil';
}
