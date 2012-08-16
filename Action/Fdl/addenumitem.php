<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * add item and return html input of an attribute
 *
 * @author Anakeen
 * @version $Id: addenumitem.php,v 1.2 2008/12/11 10:06:52 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/editutil.php");
/**
 * Display editor to fix a document version
 * @param Action &$action current action
 * @global docid Http var : document id
 * @global aid Http var : attribute id
 */
function addenumitem(Action & $action)
{
    $docid = $action->getArgument("docid");
    $attrid = $action->getArgument("aid");
    $key = $action->getArgument("key");
    $index = $action->getArgument("index");
    
    $key = trim(str_replace('"', '', $key));
    $dbaccess = $action->GetParam("FREEDOM_DB");
    
    $action->lay->template = "addenumitem $docid $attrid <b>$key</b>";
    $doc = new_doc($dbaccess, $docid);
    if ($doc->isAlive()) {
        $action->lay->template = "addenumitem/2 $docid $attrid <b>$key</b>";
        /**
         * @var NormalAttribute $oa
         */
        $oa = $doc->getAttribute($attrid);
        if ($oa) {
            $err = $oa->addEnum($dbaccess, str_replace('.', '\.', $key) , $key);
            if ($oa->repeat && (!$oa->inArray())) {
                $v = $doc->getValue($oa->id);
                if ($v != "") $v.= "\n$key";
                else $v = $key;
            } else {
                $v = $key;
            }
            
            $i = getHtmlInput($doc, $oa, $v, $index);
            $action->lay->noparse = true;
            $action->lay->template = $i;
        }
    }
}
?>