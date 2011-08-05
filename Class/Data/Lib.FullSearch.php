<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 * Full Text Search document
 *
 * @author Anakeen 2009
 * @version $Id:  $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package API
 */
/**
 */

include_once ("FGSEARCH/fullsearch.php");

function getHighlight(&$doc, $keys)
{
    
    $htext = nl2br(str_replace(array(
        '[b]',
        '[/b]'
    ) , array(
        '<b>',
        '</b>'
    ) , (str_replace("<", "&lt;", preg_replace("/<\/?(\w+[^:]?|\w+\s.*?)>/", "", str_replace(array(
        '<b>',
        '</b>'
    ) , array(
        '[b]',
        '[/b]'
    ) , 
    nl2br(wordwrap(nobr(highlight_text($doc->dbid, $doc->svalues, $keys) , 80)))))))));
    
    return $htext;
}
?>
