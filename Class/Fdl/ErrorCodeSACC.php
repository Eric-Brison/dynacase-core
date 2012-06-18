<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Error codes used to checking manage user/group/role
 * @class ErrorCodeSACC
 * @brief List all error code for user/group/role searching
 * @search SearchAccount
 * @see ErrorCode
 */
class ErrorCodeSACC
{
    /**
     * the argument must be SearchAccount::returnAccount or SearchAccount::returnDocument
     * @see SearchAccount::setObjectReturn()
     */
    const SACC0001 = 'Incorrect argument "%s" to return type account search';
    /**
     * the argument must be valid role reference
     * @see SearchAccount::addRoleFilter()
     */
    const SACC0002 = 'Unknow role "%s" to be use with account search filter';
    /**
     * The slice must be a positive number or "all"
     * @see SearchAccount::setSlice()
     */
    const SACC0003 = 'Incorrect slice argument %s for account search';
    /**
     * The start must be a positive number or zero
     * @see SearchAccount::setSlice()
     */
    const SACC0004 = 'Incorrect start argument %s for account search';
    /**
     * the argument must be a valid group login
     * @see SearchAccount::addGroupFilter()
     */
    const SACC0005 = 'Unknow group "%s" to be use with account search filter';
}
