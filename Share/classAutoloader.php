<?php
/*
 * @author Gérald Croes
 * @author Anakeen
 * @package FDL
*/

namespace Dcp;

class Autoloader
{
    private static function getAutoloader()
    {
        return DirectoriesAutoloader::instance(DEFAULT_PUBDIR, '.autoloader.cache');
    }
    private static function configure(DirectoriesAutoloader $autoloader)
    {
        return $autoloader->addDirectory('./')->addCustomFilter('\Dcp\autoloaderIgnoreDotD');
    }
    /**
     * Register Dynacase Platform autoloader.
     */
    public static function register()
    {
        require_once 'WHAT/classAutoloader.php';
        require_once 'WHAT/classAutoloaderIgnoreDotD.php';
        include_once 'WHAT/Lib.Prefix.php';
        
        self::configure(self::getAutoloader())->register();
    }
    /**
     * Check if a class is known by the autoloader.
     * @param string $pClassName The class's name
     * @return bool true if the class is known by the autoloader, otherwise false.
     */
    public static function classExists($pClassName)
    {
        return self::getAutoloader()->classExists($pClassName);
    }
    /**
     *
     */
    public static function forceRegenerate()
    {
        require_once 'WHAT/Lib.Prefix.php';
        require_once 'WHAT/classAutoloaderIgnoreDotD.php';
        
        $autoloader = self::configure(self::getAutoloader());
        $cacheFile = $autoloader->getCacheFilePath();
        if (is_file($cacheFile)) {
            unlink($cacheFile);
        }
        $autoloader->forceRegenerate('');
    }
}

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
     * @param string $pFileName file name
     *
     * @return array of strings
     *
     */
    public function find($pFileName);
}
/**
 * Class in charge of detecting classes declarations in files (only for PHP 5.3)
 *
 * @author Gérald Croes
 * @author Anakeen
 *
 */
