<?php
/*
 * @author Anakeen
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
         * @errorCode value cannot be used to modify document attribute - detected in internal Doc::setValue
         */
        const VALUE0001 = 'attribute "%s", (family "%s", document "%s") : set internal value error : "%s"';
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
         * @errorCode a int attribute must containt int value
         * @see Doc::setAttributeValue
         */
        const VALUE0005 = 'attribute "%s", (family "%s", document "%s") : set value error : "%s"';

        /**
         * @errorCode a non multiple attribute cannot has an array value
         * @see Doc::setAttributeValue
         */
        const VALUE0006 = 'attribute "%s", (family "%s", document "%s") : cannot set single attribute with an array  "%s"';

        /**
         * @errorCode error during set a complete array
         * @see Doc::setAttributeValue
         */
        const VALUE0007 = 'attribute "%s", (family "%s", document "%s") : cannot update array  "%s"';

        /**
         * @errorCode only array values must be used to set a complete array
         * @see Doc::setAttributeValue
         */
        const VALUE0008 = 'attribute "%s", (family "%s", document "%s") : array value is requires to set array attribute  "%s"';

        /**
         * @errorCode error during set a complete array. Each row must be an array
         * @see Doc::setAttributeValue
         */
        const VALUE0009 = 'attribute "%s", (family "%s", document "%s") : a row is not an array. Cannot update array  "%s"';


        /**
         * @errorCode a int attribute must containt int value
         * @see Doc::setAttributeValue
         */
        const VALUE0200 = 'a int attribute value must be a int type or a string. Found "%s" (%s).';

        /**
         * @errorCode a double attribute must containt double or int value
         * @see Doc::setAttributeValue
         */
        const VALUE0201 = 'a double attribute value must be a double or int type or a string. Found "%s" (%s).';

        /**
         * @errorCode only scalar type can be used for
         * @see Doc::setAttributeValue
         */
        const VALUE0202 = 'a attribute value must not be an object. Found "%s" (%s).';


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
        public $originalError="";
        public $attributeId="";
        public $index=-1;
    }
}
