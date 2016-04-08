<?php
/*
 * @author Anakeen
 * @package FDL
*/
namespace {
    /**
     * Error codes used when manage application parameters
     * @class ErrorCodePMGT
     * @brief List all error code for user/group/role searching
     * @search SearchAccount
     * @see ErrorCode
     */
    class ErrorCodePMGT
    {
        /**
         * @errorCode The parameter key not exists
         * Can be due to unkown application name also
         */
        const PMGT0001 = 'Parameter "%s" for application "%s" not found';
        /**
         * @errorCode Error during record  application parameter value
         */
        const PMGT0002 = 'Cannot set parameter "%s" for application "%s" : %s';
        /**
         * @errorCode The global parameter key not existsc
         * also when parameter is not declared as global
         */
        const PMGT0003 = 'Global parameter "%s" not found';
        /**
         * @errorCode The parameter key not exists
         * also when parameter is not declared as user
         */
        const PMGT0004 = 'User parameter "%s" for application "%s" not found';
        /**
         * @errorCode Error during record user application parameter value
         */
        const PMGT0005 = 'Cannot set user parameter "%s" for application "%s" : %s';
        /**
         * @errorCode Unknow application name
         */
        const PMGT0006 = 'Cannot set application parameter "%s" . Application "%s" not found';
        /**
         * @errorCode The parameter key not exists
         * also when parameter is not declared as user and global
         */
        const PMGT0007 = 'Global User parameter "%s" "%s" not found';
        /**
         * @errorCode The parameter key not exists
         * also when parameter is not declared as user and global
         */
        const PMGT0008 = 'Cannot set User parameter "%s" "%s" user "#%s" not found';
        /**
         * for beautifier
         */
        private function _bo()
        {
            if (true) $a = 1;
        }
    }
}

namespace Dcp\PMGT {
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
