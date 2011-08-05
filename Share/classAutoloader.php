<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace dcp;

class ExtensionFilterIteratorDecorator extends \FilterIterator
{
    private $_ext;
    /**
     * Check whether the current element of the iterator is acceptable
     *
     * @see FilterIterator::accept()
     *
     * @return bool true if the current element is acceptable, otherwise false.
     *
     */
    public function accept()
    {
        if (substr($this->current() , -1 * strlen($this->_ext)) === $this->_ext) {
            return is_readable($this->current());
        }
        return false;
    }
    /**
     * define which extension is allowed
     *
     * @param string $pExt extension
     *
     * @return void
     */
    public function setExtension($pExt)
    {
        $this->_ext = $pExt;
    }
}
/**
 * Interface for classes in charge of detecting classes declarations in files
 *
 * @author dev@dynacase.org <dev@dynacase.org>
 *
 */
interface IClassHunter {
    /**
     * Find all classes in given file
     *
     * @param text $pFileName file name
     *
     * @return array of strings
     *
     */
    public function find($pFileName);
}
/**
 * Class in charge of detecting classes declarations in files (only for PHP 5.3)
 *
 * @author dev@dynacase.org <dev@dynacase.org>
 *
 */
class ClassHunterForPHP5_3 implements IClassHunter
{
    /**
     * Find all classes in given file
     *
     * @param text $pFileName file name
     *
     * @return array of strings
     *
     */
    public function find($pFileName)
    {
        $toReturn = array();
        $tokens = token_get_all(file_get_contents($pFileName, false));
        $currentNamespace = '';
        $namespaceHunt = false;
        $validatedNamespaceHunt = false;
        $classHunt = false;
        $whitespaceCount = 0;
        foreach ($tokens as $token) {
            if (is_array($token)) {
                if ($token[0] === T_INTERFACE || $token[0] === T_CLASS) {
                    $classHunt = true;
                    continue;
                } elseif ($token[0] === T_NAMESPACE) {
                    $namespaceHunt = true;
                    continue;
                }
                if ($classHunt && $token[0] === T_STRING) {
                    $toReturn[(strlen($currentNamespace) > 0 ? $currentNamespace . '\\' : '') . $token[1]] = $pFileName;
                    $classHunt = false;
                } elseif ($namespaceHunt && $validatedNamespaceHunt && ($token[0] === T_STRING || $token[0] === T_NS_SEPARATOR)) {
                    $currentNamespace.= $token[1];
                } elseif ($namespaceHunt && !$validatedNamespaceHunt && $token[0] === T_WHITESPACE) {
                    $currentNamespace = '';
                    $validatedNamespaceHunt = true;
                } elseif ($namespaceHunt && !$validatedNamespaceHunt && $token[0] !== T_WHITESPACE) {
                    $namespaceHunt = false;
                }
            } else {
                if ($token === ';' || $token === '{') {
                    //can validate namespace only when using namespace{} syntax
                    if ($namespaceHunt && !$validatedNamespaceHunt && $token === '{') {
                        $currentNamespace = '';
                    }
                    $classHunt = false;
                    $namespaceHunt = false;
                    $validatedNamespaceHunt = false;
                }
            }
        }
        return $toReturn;
    }
}

class DirectoriesAutoloaderException extends \Exception
{
}
class DirectoriesAutoloader
{
    private $_classHunterStrategy;
    //--- Singleton
    
    /**
     * make constructor private
     *
     * @return void
     */
    private function __construct()
    {
    }
    /**
     *
     * @var DirectoriesAutoloader
     */
    private static $_instance = false;
    /**
     * get singleton for DirectoriesAutoloader
     *
     * @param string $pTmpPath     path to tmp dir
     * @param string $pTmpFileName tmp file name
     *
     * @return dcp\DirectoriesAutoloader
     */
    public static function instance($pTmpPath, $pTmpFileName = 'directoriesautoloader.cache.php')
    {
        if (self::$_instance === false) {
            self::$_instance = new DirectoriesAutoloader();
            self::$_instance->setCachePath($pTmpPath);
            self::$_instance->setCacheFileName($pTmpFileName);
            self::$_instance->_classHunterStrategy = ClassHunterFactory::create(PHP_VERSION);
        }
        return self::$_instance;
    }
    //--- /Singleton
    
