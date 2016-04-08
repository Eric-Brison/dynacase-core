<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Full Text Search document
 *
 * @author Anakeen
 * @version $Id:  $
 * @package FDL
 */
/**
 */

function getHighlight(&$doc, $keys)
{
    static $oh = null;
    if (!$oh) {
        $oh = new SearchHighlight();
    }
    
    simpleQuery($doc->dbaccess, sprintf("select svalues from docread where id=%d", $doc->id) , $text, true, true);
    $h = $oh->highlight($text, $keys);
    $htext = nl2br($h);
    
    return $htext;
}
?>
