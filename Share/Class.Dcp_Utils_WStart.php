<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Utils;

interface WStartStdioInterface {
    public function wstart_stdout($msg);
    public function wstart_stderr($msg);
}

class WStartException extends \Exception
{
};

class WStartDefaultStdio implements WStartStdioInterface
{
    public function wstart_stdout($msg)
    {
        return;
    }
    public function wstart_stderr($msg)
    {
        return;
    }
}

class WStartInternals
{
    /**
     * @var WStartStdioInterface
     */
    protected $stdio = null;
    protected $verbose = false;
    protected $contextRoot = false;
    
    public function __construct($contextRoot)
    {
        $this->setVerbose(false);
        $this->setStdio(new WStartDefaultStdio());
        $this->setContextRoot($contextRoot);
    }
    protected function setContextRoot($contextRoot)
    {
        if (!is_string($contextRoot) || strlen($contextRoot) <= 0) {
            throw new WStartException(sprintf("contextRoot must not be empty."));
        }
        if (!is_dir($contextRoot)) {
            throw new WStartException(sprintf("contextRoot '%s' is not a directory.", $contextRoot));
        }
        if (!is_readable($contextRoot)) {
            throw new WStartException(sprintf("contextRoot '%s' is not readable.", $contextRoot));
        }
        if (!is_writable($contextRoot)) {
            throw new WStartException(sprintf("contextRoot '%s' is not writable.", $contextRoot));
        }
        if (($realContextRoot = realpath($contextRoot)) === false) {
            throw new WStartException(sprintf("could not get real path from contextRoot '%s'.", $contextRoot));
        }
        $this->contextRoot = $realContextRoot;
    }
    /**
     * Scan given directory and delete dead symlinks (i.e. symlinks pointing to non-existing files)
     *
     * @param string $dir
     * @throws WStartException
     */
    public function deleteDeadLinks($dir)
    {
        if (($dh = opendir($dir)) === false) {
            throw new WStartException(sprintf("Error opening directory '%s'.", $dir));
        }
        while (($file = readdir($dh)) !== false) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $absLink = $this->absolutize($dir . DIRECTORY_SEPARATOR . $file);
            if (!is_link($absLink)) {
                continue;
            }
            $target = readlink($absLink);
            if ($target === false) {
                continue;
            }
            if (substr($target, 0, 1) != '/') {
                $target = dirname($absLink) . DIRECTORY_SEPARATOR . $target;
            }
            if (file_exists($target)) {
                continue;
            }
            $this->verbose(2, sprintf("Deleting link '%s' to non-existing file '%s'.\n", $absLink, $target));
            if (unlink($absLink) === false) {
                closedir($dh);
                throw new WStartException(sprintf("Error deleting dead symlink '%s' to '%s'.", $absLink, $target));
            }
        }
        closedir($dh);
    }
    /**
     * Link files from source dir to destination dir.
     *
     * @param string $sourceDir Source dir from which files are to be linked
     * @param string $destDir Destination dir to which the symlinks will be created
     * @param array $linked List of conflicting/duplicates files (i.e. source files with the same name)
     * @throws WStartException
     */
    public function linkFiles($sourceDir, $destDir, &$linked = array())
    {
        $this->verbose(2, sprintf("Processing files from '%s'.\n", $sourceDir));
        if (($dh = opendir($this->absolutize($sourceDir))) === false) {
            throw new WStartException(sprintf("Error opening directory '%s'.", $this->absolutize($sourceDir)));
        }
        while (($file = readdir($dh)) !== false) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $relSourceFile = $this->relativize($sourceDir . DIRECTORY_SEPARATOR . $file);
            $absSourceFile = $this->absolutize($relSourceFile);
            if (!is_file($absSourceFile) && !is_dir($absSourceFile)) {
                continue;
            }
            $relTarget = '..' . DIRECTORY_SEPARATOR . $relSourceFile;
            $absLink = $this->absolutize($destDir . DIRECTORY_SEPARATOR . basename($relSourceFile));
            if (!isset($linked[$absLink])) {
                $linked[$absLink] = array();
            }
            if (is_link($absLink)) {
                $source = readlink($absLink);
                if ($source !== false && $source == $relTarget) {
                    $linked[$absLink][] = $relTarget;
                    continue;
                }
                if (unlink($absLink) === false) {
                    closedir($dh);
                    throw new WStartException(sprintf("Error removing symlink '%s'.", $absLink));
                }
            }
            $this->verbose(2, sprintf("Linking '%s' to '%s'.\n", $relTarget, $absLink));
            if (symlink($relTarget, $absLink) === false) {
                closedir($dh);
                throw new WStartException(sprintf("Error symlinking '%s' to '%s'.", $relTarget, $absLink));
            }
            $linked[$absLink][] = $relTarget;
        }
        closedir($dh);
    }
    /**
     * Create a directory if it does not already exists...
     *
     * @param string $dir
     * @throws WStartException
     */
    protected function mkdir($dir)
    {
        if (is_dir($dir)) {
            return;
        }
        if (mkdir($dir) === false) {
            throw new WStartException(sprintf("Error creating directory '%s'.", $dir));
        }
    }
    /**
     * Remove files matching the specified regex in the given directory
     *
     * @param $dir
     * @param $regex
     * @throws WStartException
     */
    protected function removeFilesByRegex($dir, $regex)
    {
        if (($dh = opendir($dir)) === false) {
            throw new WStartException(sprintf("Error opening directory '%s'.", $dir));
        }
        while (($file = readdir($dh)) !== false) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $ret = preg_match($regex, $file);
            if ($ret === false) {
                closedir($dh);
                throw new WStartException(sprintf("Malformed regex pattern '%s'.", $regex));
            }
            if ($ret === 0) {
                continue;
            }
            $this->verbose(2, sprintf("Removing '%s'.\n", $dir . DIRECTORY_SEPARATOR . $file));
            if (unlink($dir . DIRECTORY_SEPARATOR . $file) == false) {
                closedir($dh);
                throw new WStartException(sprintf("Error removing file '%s'.", $file));
            }
        }
        closedir($dh);
    }
    /**
     * Returns surdirs containing a specific subdir
     *
     * @param $subdir
     * @return string[] list of dir/subdir relative to contextRoot
     */
    public function getSubDirs($subdir)
    {
        $appImagesDirs = array();
        if (($dh = opendir($this->contextRoot)) === false) {
            return $appImagesDirs;
        }
        while (($elmt = readdir($dh)) !== false) {
            if ($elmt == '.' || $elmt == '..') {
                continue;
            }
            if ($elmt === 'supervisor') {
                continue;
            }
            if (!is_dir($this->absolutize($elmt))) {
                continue;
            }
            if (!is_dir($this->absolutize($elmt . DIRECTORY_SEPARATOR . $subdir))) {
                continue;
            }
            $appImagesDirs[] = $elmt . DIRECTORY_SEPARATOR . $subdir;
        }
        closedir($dh);
        return $appImagesDirs;
    }
    public function getImagesDirs()
    {
        return $this->getSubDirs('Images');
    }
    public function getDocsDirs()
    {
        return $this->getSubDirs('Docs');
    }
    protected function debug($msg)
    {
        $this->stdio->wstart_stderr($msg);
    }
    /**
     * Print a message with the specified verbose level.
     *
     * @param $level
     * @param $msg
     */
    protected function verbose($level, $msg)
    {
        if ($this->verbose <= 0) {
            return;
        }
        if ($level <= $this->verbose) {
            $this->stdio->wstart_stdout($msg);
        }
    }
    /**
     * @param int $verbose Verbose level (e.g. 1, 2, etc.)
     * @return bool
     */
    public function setVerbose($verbose)
    {
        $previous = $this->verbose;
        $this->verbose = (int)$verbose;
        return $previous;
    }
    /**
     * @param $stdio
     * @return WStartStdioInterface
     * @throws WStartException
     */
    public function setStdio($stdio)
    {
        if (!is_a($stdio, '\Dcp\Utils\WStartStdioInterface')) {
            throw new WStartException(sprintf("Wrong class for stdioInterface: %s", get_class($stdio)));
        }
        $previous = $this->stdio;
        $this->stdio = $stdio;
        return $previous;
    }
    /**
     * Compute absolute path from context's root
     *
     * - If the file is relative, then the absolute path is computed relative to the context's root.
     * - If the file is already in a absolute form, then their current absolute form is used.
     *
     * @param $file
     * @return string
     */
    public function absolutize($file)
    {
        if (substr($file, 0, 1) != '/') {
            $file = $this->contextRoot . DIRECTORY_SEPARATOR . $file;
        }
        return $file;
    }
    /**
     * Compute relative path from context's root
     *
     * - If the file is already in a relative form, then their current relative form is used.
     * - If the file is absolute and located under the context'root, then the relative path from the context's root is
     *   used.
     * - If the file is absolute and located outside the context's root, then an exception is thrown.
     *
     * @param $file
     * @return string
     * @throws WStartException
     */
    public function relativize($file)
    {
        if (substr($file, 0, 1) != '/') {
            return $file;
        }
        if ($file == $this->contextRoot) {
            return '.';
        }
        if (strpos($file, $this->contextRoot . DIRECTORY_SEPARATOR) === 0) {
            $file = substr($file, strlen($this->contextRoot . DIRECTORY_SEPARATOR));
            if ($file == '') {
                $file = '.';
            }
            return $file;
        }
        throw new WStartException(sprintf("Could not relativize '%s' to '%s'.", $file, $this->contextRoot));
    }
    /**
     * @param $file
     * @param $callback
     * @throws WStartException
     */
    public function sedFile($file, $callback)
    {
        if (($perms = fileperms($file)) === false) {
            throw new WStartException(sprintf("Error reading permissions for '%s'.", $file));
        }
        $content = file_get_contents($file);
        if ($content === false) {
            throw new WStartException(sprintf("Error reading content from '%s'.", $file));
        }
        $content = call_user_func_array($callback, array(
            $content
        ));
        $tmpFile = tempnam(getTmpDir() , 'sedFile');
        if ($tmpFile === false) {
            throw new WStartException(sprintf("Error creating temporary file."));
        }
        if (file_put_contents($tmpFile, $content) === false) {
            unlink($tmpFile);
            throw new WStartException(sprintf("Error writing content to temporary file '%s'.", $tmpFile));
        }
        if (rename($tmpFile, $file) === false) {
            unlink($tmpFile);
            throw new WStartException(sprintf("Error renaming '%s' to '%s'.", $tmpFile, $file));
        }
        /* Replicate original rights with extended rights */
        $perms = $perms & 07777;
        if (chmod($file, $perms) === false) {
            throw new WStartException(sprintf("Error applying permissions '%o' to '%s'.", $perms, $file));
        }
    }
}

