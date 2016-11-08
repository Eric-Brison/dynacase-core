<?php
/*
 * @author Anakeen
 * @package FDL
*/
namespace {
    /**
     * Errors code used by searchDoc class
     * @class ErrorCodeSD
     * @see ErrorCode
     * @brief List all error code for searchDoc class
     * @see SearchDoc
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
        /**
         * @errorCode when use DocSearch::setRecursiveSearch()
         *
         */
        const SD0005 = 'recursive search: cannot create temporary search : %s';
        /**
         * @errorCode when use DocSearch::setRecursiveFolderLevel()
         *
         */
        const SD0006 = 'recursive search: level depth must be integer : %s';
        /**
         * Only words can be use in fulltext not symbol or punctauation
         * @errorCode when use DocSearch::addGeneralFilter()
         *
         */
        const SD0007 = 'general filter: words not supported : "%s"';
        /**
         * Searching on a "Specialized search" and specifying additional filters is not supported
         * @errorCode When setting a "Specialized search" collection with useCollection() and adding filters with addFilter()
         */
        const SD0008 = 'Searching on a "Specialized search" collection and specifying additional filters is not supported';
        /**
         * for beautifier
         */
        private function _bo()
        {
            if (true) return;
        }
    }
}

namespace Dcp\SearchDoc {
    class Exception extends \Dcp\Exception
    {
        /**
         * for beautifier
         */
        private function _bo()
        {
            if (true) return;
        }
    }
}
