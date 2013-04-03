<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
namespace {
    /**
     * Error codes used to checking family attribute structure
     * @class ErrorCodeVALUE
     * @see ErrorCode
     * @see Doc::setAttributeValue()
     * @brief List all error code used when use setAttrbuteValue
     */
    class ErrorCodeVALUE
    {
        /**
         * @errorCode value cannot be used to modify document attribute
         */
        const VALUE0001 = 'attribute "%s", (family "%s", document "%s") : set value error : "%s"';
        /**
         * @errorCode for multiple attribute only array values can be set
         */
        const VALUE0002 = 'value "%s" must be an array to set attribute "%s", (family "%s", document "%s")';
        /**
         * @errorCode for multiple attribute which are in array only array values can be set : combine option "multiple=yes" and in array
         */
        const VALUE0003 = 'each values of a multiple in array "%s" must be an array to set attribute "%s", (family "%s", document "%s")';
        /**
         * @errorCode the attribute is not a part of document
         * @see Doc::setAttributeValue
         */
        const VALUE0004 = 'attribute "%s" is not defined for document "%s" (family "%s") : update aborted';
        /**
         * @errorCode the attribute be an array
         * @see Doc::getAttributeValue
         */
        const VALUE0100 = 'attribute "%s" is not an array in document "%s" (family "%s") : cannot use array value';

        /**
         * @errorCode the attribute is not a part of document
         * @see Doc::getAttributeValue
         */
        const VALUE0101 = 'attribute "%s" is not defined for document "%s" (family "%s")';
    }
}
namespace Dcp\AttributeValue {
    class Exception extends \Dcp\Exception
    {
       
    }
}
