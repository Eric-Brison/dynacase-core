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
    private $_ignoreGlobList = false;
    
    public function accept()
    {
        $c = $this->current()->getPathname();
        
        if (substr($c, -4) != '.php') return false;
        if (preg_match('/^Method/', basename($c))) return false;
        
        $exclusionPatterns = array(
            './lib/ext/',
            './lib/zendframework/',
            './lib/jquery-dataTables/',
            './lib/jquery-ui/'
        );
        
        foreach ($exclusionPatterns as $pattern) {
            if (substr($c, 0, strlen($pattern)) == $pattern) {
                return false;
            }
        }
        
        if ($this->_inAutoloaderIgnore($c)) return false;
        
        return is_readable($c);
    }
    private function _inAutoloaderIgnoreDotD($fileName)
    {
    }
    /**
     * Check filname againts glob patterns from .autoloader-ignore.d files and
     * evaluate if the given (file|dir)ame should be ignored.
     *
     * @param string $fileName The filename to check
     * @return bool bool(true) if the filename is ignored, bool(false) if the filename is not ignored
     */
    private function _inAutoloaderIgnore($fileName)
    {
        if ($this->_ignoreGlobList === false) {
            $this->_ignoreGlobList = $this->_parseAutoloaderIgnoreDotD();
        }
        if (strpos($fileName, './') === 0) {
            $fileName = substr($fileName, 2);
        }
        foreach ($this->_ignoreGlobList as $glob) {
            if (fnmatch($glob, $fileName)) {
                return true;
            }
        }
        return false;
    }
    /**
     * Load and parse a .autoloader-ignore file returning the list of globs.
     *
     * @param string $ignoreFile Path to the .autoloader-ignore file to parse
     * @throws Dcp\DirectoriesAutoloaderException
     * @return array|bool bool(false) on error, list of glob patterns on success
     */
    private function _parseAutoloaderIgnoreFile($ignoreFile)
    {
        $globList = file($ignoreFile);
        if ($globList === false) {
            throw new \Dcp\DirectoriesAutoloaderException(sprintf("Error opening '.autoloader-ignore.d' directory '%s'.", $dir));
        }
        $globList = array_filter(array_map(function ($e)
        {
            /* Remove trailing CR+LF */
            return rtrim($e, "\r\n");
        }
        , $globList) , function ($e)
        {
            /* Skip blank and comment lines */
            return !preg_match('/^\s*(#.*)?$/', $e);
        });
        $globList = array_map(function ($e)
        {
            /*
             * Strip leading './' from glob patterns as they are
             * already considered relatives to the current directory.
            */
            if (strpos($e, './') === 0) {
                $e = substr($e, 2);
            }
            return $e;
        }
        , $globList);
        return $globList;
    }
    /**
     * Load and parse ignore files from `$wpub/.autoloader-ignore.d` dir
     *
     * @throws Dcp\DirectoriesAutoloaderException
     * @return array|bool bool(false) on error, or list of glob patterns on success
     */
    private function _parseAutoloaderIgnoreDotD()
    {
        global $pubdir;
        $globList = array();
        $dir = $pubdir . DIRECTORY_SEPARATOR . '.autoloader-ignore.d';
        if (!is_dir($dir)) {
            return $globList;
        }
        $dh = opendir($dir);
        if ($dh === false) {
            throw new \Dcp\DirectoriesAutoloaderException(sprintf("Error opening '.autoloader-ignore.d' directory '%s'.", $dir));
        }
        while ($file = readdir($dh)) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $globList = array_merge($globList, $this->_parseAutoloaderIgnoreFile($dir . DIRECTORY_SEPARATOR . $file));
        }
        closedir($dh);
        return $globList;
    }
}

\Dcp\DirectoriesAutoloader::instance(DEFAULT_PUBDIR, '.autoloader.cache')->addDirectory('./')->addCustomFilter('excludeSomeFiles')->register();