    /**
     * register autoloader against SPL
     *
     * @return boolean
     */
    public function register()
    {
        return spl_autoload_register(array(
            $this,
            'autoload'
        ));
    }
    /**
     * force autoloader to regenerate cache now!
     *
     * @return dcp\DirectoriesAutoloader
     */
    public function forceRegenerate()
    {
        $this->_canRegenerate = true;
        $this->_classes = null;
        $this->_saveIncache();
        return self::$_instance;
    }
    //--- Cache
    private $_cachePath;
    private $_cacheFileName = 'directoriesautoloader.cache.php';
    /**
     * define the path of tmp directory
     *
     * @param string $pTmp cache directory path
     *
     * @throws \dcp\DirectoriesAutoloaderException
     *
     * @return void
     */
    public function setCachePath($pTmp)
    {
        if (!is_writable($pTmp)) {
            throw new DirectoriesAutoloaderException('Cannot write in given CachePath [' . $pTmp . ']');
        }
        $this->_cachePath = $pTmp;
    }
    /**
     * define the name of tmp file
     *
     * @param string $ptmpFileName tmp file name
     *
     * @return void
     */
    public function setCacheFileName($ptmpFileName)
    {
        $this->_cacheFileName = $ptmpFileName;
    }
    /**
     * get the fully qualified tmp file path
     *
     * @return string
     */
    public function getCacheFilePath()
    {
        return $this->_cachePath . '/' . $this->_cacheFileName;
    }
    //--- /Cache
    //--- custom filters
    private $_customFilterClasses = null;
    /**
     * add an instance of FilterIterator as custom filter
     *
     * @param string $pCustomFilterClass className of customFilter to add
     *
     * @throws dcp\DirectoriesAutoloaderException
     *
     * @return dcp\DirectoriesAutoloader
     */
    public function addCustomFilter($pCustomFilterClass)
    {
        $filterClassParents = class_parents($pCustomFilterClass);
        if (isset($filterClassParents['FilterIterator'])) {
            if (!is_array($this->_customFilterClasses)) {
                $this->_customFilterClasses = array(
                    $pCustomFilterClass
                );
                //error_log("adding [$pCustomFilterClass] as custom filter");
                
            } else {
                $this->_customFilterClasses[] = $pCustomFilterClass;
            }
        } else {
            throw new DirectoriesAutoloaderException('Custom filter class [' . $pCustomFilterClass . '] does not inherits from FilterIterator');
        }
        return self::$_instance;
    }
    //--- /custom filters
    //--- Autoload
    
    /**
     * autoloader
     *
     * @param string $pClassName name of the class to load
     *
     * @return boolean
     */
    public function autoload($pClassName)
    {
        //do we already know this class?
        if ($this->_loadClass($pClassName)) {
            return true;
        }
        //If we are allowed to regenerate autoload file, we try again
        if ($this->_canRegenerate) {
            $this->_canRegenerate = false; //avoid loops
            $this->_includesAll();
            $this->_saveInCache();
            return $this->autoload($pClassName);
        }
        //we really found nothing
        return false;
    }
    private $_canRegenerate = true;
    //--- /Autoload
    
