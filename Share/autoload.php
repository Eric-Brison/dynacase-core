<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

require_once 'WHAT/classAutoloader.php';
include_once 'WHAT/Lib.Prefix.php';

class excludeSomeFiles extends FilterIterator
{
    public function accept()
    {
        $c = $this->current();
        
        if (substr($c, -4) != '.php') return false;
        $exclusionPatterns = array(
            './.img-resize/',
            './context/',
            './fckeditor/',
            './jscalendar/',
            './Images/',
            './img-cache/',
            './lib/ext/',
            './lib/zendframework/',
            './locale/',
            './session/',
            './FDLGEN/',
            './vaultfs/'
        );
        
        foreach ($exclusionPatterns as $pattern) {
            if (substr($c, 0, strlen($pattern)) == $pattern) {
                return false;
            }
        }
        
        if (preg_match('/^Method/', basename($c))) return false;
        return is_readable($c);
    }
}

\dcp\DirectoriesAutoloader::instance(DEFAULT_PUBDIR, '.autoloader.cache')->addDirectory('./')->addCustomFilter('excludeSomeFiles')->register();
?>