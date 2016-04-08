<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Full Text Search document
 *
 * @author Anakeen
 * @version $Id: fullsearch.php,v 1.10 2008/01/04 17:56:37 eric Exp $
 * @package FDL
 * @subpackage GED
 */
/**
 */

include_once ("FDL/Class.SearchDoc.php");
include_once ("FDL/Class.DocSearch.php");

include_once ("FDL/freedom_util.php");

class SearchHighlight
{
    
    private $dbid;
    /**
     * @var string limit size in Kb
     */
    private $limit = 200;
    
    public $beginTag = '<b>';
    public $endTag = '</b>';
    
    public function __construct()
    {
        $this->dbid = getDbId(getDbAccess());
    }
    
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }
    public static function strtr8($s, $c1, $c2)
    {
        $s9 = utf8_decode($s);
        $s9 = strtr($s9, utf8_decode($c1) , utf8_decode($c2));
        return utf8_encode($s9);
    }
    /**
     * return part of text where are found keywords
     * use simply regexp replace
     * @param string $s original text
     * @param string $k keywords
     * @return string HTML text with <b> tags
     */
    public function rawHighLight($s, $k)
    {
        $offsetStart = 100; // number of characters displayed before and after first result
        $replace = $this->beginTag . '$1' . $this->endTag;
        
        $out = preg_replace("/($k)/iu", $replace, str_replace(array(
            '£',
            $this->beginTag,
            $this->endTag
        ) , array(
            ' - ',
            " ",
            ""
        ) , ($s)));
        $begin = strpos($out, $this->beginTag);
        $end = strpos($out, $this->endTag);
        if ($begin === false) {
            $out = "-";
        } else {
            $begin = strpos($out, " ", max(0, $begin - $offsetStart));
            if ($begin === false) {
                $begin = 0;
            }
            $end = strpos($out, " ", $end + $offsetStart);
            if ($end === false) {
                $end = $begin + 100;
            }
            
            $out = substr($out, $begin, $end - $begin);
        }
        return $out;
    }
    /**
     * return part of text where are found keywords
     * Due to unaccent fulltext vectorisation need to transpose original text with highlight text done by headline tsearch2 sql function
     * @param string $s original text
     * @param string $k keywords
     * @return string HTML text with <b> tags
     */
    public function highlight($s, $k)
    {
        $headline = '';
        $k = trim($k);
        if ($k == "") {
            $h = str_replace('£', ' - ', substr($s, 0, 100));
            $pos1 = mb_strpos($h, ' ');
            $pos2 = mb_strrpos($h, ' ');
            $headline = substr($h, $pos1, ($pos2 - $pos1));
        } else if ((strlen($s) / 1024) > $this->limit) {
            $headline = sprintf(_("document too big (%dKo): no highlight") , (strlen($s) / 1024));
        } else {
            
            $k = preg_replace('/\s+/u', '&', unaccent($k));
            // print_r("\n============\n\tK=$k\n");
            $s = self::strtr8($s, "£", ",");
            $s = preg_replace('/[ ]+ /u', ' ', $s);
            $s = str_replace(array(
                "<br />",
                " \r",
                "\n "
            ) , array(
                '',
                '',
                "\n"
            ) , $s);
            
            $s = preg_replace('/<[a-z][^>]+>/i', '', $s);
            $s = preg_replace('/<\/[a-z]+\s*>/i', '', $s);
            $s = preg_replace('/<[a-z]+\/>/i', '', $s);
            $s = preg_replace('/«/u', '"', $s);
            $s = preg_replace('/»/u', '"', $s);
            $s = preg_replace('/\p{C}/u', '', $s); // delete control characters
            $s = preg_replace('/\p{S}/u', '', $s); // delete symbol characters
            $us = unaccent($s);
            //print_r("\n\tSL".mb_strlen($s).'=='.mb_strlen($us)."\n");
            //print_r("\n\tS=$s\n");
            //print_r("\n\tUS=$us\n");
            $q = sprintf("select ts_headline('french','%s',to_tsquery('french','%s'),'MaxFragments=1,StartSel=%s, StopSel=%s')", pg_escape_string($us) , pg_escape_string($k) , pg_escape_string($this->beginTag) , pg_escape_string($this->endTag));
            $result = pg_query($this->dbid, $q);
            if (pg_numrows($result) > 0) {
                $arr = pg_fetch_array($result, 0, PGSQL_ASSOC);
                $headline = $arr["ts_headline"];
                //print_r("\n\tL=$headline");
                
            }
            
            $pos = mb_strpos($headline, $this->beginTag);
            if ($pos !== false) {
                $sw = (str_replace(array(
                    $this->beginTag,
                    $this->endTag
                ) , array(
                    '',
                    ''
                ) , $headline));
                
                $offset = mb_strpos($us, $sw);
                
                if ($offset === false) return $headline; // case mismatch in characters
                $nh = mb_substr($s, $offset, mb_strlen($sw));
                //print_r("\n\tN=$nh\n");
                //print "\nGOOD : $offset - ".mb_strlen($headline)."========\n";
                // recompose headline with accents
                $bo = mb_strpos($headline, $this->beginTag, 0);
                while ($bo !== false) {
                    $nh = mb_substr($nh, 0, $bo) . $this->beginTag . mb_substr($nh, $bo);
                    $bo = mb_strpos($headline, $this->endTag, $bo);
                    if ($bo) {
                        $nh = mb_substr($nh, 0, $bo) . $this->endTag . mb_substr($nh, $bo);
                        $bo = mb_strpos($headline, $this->beginTag, $bo);
                    }
                }
                $headline = $nh;
            }
        }
        return $headline;
    }
}
?>
