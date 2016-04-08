<?php
/*
 * @author Anakeen
 * @package FDL
*/

class InstallUtils
{
    
    public static function replace_usage()
    {
        return <<<'EOF'

Usage:

    [-f <file1>] ... [-f <fileN>] [+e[pcre_modifier]] <key1> <value1> [-e] [+s] <key2> <value2> [-s] ...

    -f
        file to modify
    +e[pcre_modifier]
        activate regex matching for keys with optional PCRE modifiers
    -e
        disable regex matching (plain matching)(default)
    +s
        activate safe php string replacement
    -s
        disable safe php string replacement
    +q
        when using +s, add apostrophe around the string (default)
    -q
        when using +s, do not add apostrophe around the string
    -R
        reset flags to defaults


EOF;
        
        
    }
    public static function replace($argv)
    {
        $vars = array();
        $files = array();
        /* flags */
        $regex = false;
        $php_string = false;
        $quote = true;
        $end = false;
        while (count($argv) > 0) {
            $opt = array_shift($argv);
            if (!$end && $opt == '-R') {
                /* Reset flags to defaults */
                $regex = false;
                $php_string = false;
                $quote = true;
                $end = false;
            } elseif (!$end && $opt == '+s') {
                $php_string = true;
            } elseif (!$end && $opt == '-s') {
                $php_string = false;
            } elseif (!$end && $opt == '+q') {
                $quote = true;
            } elseif (!$end && $opt == '-q') {
                $quote = false;
            } elseif (!$end && substr($opt, 0, 2) == '+e') {
                $regex = substr($opt, 2);
                if ($regex === false) {
                    $regex = '';
                }
            } elseif (!$end && $opt == '-e') {
                $regex = false;
            } else if (!$end && $opt == '--') {
                $end = true;
            } elseif (!$end && $opt == '-f') {
                $file = array_shift($argv);
                if ($file === null) {
                    throw new Exception(sprintf("Missing file after '-f' option.\n%s", self::replace_usage()));
                }
                $files[] = $file;
            } else {
                $value = array_shift($argv);
                if ($value === null) {
                    throw new Exception(sprintf("Missing value for variable '%s'.\n%s", $opt, self::replace_usage()));
                }
                $vars[] = array(
                    'key' => $opt,
                    'value' => $value,
                    'regex' => $regex,
                    'php_string' => $php_string,
                    'quote' => $quote
                );
            }
        }
        foreach ($files as $file) {
            $content = file_get_contents($file);
            if ($content === false) {
                throw new Exception(sprintf("Error reading from file '%s'.", $file));
            }
            foreach ($vars as $var) {
                $modifier = '';
                if ($var['regex'] === false) {
                    $var['key'] = preg_quote($var['key'], "/");
                } else {
                    $modifier = $var['regex'];
                }
                if ($var['php_string']) {
                    $var['value'] = var_export($var['value'], true);
                    if (!$var['quote']) {
                        $var['value'] = substr($var['value'], 1, -1);
                    }
                }
                
                $content = preg_replace('/' . $var['key'] . '/' . $modifier, $var['value'], $content);
                if ($content === false) {
                    throw new Exception(sprintf("Error in regex '/%s/'.", $var['key']));
                }
            }
            if (file_put_contents($file, $content) === false) {
                throw new Exception(sprintf("Error writing to file '%s'.", $file));
            }
        }
        return 0;
    }
    public static function doublequote_usage()
    {
        return <<<'EOF'

Usage:

    [<string1>] [-b] [<string2>] [<string3>] +b [<stringN>]

Returns the string escaped and enclosed between quotes (").

    +b
        Enable backslash escaping (default)
    -b
        Disable backslash escaping
    +q
        Enable surrounding double-quotes (")(default)
    -q
        Disable surrounding double-quotes (")


EOF;
        
        
    }
    public static function doublequote($argv)
    {
        $strings = array();
        $backslash = true;
        $quote = true;
        $end = false;
        while (count($argv) > 0) {
            $opt = array_shift($argv);
            if (!$end && $opt == '+b') {
                $backslash = true;
            } elseif (!$end && $opt == '-b') {
                $backslash = false;
            } elseif (!$end && $opt == '+q') {
                $quote = true;
            } elseif (!$end && $opt == '-q') {
                $quote = false;
            } else if (!$end && $opt == '--') {
                $end = true;
            } else {
                $strings[] = array(
                    'value' => $opt,
                    'backslash' => $backslash,
                    'quote' => $quote
                );
            }
        }
        foreach ($strings as $string) {
            if ($string['backslash']) {
                $string['value'] = str_replace(array(
                    "\""
                ) , array(
                    "\\\""
                ) , $string['value']);
            }
            $string['value'] = str_replace(array(
                "\""
            ) , array(
                "\\\""
            ) , $string['value']);
            if ($string['quote']) {
                $string['value'] = '"' . $string['value'] . '"';
            }
            print ($string['value'] . PHP_EOL);
        }
    }
    public static function pg_escape_string_usage()
    {
        return <<<'EOF'

Usage:

    [<string1>]

Returns the string escaped for inclusion between apostrophes.

EOF;
        
        
    }
    public static function pg_escape_string($argv)
    {
        $strings = array();
        $end = false;
        while (count($argv) > 0) {
            $opt = array_shift($argv);
            if (!$end && $opt == '--') {
                $end = true;
            } else {
                $strings[] = array(
                    'value' => $opt,
                );
            }
        }
        foreach ($strings as $string) {
            printf('%s' . PHP_EOL, pg_escape_string($string['value']));
        }
    }
    
    public static function _call($argv)
    {
        $function = array_shift($argv);
        if ($function === null) {
            throw new Exception(sprintf("Missing function name."));
        }
        if (!method_exists(__CLASS__, $function)) {
            throw new Exception(sprintf("Function '%s' does not exists.", $function));
        }
        $function = __CLASS__ . '::' . $function;
        return call_user_func($function, $argv);
    }
}

if (basename(__FILE__) == basename($argv[0])) {
    array_shift($argv);
    $ret = InstallUtils::_call($argv);
    exit(($ret === false) ? 255 : $ret);
}