    /**
     * look for all classes into registered directories
     *
     * @return void
     */
    private function _includesAll()
    {
        $cwd = getcwd();
        //include known classes
        foreach ($this->_directories as $directory => $recursive) {
            /*
             * Relative directories are handled relative to _cachePath
            */
            $changedCwd = false;
            if (strpos($directory, '/') !== 0) {
                $ret = chdir($this->_cachePath . DIRECTORY_SEPARATOR . $directory);
                if ($ret === false) {
                    continue;
                }
                $changedCwd = true;
            }
            
            $directories = new \AppendIterator();
            //add all paths that we want to browse
            if ($recursive) {
                $directories->append(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory)));
            } else {
                $directories->append(new \DirectoryIterator($directory));
            }
            if (is_array($this->_customFilterClasses)) {
                foreach ($this->_customFilterClasses as $customFilterClass) {
                    //error_log("trying to use [$customFilterClass] as filter");
                    //error_log("directories was a " . get_class($directories));
                    $directories = new $customFilterClass($directories);
                    //error_log("directories is now a " . get_class($directories));
                    
                }
            }
            //restrict files to php ones
            $files = new ExtensionFilterIteratorDecorator($directories);
            $files->setExtension('.php');
            foreach ($files as $fileName) {
                $classes = $this->_classHunterStrategy->find((string)$fileName);
                foreach ($classes as $className => $fileName) {
                    $this->_classes[strtolower($className) ] = $fileName;
                }
            }
            
            if ($changedCwd) {
                chdir($cwd);
            }
        }
        //error_log('included all classes as ' . var_export($this->_classes, true));
        
    }
    /**
     * array of files containing classes indexed by class name
     *
     * @var array $_classes
     */
    private $_classes = array();
    /**
     * write cache file
     *
     * @see DirectoriesAutoloader::_classes
     * @see DirectoriesAutoloader::getCacheFilePath
     *
     * @throws dcp\DirectoriesAutoloaderException
     *
     * @return void
     */
    private function _saveIncache()
    {
        foreach ($this->_classes as $className => & $fileName) {
            if (substr($fileName, 0, 2) == './') {
                $fileName = substr($fileName, 2);
            }
        }
        unset($fileName);
        $toSave = '<?php' . PHP_EOL;
        $toSave.= '// Cache generated at: ' . date(DATE_W3C) . PHP_EOL;
        $toSave.= '$classes = ' . var_export($this->_classes, true);
        $toSave.= '; ?>';
        //error_log('will save classes');
        if (file_put_contents($this->getCacheFilePath() , $toSave) === false) {
            throw new DirectoriesAutoloaderException('Cannot write cache file ' . $this->getCacheFilePath());
        }
    }
    /**
     * try to load a class
     *
     * @param string $pClassName class name
     *
     * @return boolean
     */
    private function _loadClass($pClassName)
    {
        $className = strtolower($pClassName);
        if (count($this->_classes) === 0) {
            if (is_readable($this->getCacheFilePath())) {
                //error_log('Loading classes from [' . $this->getCacheFilePath() . ']');
                require $this->getCacheFilePath();
                $this->_classes = $classes;
            }
        }
        if (isset($this->_classes[$className])) {
            require_once $this->_classes[$className];
            //error_log('loaded class [' . $className . ']');
            return true;
        }
        return false;
    }
    /**
     * add a directory to autoloaded directories
     *
     * @param string  $pDirectory directory path
     * @param boolean $pRecursive should we recursively scan this directory
     *
     * @throws dcp\DirectoriesAutoloaderException
     *
     * @return dcp\DirectoriesAutoloader
     */
    public function addDirectory($pDirectory, $pRecursive = true)
    {
        if (!is_readable($pDirectory)) {
            throw new DirectoriesAutoloaderException('Cannot read from [' . $pDirectory . ']');
        }
        $this->_directories[$pDirectory] = $pRecursive ? true : false;
        return self::$_instance;
    }
    private $_directories = array();
}
class ClassHunterFactory
{
    /**
     * Factory to decide which classHunter we use (should we use namespaces or not?)
     *
     * @param string $version php version
     *
     * @return \dcp\ClassHunterForPHP5_3|\dcp\ClassHunterForPHP5_2
     */
    public static function create($version)
    {
        return new ClassHunterForPHP5_3();
    }
}
