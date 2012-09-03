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
class ErrorCodeACCT
{
    /**
     * @errorCode
     * the reference role to add to a user is not correct. can be system identifier
     * or login reference (role_login)
     */
    const ACCT0001 = 'Cannot add role "%s" for %s user "%s". This role is unknow';
    /**
     * @errorCode
     * the user object must be completed (must have id)
     */
    const ACCT0002 = 'Cannot add role "%s" in unassigned user object';
    /**
     * @errorCode
     * the role can be add only on a user (not to a role itself neither a group)
     */
    const ACCT0003 = 'Cannot add role "%s" into a role or a group ("%s"). ';
    /**
     * @errorCode
     * the user object must be completed (must have id)
     */
    const ACCT0004 = 'Cannot delete all role to a unassigned user object';
    /**
     * @errorCode
     * the user object must not be a role (only group or user)
     */
    const ACCT0005 = 'Cannot delete all role to a role ("%s") object';
    /**
     * @errorCode
     * the user object must be completed (must have id)
     */
    const ACCT0006 = 'Cannot set role "%s" in unassigned user object';
    /**
     * @errorCode
     * the role can be add only on a group or a user (not to a role itself)
     */
    const ACCT0007 = 'Cannot set role "%s" into a role ("%s"). ';
    /**
     * @errorCode
     * the reference role to add to a user is not correct. can be system identifier
     * or login reference (role_login)
     * This is a part of a role set
     */
    const ACCT0008 = 'Cannot add role "%s" for %s user "%s". This role is unknow';
}
