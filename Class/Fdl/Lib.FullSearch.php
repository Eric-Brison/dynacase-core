<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Full Text Search document
 *
 * @author Anakeen
 * @version $Id:  $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
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
    
    simpleQuery($doc->dbaccess, sprintf("select search.document.svalues from docread, search.documents where docread.id=%d and  docread.id=search.documents.id", $doc->id) , $text, true, true);
    $h = $oh->highlight($text, $keys);
    $htext = nl2br($h);
    
    return $htext;
}
?>
