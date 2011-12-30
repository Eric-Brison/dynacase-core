<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Errors code used to checking profid
 * @class ErrorCodePRFD
 * @brief List all error code for profid
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
