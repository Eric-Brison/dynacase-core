<?php
/*
 * @author Anakeen
 * @package FDL
*/
namespace {
    /**
     * ErrorCodeApplicationParameterManager
     *
     * @see ApplicationParameterManager
     */
    class ErrorCodeAPM
    {
        /**
         * @errorCode The application name not exists
         */
        const APM0001 = 'The application id "%s" is not found';
        /**
         * @errorCode The global parameter key not exists
         */
        const APM0002 = 'Global parameter "%s" is not found';
        /**
         * @errorCode The application name not exists
         */
        const APM0003 = 'The application name "%s" is not found';
        /**
         * @errorCode The type of the application element is not usable
         */
        const APM0004 = 'The application type is not usable';
        /**
         * @errorCode The type of the user element is not usable
         */
        const APM0005 = 'The user type is not usable';
        /**
         * @errorCode Error during record user application parameter value
         */
        const APM0006 = 'Cannot set user parameter "%s" for application "%s"';
        /**
         * @errorCode The parameter key not exists
         * also when parameter is not declared as user and global
         */
        const APM0007 = 'Cannot set User parameter "%s" "%s" user "#%s" not found';
        /**
         * @errorCode Unable to get parameter
         */
        const APM0008 = 'The parameter %s of the application %s is not found';
        /**
         * @errorCode Error during record  application parameter value
         */
        const APM0009 = 'Cannot set parameter "%s" for application "%s" : %s';
        /**
         * @errorCode Unable to get application id, when parameter is global need a parameter name
         */
        const APM0010 = 'Unable to get application id, when parameter is global need a parameter name';
        /**
         * @errorCode Error during record  global application parameter value
         */
        const APM0011 = 'Cannot set parameter "%s" no value detected';
        /**
         * for beautifier
         */
        private function _bo()
        {
            if (true) $a = 1;
        }
    }
}

namespace Dcp\ApplicationParameterManager {
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
