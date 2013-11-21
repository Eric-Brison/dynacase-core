<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
namespace {
    /**
     * Errors code used to database query errors
     * @class ErrorCodeDB
     * @see ErrorCode
     * @brief List all error code database errors
     * @see ErrorCode
     */
    class ErrorCodeSD
    {
        /**
         * @errorCode the join must be conform to syntax
         *
         */
        const SD0001 = 'join syntax error : %s';

        /**
         * @errorCode only and, or operator allowed
         *
         */
        const SD0002 = 'general filter: Unknown operator %s : %s';


        /**
         * @errorCode all parenthesis must be closes
         *
         */
        const SD0003 = 'general filter: unbalanced parenthesis : %s';


        /**
         * @errorCode error in syntax
         *
         */
        const SD0004 = 'general filter: check syntax : %s';

    }
}

namespace Dcp\SearchDoc {
    class Exception extends \Dcp\Exception
    {
    }
}
