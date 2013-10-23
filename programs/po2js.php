#!/usr/bin/env php
<?php
/**
 * translate mo file to json
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
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
            if ($this->encoding === "iso") {
                $js = utf8_encode($js);
            }
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
            if ($pocontent !== false) {
                $pocontent .= "\n\n";
                preg_match_all('/^msgid (?P<msgid>".*?)msgstr (?P<msgstr>".*?")\n\n/ms', $pocontent, $matches, PREG_SET_ORDER);
                foreach ($matches as $m) {
                    $this->memoEntry($m['msgid'], $m['msgstr']);
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
     * @param $key
     * @param $text
     */
    protected function memoEntry($key, $text)
    {
        $tkey = explode("\n", $key);
        $ttext = explode("\n", "$text");
        $key = trim(implode("\n", array_map('Po2js::trimquote', $tkey)));
        $text = trim(implode("\n", array_map('Po2js::trimquote', $ttext)));
        if ($key && $text) {
            $this->entries[$key] = $text;
        } else if ($key == "") {
            if (stristr($text, "charset=ISO-8859") !== false) {
                $this->encoding = 'iso';
            }
        }
    }

    protected static function trimquote($s)
    {
        return trim($s, '"');
    }

}