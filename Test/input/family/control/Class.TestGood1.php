<?php
/*
 * @author Anakeen
 * @package FDL
*/
namespace Tst;
class FamImp4 extends Famimp4Plus
{
    
    public function goodCompute1()
    {
        return 1;
    }
    public function goodCompute2($a)
    {
        return intval($a) + 1;
    }
    public function goodConstraint2($a)
    {
        return "$a";
    }
    /**
     * @apiExpose
     * @return int
     */
    public function forMenu()
    {
        return 1;
    }
}
