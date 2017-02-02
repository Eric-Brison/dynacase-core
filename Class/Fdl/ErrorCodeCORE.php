<?php
/*
 * @author Anakeen
 * @package FDL
*/
namespace {
    /**
     * global Error Code
     * @class ErrorCodeCORE
     * @brief List all error code for user/group/tole mamnagement
     * @see ErrorCode
     */
    class ErrorCodeCORE
    {
        /**
         * @errorCode Action::exitError is called
         */
        const CORE0001 = '%s';
        /**
         * @errorCode Api Usage error
         */
        const CORE0002 = '%s';
        /**
         * @errorCode Api Usage help
         */
        const CORE0003 = '%s';
        /**
         * @errorCode application name is not declared
         */
        const CORE0004 = 'Fail to find application %s';
        /**
         * @errorCode action name name is not declared for application
         */
        const CORE0005 = 'Action "%s"  not declared for application "%s" (#%d)';
        /**
         * @errorCode action name name is not declared for application
         */
        const CORE0006 = "Access deny to action \"%s\" [%s].\n Need \"%s\" Acl for \"%s\" user";
        /**
         * @errorCode application name is not available (property available is N)
         */
        const CORE0007 = 'Unavailable application %s';
        /**
         * @errorCode action name is not available for application (property available is N)
         */
        const CORE0008 = 'Action "%s"  not available for application "%s"';
        /**
         * @errorCode action from an admin application is launched from non admin mode
         */
        const CORE0009 = 'Action "%s" [%s]  from application "%s" [%s] must be run in admin mode';
        /**
         * @errorCode Guest acess is not allowed (see CORE_ALLOW_GUEST)
         */
        const CORE0010 = 'Guest access not allowed';
        /**
         * @errorCode The locale is not supported by the operating system
         */
        const CORE0011 = "Locale '%s' is not supported by the operating system";
        /**
         * @errorCode Access forbidden to action
         */
        const CORE0012 = "Access deny : %s";
        /**
         * @errorCode Trying to access a non-existing user account
         */
        const CORE0013 = "Error : User [%s] doesn't exists";
        /**
         * @errorCode Trying to access a desactivated user account
         */
        const CORE0014 = "Error : User account [%s] is desactivated";
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
namespace Dcp\Core {
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
