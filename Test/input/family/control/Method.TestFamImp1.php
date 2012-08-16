<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Ooo Layout test
 *
 * @author Anakeen
 * 
 * @package Dcp\Pu
 */
/**
 */
/**
 */
/**
 * @begin-method-ignore
 * this part will be deleted when construct document class until end-method-ignore
 */
class _TST_FAMIMP1 extends Doc
{
    /**
     * @end-method-ignore
     */
    public function preImport($extra)
    {
        $tkey = $tval = array();
        foreach ($extra as $id => $val) {
            $tkey[] = $id;
            $tval[] = $val;
        }
        $this->setValue("tst_extrakey", $tkey);
        $this->setValue("tst_extraval", $tval);
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
