<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Errors code used to checking workflows
 * @class ErrorCodeWFL
 * @brief List all error code for workflows
 */
class ErrorCodeWFL
{
    /**
     * transition declaration must be an array
     */
    const WFL0200 = 'workflow transition is not an array for class %s';
    /**
     * transition declaration must be an array
     */
    const WFL0201 = 'workflow transition unknow property %s for transition #%d in class %s (must be one of %s)';
    /**
     * the model transition is required to declare a transition
     */
    const WFL0202 = 'workflow transition #%d property \'t\' is mandatory in class %s';
    /**
     * transition are declared in a an array
     * @code
     * public $transitions = array(
     self::T1 => array(
     "m1" => "SendMailToVeto",
     "ask" => array(
     "wan_idveto",
     "wan_veto"
     ) ,
     "nr" => true
     ) ,
     * @endcode
     */
    const WFL0100 = 'workflow transition model is not an array for class %s';
    /**
     * field use for transition declaration must be valid
     */
    const WFL0101 = 'workflow transition unknow property %s for transition model %s in class %s (must be one of %s)';
    /**
     * number of transition model are limited
     */
    const WFL0102 = 'workflow %s number of transition model (found %d) exceed limit (max is %s)';
    /**
     * declaration of ask must be in an array
     * @code
     * public $transitions = array(
     self::T1 => array(
     "ask" => array(
     "wan_idveto",
     "wan_veto"
     )
     ) ,
     * @endcode
     */
    const WFL0103 = 'workflow transition ask is not an array for transition model %s in class %s';
    /**
     * ask array must reference workflow attributes
     */
    const WFL0104 = 'unknow attribute %s in workflow transition ask in class %s';
    /**
     * m1 property must be a worflow method
     */
    const WFL0105 = 'workflow unknow m1 method %s for transition model %s in class %s';
    /**
     * m2 property must be a worflow method
     */
    const WFL0106 = 'workflow unknow m2 method %s for transition model %s in class %s';
    /**
     * nr property is a boolean
     */
    const WFL0107 = 'workflow transition nr property is not a boolean for transition model %s in class %s';
    /**
     *
     */
    const WFL0050 = 'workflow transition or state key %s syntax error for %s (limit to %d alpha characters)';
    /**
     * if family is declared as workflow, classname field is required
     */
    const WFL0001 = 'workflow class name is empty';
    /**
     * the name of a workflow class must be a valid PHP name class
     */
    const WFL0002 = 'class name %s not valid';
    /**
     * PHP file is not valid
     */
    const WFL0003 = 'PHP parsing %s';
    /**
     * cannot find a class named as it is needed by workflow
     */
    const WFL0004 = 'workflow class %s not found';
    /**
     * the file of the workflow PHP class is not found
     */
    const WFL0005 = 'file name for %s not found';
    /**
     * the workflow class must be a descendant of WDoc class
     */
    const WFL0006 = 'workflow class %s not inherit from WDoc class';
    /**
     * the attrPrefix must not be empty
     */
    const WFL0007 = 'workflow : missing attrPrefix definition for %s class';
    /**
     * the attrPrefix must be composed of just few characters
     */
    const WFL0008 = 'workflow : syntax error attrPrefix for %s class (limit to 10 alpha characters)';
    /**
     * activies label is not an array
     */
    const WFL0051 = 'workflow activies labels is not an array for class %s';
    /**
     * activies label not match any state
     */
    const WFL0052 = 'unknow state %s for the activity %s for class %s';
}
