<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
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
         * @errorCode
         * Action::exitError is called
         */
        const CORE0001 = '%s';
        /**
         * @errorCode
         * Api Usage error
         */
        const CORE0002 = '%s';
        /**
         * @errorCode
         * Api Usage help
         */
        const CORE0003 = '%s';
        /**
         * @errorCode
         * application name is not declared
         */
        const CORE0004 = 'Fail to find application %s';
        /**
         * @errorCode
         * action name name is not declared for application
         */
        const CORE0005 = 'Action "%s"  not declared for application "%s" (#%d)';
        /**
         * @errorCode
         * action name name is not declared for application
         */
        const CORE0006 = "Access deny to action \"%s\" [%s].\n Need \"%s\" Acl for \"%s\" user";
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
