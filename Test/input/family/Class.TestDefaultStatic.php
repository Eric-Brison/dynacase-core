<?php
/*
 * @author Anakeen
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
