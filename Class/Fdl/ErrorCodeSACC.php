<?php
/*
 * @author Anakeen
 * @package FDL
*/
namespace {
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
         * @errorCode
         * the argument must be SearchAccount::returnAccount or SearchAccount::returnDocument
         * @see SearchAccount::setReturnType()
         */
        const SACC0001 = 'Incorrect argument "%s" to return type account search';
        /**
         * @errorCode
         * the argument must be valid role reference
         * @see SearchAccount::addRoleFilter()
         */
        const SACC0002 = 'Unknow role "%s" to be use with account search filter';
        /**
         * @errorCode
         * The slice must be a positive number or "all"
         * @see SearchAccount::setSlice()
         */
        const SACC0003 = 'Incorrect slice argument %s for account search';
        /**
         * @errorCode
         * The start must be a positive number or zero
         * @see SearchAccount::setSlice()
         */
        const SACC0004 = 'Incorrect start argument %s for account search';
        /**
         * @errorCode
         * the argument must be a valid group login
         * @see SearchAccount::addGroupFilter()
         */
        const SACC0005 = 'Unknow group "%s" to be use with account search filter';
        /**
         * @errorCode
         * the argument must be a valid family Id
         * @see SearchAccount::filterFamily()
         */
        const SACC0006 = 'Unknow family "%s" to be use with account search filter';
        /**
         * @errorCode
         * for beautifier
         */
        private function _bo()
        {
            if (true) $a = 1;
        }
    }
}

namespace Dcp\Sacc {
    /**
     * @errorCode
     * Account search exception
     */
    class Exception extends \Dcp\Exception
    {
        /**
         * for beautifier
         */
        private function _bo()
        {
            if (true) $a = 1;
        }
    }
}
