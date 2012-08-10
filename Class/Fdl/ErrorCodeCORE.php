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
         * Action::exitError is called
         */
        const CORE0001 = '%s';
        /**
         * Api Usage error
         */
        const CORE0002 = '%s';
        /**
         * Api Usage help
         */
        const CORE0003 = '%s';
        /**
         * application name is not declared
         */
        const CORE0004 = 'Fail to find application %s';
        /**
         * action name name is not declared for application
         */
        const CORE0005 = 'Action "%s"  not declared for application "%s" (#%d)';
        /**
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
