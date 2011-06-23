<?php

require_once 'WHAT/classAutoloader.php';

class excludeSomeFiles extends FilterIterator
{
    public function accept()
    {
        $c = $this->current();
        
        if (substr($c, -4) != '.php') return false;
        $exclusionPatterns = array(
            './lib/zendframework/',
            './lib/ext/',
            './context/'
        );
        
        foreach ( $exclusionPatterns as $pattern ) {
            if (substr($c, 0, strlen($pattern)) == $pattern) {
                return false;
            }
        }
        
        if (preg_match('/Method\./', $c)) return false;
        return is_readable($c);
    }
}

\dcp\DirectoriesAutoloader::instance('.', '.autoloader.cache')->addDirectory('./')->addCustomFilter('excludeSomeFiles')->register();
?>