class ClassHunterForPHP5_3 implements IClassHunter
{
    /**
     * Find all classes in given file
     *
     * @param string $pFileName file name
     *
     * @return array of strings
     *
     */
    public function find($pFileName)
    {
        if (!defined("T_TRAIT")) define("T_TRAIT", "T_TRAIT"); // defined in PHP 5.4 only
        $toReturn = array();
        $tokens = token_get_all(file_get_contents($pFileName, false));
        $currentNamespace = '';
        $namespaceHunt = false;
        $validatedNamespaceHunt = false;
        $classHunt = false;
        $whitespaceCount = 0;
        foreach ($tokens as $token) {
            if (is_array($token)) {
                if ($token[0] === T_INTERFACE || $token[0] === T_CLASS || $token[0] === T_TRAIT) {
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
    /**
     * @var \dcp\ClassHunterForPHP5_3
     */
    private $_classHunterStrategy;
    private $_lockfd = false;
    //--- Singleton
    
    /**
     * make constructor private
     *
     * @return \dcp\DirectoriesAutoloader
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
     * @return \dcp\DirectoriesAutoloader
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
     * @return \dcp\DirectoriesAutoloader
     */
    public function forceRegenerate($className)
    {
        $this->_canRegenerate = true;
        $this->_classes = array();
        $this->_regenerate($className);
        $this->_saveIncache();
        
        return self::$_instance;
    }
    /*
     * Enable dryrun/test mode
    */
    private $_dryRun = false;
    public function dryRun($dryRun = true)
    {
        $this->_dryRun = $dryRun;
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
    /**
     * get the cache lock file
     *
     * @return string
     */
    private function _getCacheLockFilePath()
    {
        return $this->getCacheFilePath() . '.lock';
    }
    //--- /Cache
    //--- custom filters
    private $_customFilterClasses = null;
    /**
     * add an instance of FilterIterator as custom filter
     *
     * @param string $pCustomFilterClass className of customFilter to add
     *
     * @throws \dcp\DirectoriesAutoloaderException
     *
     * @return \dcp\DirectoriesAutoloader
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
     * @throws DirectoriesAutoloaderException
     */
    private function _lock()
    {
        if ($this->_dryRun) {
            return;
        }
        if ($this->_lockfd !== false) {
            throw new DirectoriesAutoloaderException(sprintf("Cache lock is already opened."));
        }
        $lockfile = $this->_getCacheLockFilePath();
        $this->_lockfd = fopen($lockfile, 'a');
        if ($this->_lockfd === false) {
            throw new DirectoriesAutoloaderException(sprintf("Error opening cache lock file '%s'.", $lockfile));
        }
        if (flock($this->_lockfd, LOCK_EX) === false) {
            throw new DirectoriesAutoloaderException(sprintf("Error locking cache lock file '%s'.", $lockfile));
        }
    }
    /**
     * @throws DirectoriesAutoloaderException
     */
    private function _unlock()
    {
        if ($this->_dryRun) {
            return;
        }
        if ($this->_lockfd === false) {
            throw new DirectoriesAutoloaderException(sprintf("Cache lock not opened."));
        }
        if (flock($this->_lockfd, LOCK_UN) === false) {
            fclose($this->_lockfd);
            $this->_lockfd = false;
            throw new DirectoriesAutoloaderException(sprintf("Error unlocking cache lock."));
        }
        fclose($this->_lockfd);
        $this->_lockfd = false;
    }
    /**
     * @param $pClassName
     * @return bool
     * @throws \Exception
     */
    private function _regenerate($pClassName)
    {
        $this->_lock();
        /*
         * Re-check the cache in case of a concurrent process
         * which might have already re-built the cache.
        */
        $this->_classes = array(); // Empty the cache to force a re-validation from the cache file
        if ($this->_loadClass($pClassName)) {
            $this->_unlock();
            return true;
        }
        /*
         * If the re-validation failed, then the cache is now re-populated with the content of the cache file,
         * so we re-empty it before regenerating the new content
        */
        $this->_classes = array();
        /*
         * Re-build the cache
        */
        try {
            $this->_includesAll();
            $this->addFamilies('FDLGEN');
            $this->_saveInCache();
        }
        catch(\Exception $e) {
            $this->_unlock();
            throw $e;
        }
        $this->_unlock();
        return false;
    }
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
            $this->_regenerate($pClassName);
            $this->_canRegenerate = false; //avoid loops
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
        $err = "";
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
                $directories->append(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory) , \RecursiveIteratorIterator::LEAVES_ONLY, \RecursiveIteratorIterator::CATCH_GET_CHILD));
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
                foreach ($classes as $className => $fileName2) {
                    $fileName1 = (isset($this->_classes[strtolower($className) ])) ? $this->_classes[strtolower($className) ] : null;
                    if ($fileName1 !== null && realpath($fileName1) !== false && realpath($fileName2) !== realpath($fileName1)) {
                        $err.= ($err ? "\n" : '') . sprintf(_("Class %s from file %s already declared in autoload with file %s") , $className, $fileName2, $this->_classes[strtolower($className) ]);
                    } else {
                        $this->_classes[strtolower($className) ] = $fileName2;
                    }
                }
            }
            if ($changedCwd) {
                chdir($cwd);
            }
        }
        if ($err) {
            require_once "WHAT/Class.Log.php";
            $log = new \Log();
            $log->setLogLevel("WEI");
            $log->error($err);
            throw new DirectoriesAutoloaderException($err);
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
     * get file path where className is defined
     * @param string $className
     * @return string|null
     */
    public function getClassFile($className)
    {
        return isset($this->_classes[strtolower($className) ]) ? $this->_classes[strtolower($className) ] : null;
    }
    /**
     * write cache file
     *
     * @see DirectoriesAutoloader::_classes
     * @see DirectoriesAutoloader::getCacheFilePath
     *
     * @throws \dcp\DirectoriesAutoloaderException
     *
     * @return void
     */
    private function _saveIncache()
    {
        if ($this->_dryRun) {
            return;
        }
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
        /*
         * Atomically write .autoloader.cache file
        */
        $cacheFileName = $this->getCacheFilePath();
        $cacheDirName = dirname($cacheFileName);
        $tmpFile = tempnam($cacheDirName, '.autoloader.cache.tmp');
        if ($tmpFile === false) {
            throw new DirectoriesAutoloaderException("Error creating temporary autoloader cache file.");
        }
        if (file_put_contents($tmpFile, $toSave) === false) {
            unlink($tmpFile);
            throw new DirectoriesAutoloaderException(sprintf("Error writing cache content to temporary file '%s'", $tmpFile));
        }
        if (rename($tmpFile, $cacheFileName) === false) {
            unlink($tmpFile);
            throw new DirectoriesAutoloaderException(sprintf("Error renaming temporary cache file '%s' to '%s'.", $tmpFile, $cacheFileName));
        }
        chmod($cacheFileName, (0666 ^ umask()));
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
        if ($this->classExists($className)) {
            include_once ('WHAT/Lib.Prefix.php');
            if (!file_exists(DEFAULT_PUBDIR . DIRECTORY_SEPARATOR . $this->_classes[$className])) {
                return false;
            }
            require_once $this->_classes[$className];
            if (!class_exists($className, false) && !interface_exists($className, false)) {
                if (function_exists("trait_exists") && trait_exists($className, false)) {
                    return true;
                }
                return false;
            }
            return true;
        }
        return false;
    }
    /**
     * Check if a class exists in the autoloader's cache
     *
     * @param $pClassName
     * @return bool
     */
    public function classExists($pClassName)
    {
        $className = ltrim(strtolower($pClassName),"\\");
        if (count($this->_classes) === 0) {
            if (is_readable($this->getCacheFilePath())) {
                $classes = array();
                require $this->getCacheFilePath(); // load $classes here
                $this->_classes = $classes;
            }
        }
        return isset($this->_classes[$className]);
    }
    /**
     * add a directory to autoloaded directories
     *
     * @param string  $pDirectory directory path
     * @param boolean $pRecursive should we recursively scan this directory
     *
     * @throws \dcp\DirectoriesAutoloaderException
     *
     * @return \dcp\DirectoriesAutoloader
     */
    public function addDirectory($pDirectory, $pRecursive = true)
    {
        $absoluteDirectory = $pDirectory;
        if (strpos($pDirectory, '/') !== 0) {
            $absoluteDirectory = $this->_cachePath . DIRECTORY_SEPARATOR . $pDirectory;
        }
        if (!is_readable($absoluteDirectory)) {
            throw new DirectoriesAutoloaderException('Cannot read from [' . $absoluteDirectory . ']');
        }
        $this->_directories[$pDirectory] = $pRecursive ? true : false;
        return self::$_instance;
    }
    /**
     * add Family classes in cache
     * @param string $genDirectory generate family directory
     * @return bool|DirectoriesAutoloader
     * @throws DirectoriesAutoloaderException
     */
    public function addFamilies($genDirectory)
    {
        include_once ("Lib.Common.php");
        $sql = "select * from pg_tables where tablename = 'docfam'";
        $err = \simpleQuery('', $sql, $exists);
        if (count($exists) > 0) {
            $sql = 'select id, "name" from docfam where name is not null order by id';
            $err = \simpleQuery('', $sql, $famNames);
            if ($err) {
                throw new DirectoriesAutoloaderException('Cannot access family name [' . $err . ']');
            }
            foreach ($famNames as $aFam) {
                $aFamName = $aFam["name"];
                $aFamId = $aFam["id"];
                $this->_classes['_' . strtolower($aFamName) ] = sprintf("%s/Class.Doc%d.php", $genDirectory, $aFamId);
                $this->_classes['dcp\\family\\' . strtolower($aFamName) ] = sprintf("%s/Class.Doc%d.php", $genDirectory, $aFamId);
                $this->_classes['dcp\\attributeidentifiers\\' . strtolower($aFamName) ] = sprintf("%s/Class.Attrid%d.php", $genDirectory, $aFamId);
            }
        }
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
     * @return \dcp\ClassHunterForPHP5_3
     */
    public static function create($version)
    {
        return new ClassHunterForPHP5_3();
    }
}
