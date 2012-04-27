<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package DCP
*/
/**
 * @begin-method-ignore
 * this part will be deleted when construct document class until end-method-ignore
 */
class TST_GETSEARCHMETHODS_OVERRIDE extends Doc
{
    /**
     * @end-method-ignore
     */
    /**
     * @param string $attrId
     * @param string $attrType
     * @return array
     */
    public function getSearchMethods($attrId, $attrType = '')
    {
        $methodList = parent::getSearchMethods($attrId, $attrType);
        
        $methodList = array_merge($methodList, array(
            array(
                'label' => 'One year later',
                'method' => '::getDate(365)'
            ) ,
            array(
                'label' => 'One year earlier',
                'method' => '::getDate(-365)'
            ) ,
            array(
                'label' => 'InvalidSearchMethod',
                'method' => 'InvalidSearchMethod'
            )
        ));
        
        return $methodList;
    }
    /**
     * Foo
     *
     * @searchLabel The "Foo" string
     * @searchType text
     *
     * @return string
     */
    public function getFoo()
    {
        return "Foo";
    }
    /**
     * The usual two cents advice...
     *
     * @searchLabel Two cents
     * @searchType double
     *
     * @param int $plus
     * @return float
     */
    public function getTwoCents($plus = 0)
    {
        return 0.02;
    }
    /**
     * Invalid search method
     * @return null
     */
    public function getInvalidSearchMethod() {
        return null;
    }
    /**
     * @begin-method-ignore
     * this part will be deleted when construct document class until end-method-ignore
     */
}
/**
 * @end-method-ignore
 */
