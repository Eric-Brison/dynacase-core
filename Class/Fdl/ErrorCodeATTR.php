<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Error codes used to checking family attribute structure
 * @class ErrorCodeATTR
 * @see ErrorCode
 * @brief List all error code for document's attribute errors
 * It is triggered by 'ATTR', 'PARAM', 'MODATTR' keywords
 */
class ErrorCodeATTR
{
    /**
     * @errorCode  Attribute identicator is limit to 63 alphanum characters
     */
    const ATTR0100 = 'syntax error for attribute "%s"';
    /**
     * @errorCode  Attribute identifier cannot be equal to a reserved postgresql word
     */
    const ATTR0101 = 'attribute identifier "%s" use a reserved word';
    /**
     * @errorCode  Attribute identifier is required
     */
    const ATTR0102 = 'attribute identifier is not set';
    /**
     * @errorCode  Attribute identifier cannot be equal to a doc properties name
     */
    const ATTR0103 = 'attribute identifier "%s" use a property identificator';
    /**
     * @errorCode  Attribute identicator is limit to 63 alphanum characters
     */
    const ATTR0200 = 'syntax error for structure "%s" attribute "%s"';
    /**
     * @errorCode  Attribute structure identifier is required
     */
    const ATTR0201 = 'attribute structure is not set for attribute "%s"';
    /**
     * @errorCode  Attribute structure must reference other attribute
     */
    const ATTR0202 = 'attribute structure is same as attribute "%s"';
    /**
     * @errorCode  Attribute structure must reference an attribute
     */
    const ATTR0203 = 'attribute structure reference is not an attribute for "%s", family "%s"';
    /**
     * @errorCode  Attribute structure must compatible with level hierarchy
     * @note a tab has no parent,
     * a frame must have only tab parent,
     * another attribute must have only frame parent or array parent
     */
    const ATTR0204 = 'attribute structure "%s" is not possible for "%s"';
    /**
     * @errorCode  Attribute structure must reference a frame or array attribute for normal attribute
     */
    const ATTR0205 = 'this attribute structure "%s" is not a allowed for normal attribute"%s"';
    /**
     * @errorCode  Attribute structure must be empty for tab attribute
     */
    const ATTR0206 = 'attribute structure "%s" is forbidden for a tab  attribute for "%s"';
    /**
     * @errorCode  Attribute structure must reference a tab attribute or nothing
     */
    const ATTR0207 = 'this attribute structure "%s" is not a allowed for frame attribute "%s"';
    /**
     * @errorCode  Parameter structure must reference a parameter
     */
    const ATTR0208 = 'param structure reference is not a parameter for "%s", family "%s"';
    /**
     * @errorCode  Parameter structure must compatible with level hierarchy
     * @note a tab has no parent,
     * a frame must have only tab parent,
     * another parameter must have only frame parent or array parent
     */
    const ATTR0209 = 'parameter structure "%s" is not possible for "%s"';
    /**
     * @errorCode  Parameter structure must reference a frame or array parameter for parameter
     */
    const ATTR0210 = 'this parameter structure "%s" is not a allowed for parameter"%s"';
    /**
     * @errorCode
     */
    const ATTR0211 = 'parameter "%s" must not have a phpfunc with output variables';
    /**
     * @errorCode  Attribute order must reference an attribute family
     */
    const ATTR0212 = 'the attribute "%s" has incorrect order  : "%s" (must be numeric or reference an attribute)';
    /**
     * @errorCode  Attribute order reference is not in the current frame
     */
    const ATTR0213 = 'the attribute "%s" has incorrect order  : parent is "%s" must be "%s": out of field';

