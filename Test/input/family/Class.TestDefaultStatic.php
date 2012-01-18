<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

Class TestStaticDefault
{
    public static function multiAdd()
    {
        $tx = func_get_args();
        
        return array_sum($tx);
    }
    
    public static function semiColumnConcat()
    {
        $tx = func_get_args();
        
        return implode(':', $tx);
    }
}
?>
