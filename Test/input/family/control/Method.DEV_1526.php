<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 * Test HTML Text in ODT representation
 *
 * @author Anakeen
 * @version $Id:  $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package Dcp\Pu
 */
/**
 */
/**
 * @begin-method-ignore
 * this part will be deleted when construct document class until end-method-ignore
 */
class _IGROUPDEV extends _GROUP
{
    /**
     * @end-method-ignore
     */
    function dev_1526($target, $ulink, $abstract)
    {
        $this->lay->setColumn('V_STR_COL1', $this->getTValue('COL_1'));
        
        $oooTValue = array();
        $attrName = 'ARR_3_COL_1';
        foreach ($this->getTValue($attrName) as $v) {
            array_push($oooTValue, $this->getOOoValue($this->getAttribute($attrName) , $v));
        }
        $this->lay->setColumn('MANUAL_SET_ARR_3_COL_1', $oooTValue);
        
        return $this->viewDefaultCard($target, $ulink, $abstract);
    }
    /**
     * @begin-method-ignore
     * this part will be deleted when construct document class until end-method-ignore
     */
}
/**
 * @end-method-ignore
 */
?>