    /**
     * @errorCode A parent id reference a child id
     */
    const ATTR0214 = 'the attribute "%s" has a loop reference for parent ';
    /**
     * @errorCode  Attribute isTitle is Y or N
     */
    const ATTR0400 = 'invalid value "%s" for isTitle in attribute "%s"';
    /**
     * @errorCode  Attribute isTitle must not be Y for structured attributes
     */
    const ATTR0401 = 'isTitle cannot be set for structured attribute "%s"';
    /**
     * @errorCode  Attribute isAbstract is Y or N
     */
    const ATTR0500 = 'invalid value "%s" for isAbstract in attribute "%s"';
    /**
     * @errorCode  Attribute isAbstract must not be Y for structured attributes
     */
    const ATTR0501 = 'isAbstract cannot be set for structured attribute "%s"';
    /**
     * @errorCode  Attribute type is required
     */
    const ATTR0600 = 'type is not defined for attribute "%s"';
    /**
     * @errorCode  Attribute type is not available
     */
    const ATTR0601 = 'unrecognized attribute type "%s" (attribute "%s"), type is one of %s';
    /**
     * @errorCode  a type is can be only a alpha characters
     * example text, double, money("%.02f $")
     */
    const ATTR0602 = 'syntax error for type "%s" in attribute "%s"';
    /**
     * @errorCode  the format string must contains only one %s variable
     */
    const ATTR0603 = 'bad output format "%s" in attribute "%s" ';
    /**
     * @errorCode the basic type set in a MODATTR cannot be changed
     */
    const ATTR0604 = 'incompatible redefinition of type for "%s" attribute (family "%s"). New type "%s" is not compatible with origin "%s"';
    /**
     * @errorCode a MODATTR attribute is not defined in its ancestor
     * Cannot modify an attribute which has not defined before
     */
    const ATTR0605 = 'attribute modification for "%s" attribute (family "%s") is not found in ancestor';
    /**
     * @errorCode a enum MODATTR attribute cannot redefine its items
     * If need redefine, a ATTR must be used (not a MODATTR) to redefine the attribut and cut inheritance
     */
    const ATTR0606 = 'enum attribute modification for "%s" attribute (family "%s"): the enum items cannot be redefined';
    /**
     * @errorCode The attribute's order must be a number or an attribute id reference
     */
    const ATTR0700 = 'the order "%s" must be a number or an attribute reference in attribute "%s"';
    /**
     * @errorCode  The attribute's order is required when  attribute is not a frame or a tab
     */
    const ATTR0702 = 'the order is required in attribute "%s"';
    /**
     * @errorCode  The attribute's visibility must be defined
     */
    const ATTR0800 = 'the visibility is required in attribute "%s"';
    /**
     * @errorCode  The attribute's visibility is limited to defined visibilities (H,R,...)
     */
    const ATTR0801 = 'the visibility "%s" in attribute "%s" must be one of %s';
    /**
     * @errorCode  The U visibility can be applied only on array attribute
     */
    const ATTR0802 = 'the U visibility is reserved to array, in attribute "%s"';
    /**
     * @errorCode  property isNeeded is Y or N
     */
    const ATTR0900 = 'invalid value "%s" for isNeeded in attribute "%s"';
    /**
     * @errorCode  property isNeeded must not be Y for structured attributes
     */
    const ATTR0901 = 'isNeeded cannot be set for structured attribute "%s"';
    /**
     * @errorCode  property isNeeded cannot be used when attribute is included in an array
     */
    const ATTR0902 = 'isNeeded cannot be set for  attribute included in array "%s"';
    /**
     * @errorCode  property isNeeded cannot be used when parameter is included in an array
     */
    const ATTR0903 = 'isNeeded cannot be set for parameter included in array "%s"';
    /**
     * @errorCode  syntaxt error in method describe in link
     */
    const ATTR1000 = 'method syntax error in link "%s" for attribute "%s":%s';
    /**
     * @errorCode  call method in link property must be defined in file method or in another class if precised
     * @see ATTR1260
     * @see ATTR1261
     * @see ATTR1262
     * @see ATTR1263
     */
    const ATTR1001 = 'link method error in "%s" family : %s';
    /**
     * @errorCode  Method use in link must have @apiExpose tag comment in their description
     */
    const ATTR1002 = 'method "%s" in attribute link "%s" is not an exposable method';
    /**
     * @errorCode  The input help file must exists before declared it
     */
    const ATTR1100 = 'the input help file "%s" not exists, in attribute "%s"';
    /**
     * @errorCode  The input help file must be a correct PHP file
     */
    const ATTR1101 = 'the input help file "%s" is not parsable, in attribute "%s" : %s';
    /**
     * @errorCode  The option name are composed only of alpha characters
     */
    const ATTR1500 = 'the option name "%s" is not valid in attribute "%s"';
    /**
     * @errorCode  The syntax option is : optname=optvalue
     * @note example : elabel=enter a value
     */
    const ATTR1501 = 'the option "%s" must have = sign, in attribute "%s"';
    /**
     * @errorCode  the phpfunc must be a call to a valid function or method
     */
    const ATTR1200 = 'syntax error in phpfunc attribute  "%s" : %s';
    /**
     * @errorCode  function must have 2 parenthesis one open and one close
     */
    const ATTR1201 = 'error parenthesis in method/file definition : "%s"';
    /**
     * @errorCode  function name must be a valid PHP name
     */
    const ATTR1202 = 'syntax error in function name : "%s"';
    /**
     * @errorCode  function name must exists
     */
    const ATTR1203 = 'function "%s" not exists';
    /**
     * @errorCode  double quote error in function call
     */
    const ATTR1204 = 'double quote syntax error (character %d) in function "%s"';
    /**
     * @errorCode  simple quote error in function call
     */
    const ATTR1205 = 'simple quote syntax error (character %d) in function "%s"';
    /**
     * @errorCode  output attributes must be declared after semicolumn characters
     * @note
     * example : test():MY_TEST1, MY_TEST2
     */
    const ATTR1206 = 'no output attribute, missing ":" character in function "%s"';
    /**
     * @errorCode  output attributes must represent attribute name with a comma separator
     * @note
     *  example :test():MY_TEST1, MY_TEST2
     *  test(My_TEST2):MY_TEST1
     */
    const ATTR1207 = 'outputs in function "%s" can be only alphanum characters ';
    /**
     * @errorCode  appname must be only alphanum characters
     * @note when use special help as help input
     */
    const ATTR1208 = 'appname in special help can be only alphanum characters';
    /**
     * @errorCode  input help can use only user function
     */
    const ATTR1209 = 'function "%s" is an internal php function';
    /**
     * @errorCode  input help must be defined in declared file
     */
    const ATTR1210 = 'function "%s" is not defined in "%s" file';
    /**
     * @errorCode  the called function need more arguments
     */
    const ATTR1211 = 'not enough argument call to use function "%s" (need %d arguments)';
    /**
     * @errorCode  the method use for computed must declarer an existed attribute
     * @note triggered in Doc::specRefreshGen()
     */
    const ATTR1212 = 'unknow output attribut for method "%s" in family "%s"';
    /**
     * @errorCode  declaration of call method is not correct
     * @note example : ::test()  or myClass::test()
     */
    const ATTR1250 = 'syntax error in method call (phpfunc) for attribute "%s" : %s';
    /**
     * @errorCode  call of a method mudt contains '::' characters
     * @note example : ::test()  or myClass::test()
     */
    const ATTR1251 = 'no "::" delimiter in method call "%s"';
    /**
     * @errorCode  method name must be a valid PHP name
     */
    const ATTR1252 = 'syntax error in method name : "%s"';
    /**
     * @errorCode  method name must be a valid PHP class name
     */
    const ATTR1253 = 'syntax error in class name in method call: "%s"';
    /**
     * @errorCode  call method can be return only one value
     * @note example : ::test():MY_RET
     */
    const ATTR1254 = 'only one output is possible in method "%s"';
    /**
     * @errorCode  generally when it is in constraint attribute
     */
    const ATTR1255 = 'no output is possible in method "%s"';
    /**
     * @errorCode  call method in phpfunc property must be defined in file method or in another class if precised
     */
    const ATTR1260 = 'method  "%s" (context : "%s") is not found for "%s" attribute';
    /**
     * @errorCode  call method require more arguments
     */
    const ATTR1261 = 'not enough argument call to use method "%s" (context "%s", need %d arguments) for "%s" attribute';
    /**
     * @errorCode  the phpfunc is not correct generally detected on inherited attributes
     */
    const ATTR1262 = 'syntax error in method "%s" phpfunc for "%s" attribute : %s';
    /**
     * @errorCode  the phpfunc method must be static if a classname is set
     * @note example : myClass::myStaticMethod()
     */
    const ATTR1263 = 'method "%s" (context : "%s") is not static phpfunc for "%s" attribute';
    /**
     * @errorCode  call method in phpfunc property must be defined in file method or in another class if precised
     * @see ATTR1260
     * @see ATTR1261
     * @see ATTR1262
     * @see ATTR1263
     */
    const ATTR1265 = 'phpfunc method error in "%s" family : %s';
    /**
     * @errorCode  call method in phpfunc property must be defined in file method or in another class if precised
     */
    const ATTR1266 = 'method  "%s" (context "%s" defined in parent family "%s") is not found for "%s" attribute';
    /**
     * @errorCode  enum declaration must be a set of key value
     * @note example : yellow|Yellow color,red|Red color
     */
    const ATTR1270 = 'syntax error in enum declaration near "%s"  for "%s" attribute';
    /**
     * @errorCode  the enum key must be a simple word without accent
     * @note example : yellow|Yellow color,red|Red color
     */
    const ATTR1271 = 'key must not have accent characters in enum declaration "%s"  for "%s" attribute';
    /**
     * @errorCode  the enum key is required
     * @note example : yellow|Yellow color,red|Red color
     */
    const ATTR1272 = 'key must not be empty in enum declaration "%s"  for "%s" attribute';
    /**
     * @errorCode  declaration of call constraint is not correct
     * @note example : ::isInteger(MY_ATTR)  or myClass::isSpecial(MY_ATTR)
     */
    const ATTR1400 = 'syntax error in constraint call for attribute "%s" : %s';
    /**
     * @errorCode  call method in constraint require more arguments
     */
    const ATTR1401 = 'not enough argument call to use constraint "%s" (need %d arguments, given %d) for "%s" attribute';
    /**
     * @errorCode  call constraint method must be defined in file method or in another class if precised
     */
    const ATTR1402 = 'constraint method "%s" is not found for "%s" attribute';
    /**
     * @errorCode  the phpfunc method must be static if a classname is set
     * @note example : myClass::myStaticMethod()
     */
    const ATTR1403 = 'method "%s" is not static phpfunc for "%s" attribute';
    /**
     * @errorCode  the constraint is not correct generally detected on inherited attributes
     */
    const ATTR1404 = 'syntax error in constraint "%s" for "%s" attribute : %s';
    /**
     * @errorCode  database type are incompatible with attribute type declaration
     */
    const ATTR1700 = 'database document column are erronous : %s';
    /**
     * @errorCode  due to postgresql limit, sql column number is limited
     * @note declaration for an attribute can create more than one sql column
     */
    const ATTR1701 = 'too many attributes : %d (maximum sql column is %d)';
    /**
     * @errorCode The value of the attribute will not be computed because the visibility is 'I'
     */
    const ATTR1800 = "value of attribute \"%s\" with phpfunc \"%s\" will not be computed because visibility is \"I\".";
}
