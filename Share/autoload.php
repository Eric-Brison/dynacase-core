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
    private $_ignoreFileCache = array();
    private $_ignoreDirCache = array();
    
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
    /**
     * Check path components for .autoloader-ignore files and
     * evaluate if the given (file|dir)ame should be ignored.
     *
     * @param string $fileName The filename to check
     * @return bool bool(true) if the filename is ignored, bool(false) if the filename is not ignored
     */
    private function _inAutoloaderIgnore($fileName)
    {
        $pathList = explode('/', $fileName);
        for ($p = 1; $p < count($pathList); $p++) {
            $pathName = join('/', array_slice($pathList, 0, $p + 1));
            /* Check if this directory has already been checked
             * and use the previously evaluated status
            */
            if (array_key_exists($pathName, $this->_ignoreDirCache)) {
                if ($this->_ignoreDirCache[$pathName]) {
                    return true;
                } else {
                    continue;
                }
            }
            /* Check for .autoloader-ignore file in the parent dir */
            $baseName = basename($pathName);
            $dirName = dirname($pathName);
            $ignoreFile = $this->_getAutoloaderIgnoreFile($dirName);
            if ($ignoreFile === false) {
                continue;
            }
            /* Evaluate if the basename is to be ignored */
            foreach ($ignoreFile as $glob) {
                if (fnmatch($glob, $baseName)) {
                    if (is_dir($pathName)) {
                        $this->_ignoreDirCache[$pathName] = true;
                    }
                    return true;
                }
            }
            if (is_dir($pathName)) {
                $this->_ignoreDirCache[$pathName] = false;
            }
        }
        return false;
    }
    /**
     * Check for, and parse, a .autoloader-ignore file in the given directory
     *
     * @param string $dirName The directory
     * @return array|bool bool(false) on error, list of globs (from the .autoloader-ignore file) on success
     */
    private function _getAutoloaderIgnoreFile($dirName)
    {
        if (isset($this->_ignoreFileCache[$dirName])) {
            return $this->_ignoreFileCache[$dirName];
        }
        if (!is_dir($dirName)) {
            false;
        }
        $ignoreFile = $dirName . DIRECTORY_SEPARATOR . '.autoloader-ignore';
        if (!file_exists($ignoreFile)) {
            return $this->_ignoreFileCache[$dirName] = false;
        }
        /* Cache and return the parsed ignore list */
        return $this->_ignoreFileCache[$dirName] = $this->_parseAutoloaderIgnoreFile($ignoreFile);
    }
    /**
     * Load and parse a .autoloader-ignore file returning the list of globs.
     *
     * @param string $ignoreFile Path to the .autoloader-ignore file to parse
     * @return array|bool bool(false) on error, list of glob patterns on success
     */
    private function _parseAutoloaderIgnoreFile($ignoreFile)
    {
        $globList = file($ignoreFile);
        if ($globList === false) {
            return false;
        }
        $globList = array_filter(array_map(function ($e)
        {
            return rtrim($e, "\r\n");
        }
        , $globList) , function ($e)
        {
            return !preg_match('/^\s*(#.*)?$/', $e);
        });
        return $globList;
    }
}

\Dcp\DirectoriesAutoloader::instance(DEFAULT_PUBDIR, '.autoloader.cache')->addDirectory('./')->addCustomFilter('excludeSomeFiles')->register();