class WStart extends WStartInternals
{
    /**
     *
     */
    public function clearAutoloadCache()
    {
        $this->verbose(1, sprintf("[+] Re-generating class autoloader.\n"));
        require_once sprintf('%s/WHAT/classAutoloader.php', $this->contextRoot);
        \Dcp\Autoloader::forceRegenerate();
        $this->verbose(1, sprintf("[+] Done.\n"));
    }
    /**
     * @throws WStartException
     */
    public function imageAndDocsLinks()
    {
        $this->verbose(1, sprintf("[+] Re-generating Images and Docs symlinks.\n"));
        $linked = array();
        /* Images */
        $imagesDir = $this->absolutize('Images');
        $this->mkdir($imagesDir);
        $dirs = $this->getImagesDirs();
        foreach ($dirs as $dir) {
            $this->linkFiles($dir, $imagesDir, $linked);
        }
        $this->deleteDeadLinks($imagesDir);
        /* Docs */
        $docsDir = $this->absolutize('Docs');
        $this->mkdir($docsDir);
        $dirs = $this->getDocsDirs();
        foreach ($dirs as $dir) {
            $this->linkFiles($dir, $this->contextRoot . DIRECTORY_SEPARATOR . 'Docs', $linked);
        }
        $this->deleteDeadLinks($docsDir);
        /* Check for conflicts */
        foreach ($linked as $link => $targetList) {
            if (count($targetList) <= 1) {
                continue;
            }
            $targets = join(', ', $targetList);
            $this->debug(sprintf("WARNING: symlink '%s' has multiple targets: %s\n", $link, $targets));
        }
        $this->verbose(1, sprintf("[+] Done.\n"));
    }
    /**
     * @throws WStartException
     */
    public function clearFileCache()
    {
        $this->verbose(1, sprintf("[+] Clearing cached content.\n"));
        $cacheDir = $this->absolutize('var' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'image');
        $this->removeFilesByRegex($cacheDir, '/(?:png|gif|xml|src)$/');
        $this->verbose(1, sprintf("[+] Done.\n"));
    }
    /**
     * @throws WStartException
     */
    public function refreshJsVersion()
    {
        $this->verbose(1, sprintf("[+] Incrementing WVERSION.\n"));
        $cmd = sprintf("%s/wsh.php --api=refreshjsversion 2>&1", escapeshellarg($this->contextRoot));
        exec($cmd, $output, $ret);
        if ($ret !== 0) {
            $this->debug(join("\n", $output) . "\n");
            throw new WStartException(sprintf("Error executing '%s'.", $cmd));
        }
        $this->verbose(1, sprintf("[+] Done.\n"));
    }
    /**
     * @throws WStartException
     */
    public function configureDbConnect()
    {
        $this->verbose(1, sprintf("[+] Configuring CORE_DBCONNECT.\n"));
        require_once sprintf('%s/WHAT/Lib.Common.php', $this->contextRoot);
        $CORE_DBCONNECT = getParam('CORE_DBCONNECT');
        if ($CORE_DBCONNECT == 'persistent') {
            $this->sedFile($this->absolutize('WHAT/Lib.Common.php') , function ($content)
            {
                return preg_replace('/\bpg_connect\b/', 'pg_pconnect', $content);
            });
        } else {
            $this->sedFile($this->absolutize('WHAT/Lib.Common.php') , function ($content)
            {
                return preg_replace('/\bpg_pconnect\b/', 'pg_connect', $content);
            });
        }
        $this->verbose(1, sprintf("[+] Done.\n"));
    }
    /**
     * @throws WStartException
     */
    public function style()
    {
        $this->verbose(1, sprintf("[+] Recomputing style assets.\n"));
        $cmd = sprintf("%s/wsh.php --api=setStyle 2>&1", escapeshellarg($this->contextRoot));
        exec($cmd, $output, $ret);
        if ($ret !== 0) {
            $this->debug(join("\n", $output) . "\n");
            throw new WStartException(sprintf("Error executing '%s'.", $cmd));
        }
        $this->verbose(1, sprintf("[+] Done.\n"));
    }
    /**
     * @throws WStartException
     */
    public function unStop()
    {
        $this->verbose(1, sprintf("[+] Removing maintenance mode.\n"));
        $maintenanceFile = $this->absolutize('maintenance.lock');
        if (is_file($maintenanceFile)) {
            if (unlink($maintenanceFile) === false) {
                throw new WStartException(sprintf("Error removing file '%s'.", $maintenanceFile));
            }
        }
        $this->verbose(1, sprintf("[+] Done.\n"));
    }
    /**
     * @throws WStartException
     * @throws \Dcp\Db\Exception
     */
    public function reapplyDatabaseParameters()
    {
        require_once 'WHAT/Lib.Common.php';
        require_once 'WHAT/autoload.php';
        
        $this->verbose(1, sprintf("[+] Reapplying database parameters.\n"));
        if (($err = simpleQuery('', 'SELECT current_database()', $dbName, true, true, false)) !== '') {
            throw new WStartException(sprintf("Error getting current database name: %s", $err));
        }
        $paramList = array(
            'DateStyle' => 'ISO, DMY',
            'standard_conforming_strings' => 'off'
        );
        foreach ($paramList as $paramName => $paramValue) {
            $sql = sprintf("ALTER DATABASE %s SET %s = %s", pg_escape_identifier($dbName) , pg_escape_identifier($paramName) , pg_escape_literal($paramValue));
            if (($err = simpleQuery('', $sql, $res, true, true, false)) !== '') {
                throw new WStartException(sprintf("Error setting '%s' = '%s' on database '%s': %s", $paramName, $paramValue, $dbName, $err));
            }
        }
        $this->verbose(1, sprintf("[+] Done.\n"));
    }
}
