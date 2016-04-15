<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Utils;

require_once __DIR__ . '/Class.Dcp_Utils_WStart.php';

class WStartCLIException extends \Exception
{
}

class WStartCLI implements WStartStdioInterface
{
    public static function usage($me)
    {
        print <<<EOF

Usage
-----

    $me [<options>] [--all|<operations>]

Options:

    -v|--verbose            Increase verbosity (can be specified multiple times to increase verbosity).

Operations:

    -r|--resetAutoloader    Re-generate class autoloader.
    -l|--links              Re-generate Images and Docs symlinks.
    -c|--clearFile          Clear cached content.
    -u|--upgradeVersion     Increment WVERSION.
    -b|--dbconnect          Configure CORE_DBCONNECT method
    -s|--style              Recompute style assets.
    -m|--unStop             Remove maintenance mode.


EOF;
        
        
    }
    /**
     * Initialize context environment
     *
     * @return string
     * @throws WStartCLIException
     */
    private static function bootstrap()
    {
        $contextRoot = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..');
        if ($contextRoot === false) {
            throw new WStartCLIException(sprintf("Could not get context root directory from directory '%s'!", __DIR__));
        }
        if (chdir($contextRoot) === false) {
            throw new WStartCLIException(sprintf("Could not change current working directory to '%s'!", $contextRoot));
        }
        set_include_path($contextRoot . PATH_SEPARATOR . sprintf('%s/WHAT', $contextRoot) . PATH_SEPARATOR . get_include_path());
        require_once sprintf('%s/WHAT/Lib.Prefix.php', $contextRoot);
        require_once sprintf('%s/WHAT/Class.Dcp_Utils_WStart.php', $contextRoot);
        return $contextRoot;
    }
    /**
     * Main CLI interface
     *
     * @param $argv
     * @throws WStartCLIException
     */
    public static function run(&$argv)
    {
        $me = array_shift($argv);
        if ($me === null) {
            throw new WStartCLIException(sprintf("Undefined argument #0!"));
        }
        $contextRoot = self::bootstrap();
        /*
         * Default builtin getopt() function does not report unknown options, which I find rather annoying.
         * And it treats short and long options as distinct options, which I also find rather annoying.
         * So, I'd better craft my own getopt().
        */
        $opts = array();
        while (count($argv) > 0) {
            $opt = array_shift($argv);
            if ($opt == '--') {
                break;
            }
            switch ($opt) {
                case '-h':
                case '--help':
                    $opts['help'] = true;
                    break;

                case '-v':
                case '--verbose':
                    if (!isset($opts['verbose'])) {
                        $opts['verbose'] = 0;
                    }
                    $opts['verbose']++;
                    break;

                case '-a':
                case '--all':
                    $opts['all'] = true;
                    break;

                case '-r':
                case '--resetAutoloader':
                    $opts['resetAutoloader'] = true;
                    break;

                case '-l':
                case '--links':
                    $opts['links'] = true;
                    break;

                case '-c':
                case '--clearFile':
                    $opts['clearFile'] = true;
                    break;

                case '-u':
                case '--upgradeVersion':
                    $opts['upgradeVersion'] = true;
                    break;

                case '-b':
                case '--dbconnect':
                    $opts['dbconnect'] = true;
                    break;

                case '-s':
                case '--style':
                    $opts['style'] = true;
                    break;

                case '-m':
                case '--unStop':
                    $opts['unStop'] = true;
                    break;

                default:
                    printf("ERROR: Unknown argument/option '%s'!\n", $opt);
                    self::usage($me);
                    exit(1);
                    break;
            }
        }
        if (isset($opts['help'])) {
            self::usage($me);
            exit(0);
        }
        /*
         * Set operations that need to be executed.
        */
        $operations = array(
            'reapplyDatabaseParameters' => true, /* Always reapply database parameters before anything else */
            'clearAutoloadCache' => false,
            'imageAndDocsLinks' => false,
            'clearFileCache' => false,
            'refreshJsVersion' => false,
            'configureDbConnect' => false,
            'style' => false,
            'unStop' => false
        );
        $all = true;
        if (isset($opts['resetAutoloader'])) {
            $operations['clearAutoloadCache'] = true;
            $all = false;
        }
        if (isset($opts['links'])) {
            $operations['imageAndDocsLinks'] = true;
            $all = false;
        }
        if (isset($opts['clearFile'])) {
            $operations['clearFileCache'] = true;
            $all = false;
        }
        if (isset($opts['upgradeVersion'])) {
            $operations['refreshJsVersion'] = true;
            $all = false;
        }
        if (isset($opts['dbconnect'])) {
            $operations['configureDbConnect'] = true;
            $all = false;
        }
        if (isset($opts['style'])) {
            $operations['style'] = true;
            $all = false;
        }
        if (isset($opts['unStop'])) {
            $operations['unStop'] = true;
            $all = false;
        }
        /*
         * If $all remains true, then set all operations for execution
        */
        if ($all) {
            foreach ($operations as $name => & $needExec) {
                $needExec = true;
            }
            unset($needExec);
        }
        $wstart = new WStart($contextRoot);
        $wstart->setStdio(new WStartCLI());
        if (isset($opts['verbose'])) {
            $wstart->setVerbose($opts['verbose']);
        }
        foreach ($operations as $name => $needExec) {
            if (!$needExec) {
                continue;
            }
            if (!method_exists($wstart, $name)) {
                throw new WStartCLIException(sprintf("Unknown operation '%s'!", $name));
            }
            if (call_user_func_array(array(
                $wstart,
                $name
            ) , array()) === false) {
                throw new WStartCLIException(sprintf("Execution of '%s' returned with error!", $name));
            }
        }
        exit(0);
    }
    /**
     * Wstart's stdout I/O interface
     * @param $msg
     */
    public function wstart_stdout($msg)
    {
        fputs(STDOUT, $msg);
    }
    /**
     * Wstart's stderr I/O interface
     * @param $msg
     */
    public function wstart_stderr($msg)
    {
        if (mb_substr($msg, -1) != "\n") {
            $msg.= "\n";
        }
        fputs(STDERR, $msg);
    }
}
