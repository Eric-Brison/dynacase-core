#!/usr/bin/env php
<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * translate mo file to json
 * @author Anakeen
 */

$c = new Po2js($argv[1]);
print $c->po2json();
/**
 * Convert a PO file to json string
 */
Class Po2js
{
    
    protected $pofile = "";
    protected $entries = array();
    protected $encoding = 'utf-8';
    /**
     * Construct the object
     *
     * @param string $pofile path to the po file
     * @throws Exception
     */
    public function __construct($pofile)
    {
        if (file_exists($pofile)) {
            $this->pofile = $pofile;
        } else {
            throw new Exception("PO file ($pofile) doesn't exist.");
        }
    }
    /**
     * Convert the current PO file to a json string
     *
     * JSON contains an object where key are po key and content po translation
     * if there is no translation return an empty string
     *
     * @return string
     */
    public function po2json()
    {
        $this->po2array();
        if (!empty($this->entries)) {
            $js = json_encode($this->entries);
            return $js;
        } else {
            return "";
        }
    }
    /**
     * Extract PO entries an store them
     *
     * @throws Exception
     */
    protected function po2array()
    {
        if (file_exists($this->pofile)) {
            $pocontent = file_get_contents($this->pofile);
            if ($this->encoding === 'iso') {
                $pocontent = utf8_encode($pocontent);
            }
            if ($pocontent !== false) {
                $pocontent.= "\n\n";
                preg_match_all('/
                        ( ^msgctxt \s+ (?P<msgctxt> ".*" \s* (^ ".*" \s*)* ) )?
                        \s*
                        ^msgid     \s+ (?P<msgid>   ".*" \s* (^ ".*" \s*)* )
                        \s*
                        ^msgstr    \s+ (?P<msgstr>  ".*" \s* (^ ".*" \s*)* )
                        \s*
                    /xmu', $pocontent, $matches, PREG_SET_ORDER);
                foreach ($matches as $m) {
                    $this->memoEntry($m['msgid'], $m['msgstr'], $m['msgctxt']);
                }
            } else {
                throw new Exception("PO file ({$this->pofile}) is not readable.");
            }
        } else {
            throw new Exception("PO file ({$this->pofile}) doesn't exist.");
        }
    }
    /**
     * Clean a key and a translation and add them to $this->entries
     * @param string $key text to translate
     * @param string $text translation
     * @param string $ctxt context
     */
    protected function memoEntry($key, $text, $ctxt = '')
    {
        $key = $this->mergeMsgLines($key);
        $text = $this->mergeMsgLines($text);
        $ctxt = $this->mergeMsgLines($ctxt);
        
        if ($key && $text) {
            if ($ctxt) {
                $this->entries["_msgctxt_"][$ctxt][$key] = $text;
            } else {
                $this->entries[$key] = $text;
            }
        } else if ($key == "") {
            if (stristr($text, "charset=ISO-8859") !== false) {
                $this->encoding = 'iso';
            }
        }
    }
    /**
     * Convert multiples quoted msg lines into a single unquoted line:
     *
     *     ["foo..."\n"bar..."\n"baz..."] => [foo...bar...baz...]
     *
     * @param $lines
     * @return string
     */
    protected function mergeMsgLines($lines)
    {
        /* Extract lines */
        $lines = preg_split('/\s*\n+\s*/u', $lines);
        /* Remove empty lines */
        $lines = array_filter($lines, function ($v)
        {
            return ($v !== '');
        });
        /*
         * Strip leading and trailing quotes from lines
         * and unescape gettext's control sequences.
        */
        foreach ($lines as & $line) {
            /* Remove leading and trailing quote */
            $line = preg_replace('/^\s*"(.*)"\s*$/u', '\1', $line);
            /* Unescape Getext's control sequences */
            $line = $this->unescapeGettextControlSeq($line);
        }
        unset($line);
        /*
         * Merge back into single line
        */
        return implode("", $lines);
    }
    /**
     * Unescape gettext's control sequence according to
     * http://git.savannah.gnu.org/cgit/gettext.git/tree/gettext-tools/src/po-lex.c#n750
     *
     * @param $str
     * @return string
     */
    protected function unescapeGettextControlSeq($str)
    {
        $str = preg_replace_callback('/\x5c([ntbrfva"\x5c]|[0-7]{1,3}|x[0-9a-fA-F][0-9a-fA-F])/u', function ($m)
        {
            $seq = $m[1];
            /* Unescape control chars */
            switch ($seq) {
                case 'n':
                    return "\n";
                case 't':
                    return "\t";
                case 'b':
                    return chr(0x08);
                case 'r':
                    return "\r";
                case 'f':
                    return "\f";
                case 'v':
                    return "\v";
                case 'a':
                    return chr(0x07);
                case '"':
                case chr(0x5c):
                    return $seq;
            }
            /* Unescape hexadecimal form */
            if (substr($seq, 0, 1) == 'x') {
                $seq = substr($seq, 1);
                return hex2bin($seq);
            }
            /* Unescape octal form */
            return hex2bin(sprintf("%02s", dechex(octdec($seq))));
        }
        , $str);
        return $str;
    }
}
