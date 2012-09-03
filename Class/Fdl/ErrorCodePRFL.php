<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Errors code used to checking document's profil
 * @class ErrorCodePRFL
 * @brief List all error code for profil
 * @see ErrorCode
 */
class ErrorCodePRFL
{
    /**
     * @errorCode
     * the profil id must is required
     */
    const PRFL0001 = 'profil identifier is not set';
    /**
     * @errorCode
     * the profil id must reference a document
     */
    const PRFL0002 = 'profil identifier "%s" is not found';
    /**
     * @errorCode
     * the document where attach profil must reference a document
     */
    const PRFL0003 = 'document identifier "%s" is not found, profil not set';
    /**
     * @errorCode
     * the profil id must reference a profil document
     */
    const PRFL0004 = 'profil "%s" is not compatible with "%s" document';
    /**
     * @errorCode
     * profil modifier is RESET, ADD or DELETE
     */
    const PRFL0005 = 'unavailable modifier "%s" must be one of %s';
    /**
     * @errorCode
     * syntax error for acl description
     */
    const PRFL0100 = 'acl syntax error "%s" for "%s" profil, must be "<acl>=<user|group>"';
    /**
     * @errorCode
     * acl is not available for this profil
     */
    const PRFL0101 = 'unavailable acl "%s" for "%s" profil, must be one of %s';
    /**
     * @errorCode
     * acl user is not set
     */
    const PRFL0102 = 'user id not set in acl for "%s" profil';
    /**
     * @errorCode
     * user must be exists for static profil
     */
    const PRFL0103 = 'user "%s" not found in acl for "%s" static profil';
    /**
     * @errorCode
     * acl must reference an user or an attribute in dynamic profil
     */
    const PRFL0200 = 'user or attribute "%s" not found in acl for "%s" dynamic profil, available are %s';
    /**
     * @errorCode
     * attribute in dynamic profil must reference relation attribute (docid)
     */
    const PRFL0201 = 'attribute "%s" is not a relation in acl for "%s" dynamic profil, available are %s';
    /**
     * @errorCode
     * family profy cannot be dynamic
     */
    const PRFL0202 = 'family profil"%s"  must not have dpdoc_famid';
    /**
     * @errorCode
     *  dynamic profil must reference a valid family document
     */
    const PRFL0203 = 'unknow dynamic reference "%s" for "%s" dynamic profil';
}
