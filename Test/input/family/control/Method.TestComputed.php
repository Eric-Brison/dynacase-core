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
class _TST_FAMILYCOMPUTED extends Doc
{
    /**
     * @end-method-ignore
     */
    public function isOne()
    {
        return 1;
    }
    
    public function oneMore($x)
    {
        return intval($x) + 1;
    }
    
    public function itself($x)
    {
        return $x;
    }
    
    public function isNull($x = null)
    {
        if ($x === null) return "NULL";
        return $x;
    }
    
    public function simpleAdd()
    {
        $tx = func_get_args();
        
        return array_sum($tx);
    }
    
    public function simpleCount(array $t)
    {
        return count($t);
    }
    public function bracketConcat()
    {
        $tx = func_get_args();
        
        return '[' . implode('][', $tx) . ']';
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
