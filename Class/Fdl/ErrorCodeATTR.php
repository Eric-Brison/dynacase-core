<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Errors code used to checking application access
 * @class ErrorCodeATTR
 * @brief List all error code for access
 */
class ErrorCodeATTR
{
    /**
     * Attribute identicator is limit to 63 alphanum characters
     */
    const ATTR0100 = 'syntax error for attribute "%s"';
    /**
     * Attribute identificator can set as a reserved word postgresql reserved words
     */
    const ATTR0101 = 'attribute identificator "%s" use a reserved word';
    /**
     * Attribute identificator is required
     */
    const ATTR0102 = 'attribute identificator is not set';
    /**
     * Attribute identificator can set as a reserved word like doc properties
     */
    const ATTR0103 = 'attribute identificator "%s" use a property identificator';
    /**
     * Attribute identicator is limit to 63 alphanum characters
     */
    const ATTR0200 = 'syntax error for structure "%s" attribute "%s"';
    /**
     * Attribute structure identificator is required
     */
    const ATTR0201 = 'attribute structure is not set for attribute "%s"';
    /**
     * Attribute structure must reference other attribute
     */
    const ATTR0202 = 'attribute structure is same as attribute "%s"';
    /**
     * Attribute isTitle is Y or N
     */
    const ATTR0400 = 'invalid value "%s" for isTitle in attribute "%s"';
    /**
     * Attribute isTitle must not be Y for structured attributes
     */
    const ATTR0401 = 'isTitle cannot be set for structured attribute "%s"';
    /**
     * Attribute isAbstract is Y or N
     */
    const ATTR0500 = 'invalid value "%s" for isAbstract in attribute "%s"';
    /**
     * Attribute isAbstract must not be Y for structured attributes
     */
    const ATTR0501 = 'isAbstract cannot be set for structured attribute "%s"';
    /**
     * Attribute type is required
     */
    const ATTR0600 = 'type is not defined for attribute "%s"';
    /**
     * Attribute type is not available
     */
    const ATTR0601 = 'unrecognized attribute type "%s" (attribute "%s"), type is one of %s';
    /**
     * Attribute type is required
     */
    const ATTR0602 = 'syntax error for type "%s" in attribute "%s"';
    /**
     * The attribute's order must be a number
     */
    const ATTR0700 = 'the order "%s" must be a number in attribute "%s"';
    /**
     * The attribute's order is required on no-set attribute
     */
    const ATTR0702 = 'the order is required in attribute "%s"';
    /**
     * The attribute's visibility must be defined
     */
    const ATTR0800 = 'the visibility is required in attribute "%s"';
    /**
     * The attribute's visibility is limited to defined visibilities (H,R,...)
     */
    const ATTR0801 = 'the visibility "%s" in attribute "%s" must be one of %s';
    /**
     * The U visibility can be applied only on array attribute
     */
    const ATTR0802 = 'the U visibility is reserved to array, in attribute "%s"';
    /**
     * The input help file must exists before declared it
     */
    const ATTR1100 = 'the input help file "%s" not exists, in attribute "%s"';
    /**
     * The input help file must be a correct PHP file
     */
    const ATTR1101 = 'the input help file "%s" is not parsable, in attribute "%s" : %s';
    /**
     * The option name are composed only of alpha characters
     */
    const ATTR1500 = 'the option name "%s" is not valid in attribute "%s"';
    /**
     * The syntax option is : optname=optvalue
     * @note example : elabel=enter a value
     */
    const ATTR1501 = 'the option "%s" must have = sign, in attribute "%s"';
    /**
     * the phpfile must be a call to a valid function or method
     */
    const ATTR1200 = 'syntax error in phpfile attribute  "%s" : %s';
    /**
     * function must have 2 parenthesis one open and one close
     */
    const ATTR1201 = 'error parenthesis in method/file definition : "%s"';
    /**
     * function name must be a valid PHP name
     */
    const ATTR1202 = 'syntax error in function name : "%s"';
    /**
     * function name must exists
     */
    const ATTR1203 = 'function "%s" not exists';
    /**
     * double quote error in function call
     */
    const ATTR1204 = 'double quote syntax error (character %d) in function "%s"';
    /**
     * simple quote error in function call
     */
    const ATTR1205 = 'simple quote syntax error (character %d) in function "%s"';
    /**
     * output attributes must be declared after : characters
     * @note
     * example : test():MY_TEST1, MY_TEST2
     */
    const ATTR1206 = 'no output attribute missing ":" character in function "%s"';
    /**
     * output attributes must represent attribute name with a comma separator
     * @note
     *  example :test():MY_TEST1, MY_TEST2
     *  test(My_TEST2):MY_TEST1
     */
    const ATTR1207 = 'outputs in function "%s" can be only alphanum characters ';
    /**
     * output attributes must represent attribute name with a comma separator
     * @note
     *  example : MY_APP:my_test():MY_TEST1, MY_TEST2
     *
     */
    const ATTR1208 = 'appname in special help can be only alphanum characters ';
    /**
     * input help can use only user function
     */
    const ATTR1209 = 'function "%s" is an internal php function';
    /**
     * input help must be defined in declared file
     */
    const ATTR1210 = 'function "%s" is not defined in "%s" file';
    /**
     * the called function need more arguments
     */
    const ATTR1211 = 'not enought argument call to use function "%s" (need %d arguments)';
    /**
     * declaration of call method is not correct
     * @note example : ::test()  or myClass::test()
     */
    const ATTR1250 = 'syntax error in method call for attribute "%s" : %s';
    /**
     * call of a method mudt contains '::' characters
     * @note example : ::test()  or myClass::test()
     */
    const ATTR1251 = 'no "::" delimiter in method call "%s"';
    /**
     * method name must be a valid PHP name
     */
    const ATTR1252 = 'syntax error in method name : "%s"';
    /**
     * method name must be a valid PHP class name
     */
    const ATTR1253 = 'syntax error in class name in method call: "%s"';
    /**
     * call method can be return only one value
     * @note example : ::test():MY_RET
     */
    const ATTR1254 = 'only one output is possible in method "%s"';
}
