<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
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
        return $a + 1;
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
