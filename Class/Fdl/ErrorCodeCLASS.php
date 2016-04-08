<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Errors code used to checking CLASS keyword
 * @class ErrorCodeRESE
 * @brief List all error code for CLASS
 * @see ErrorCode
 */
class ErrorCodeCLASS
{
    /**
     * @errorCode syntax error in className
     */
    const CLASS0001 = 'Class name "%s" is not a valid name for PHP class';
    /**
     * @errorCode syntax error in class file.
     */
    const CLASS0002 = 'Error in class file "%s" in family "%s" : %s';
    /**
     * @errorCode class file must be detected by autoloader
     */
    const CLASS0003 = 'No php class file found for "%s" class in family "%s"';
    /**
     * @errorCode class name object contruct failed
     */
    const CLASS0004 = 'Cannot create object "%s" ("%s") class in family "%s" : %s';
    /**
     * @errorCode class name must not be abstract, interface
     */
    const CLASS0005 = 'Class "%s" ("%s") is not instanciable class in family "%s"';
    /**
     * @errorCode in case of inherits - family class must inheriit from generacted class
     */
    const CLASS0006 = 'Class "%s" ("%s") must be a subclass of "%s" in family "%s"';
    /**
     * @errorCode in case of inherits - family parent must have name
     */
    const CLASS0007 = 'Class "%s" ("%s") has family parent without name  in family "%s"';
}
