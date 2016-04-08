<?php
/*
 * @author Anakeen
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
class _TST_SYNTAXERROR extends Doc
{
    /**
     * @end-method-ignore
     */
    public function goodCompute1()
    {
        return 1// HERE SYNTAX ERROR
    }
    public function goodCompute2($a)
    {
        return $a + 1;
    }
    public function goodConstraint2($a)
    {
        return "$a";
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
