<?php
/*
 * @author Anakeen
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
    /**
     * @errorCode account as no type
     */
    const ACCT0100 = 'Account "%s" ( #%d ) as no type. Must be a user, group or role';
    /**
     * @errorCode Search Account is not initialized
     * @see \Dcp\Core\ExportAccount::setSearchAccount
     */
    const ACCT0101 = 'Search Account must be initialized before export it';
    /**
     * @errorCode Imported substitute must reference a login account
     * @see \Dcp\Core\ImportAccounts
     */
    const ACCT0200 = 'Substitute "%s" not found for users "%s"';
    /**
     * @errorCode The accounts xml file must be validate by accounts.xsd schema
     * @see \Dcp\Core\ImportAccounts
     */
    const ACCT0201 = 'Cannot import account XML file : no match schema : %s';
    /**
     * @errorCode Document associated to account must reference a valid family
     * @see \Dcp\Core\ImportAccounts
     */
    const ACCT0202 = 'Cannot import account XML file : family "%s" not found';
    /**
     * @errorCode The accounts xml document tags must be validate by family schema
     * @see \Dcp\Core\ImportAccounts
     */
    const ACCT0203 = 'Cannot import account XML file : document tag no match schema : %s';
    /**
     * @errorCode An abort order for import account has be received
     * @see \Dcp\Core\ImportAccounts
     */
    const ACCT0204 = 'User Abort';
    /**
     * @errorCode An abort order for export account has be received
     * @see \Dcp\Core\ExportAccounts
     */
    const ACCT0205 = 'User Abort';
    /**
     * @errorCode Import account stop on first error detected
     * @see \Dcp\Core\ImportAccounts
     */
    const ACCT0206 = 'Stop On Error';
    /**
     * @errorCode Import account
     * @see \Dcp\Core\EXportAccounts::setExportSchemaDirectory
     */
    const ACCT0207 = 'Export directory "%s" is not a directory';
    /**
     * @errorCode Import account
     * @see \Dcp\Core\EXportAccounts::setExportSchemaDirectory
     */
    const ACCT0208 = 'Export directory "%s" is not a writable';
    /**
     * @errorCode The document account has already a logical name
     * @see \Dcp\Core\ImportAccounts
     */
    const ACCT0209 = 'Mismatch logical name "%s" <> "%s"';
}
