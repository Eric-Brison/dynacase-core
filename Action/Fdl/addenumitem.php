<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * add item and return html input of an attribute
 *
 * @author Anakeen
 * @version $Id: addenumitem.php,v 1.2 2008/12/11 10:06:52 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/editutil.php");
/**
 * Display editor to fix a document version
 * @param Action &$action current action
 * @global docid int Http var : document id
 * @global aid int Http var : attribute id
 */
function addenumitem(Action & $action)
{
    $docid = $action->getArgument("docid");
    $attrid = $action->getArgument("aid");
    $key = $action->getArgument("key");
    $index = $action->getArgument("index");
    
    $key = trim(str_replace('"', '', $key));
    $dbaccess = $action->dbaccess;
    
    $action->lay->noparse = true;
    $action->lay->template = htmlspecialchars("addenumitem '$docid' '$attrid' '$key'", ENT_QUOTES);
    $doc = new_doc($dbaccess, $docid);
    if ($doc->isAlive()) {
        $action->lay->noparse = true;
        $action->lay->template = htmlspecialchars("addenumitem/2 '$docid' '$attrid' '$key'", ENT_QUOTES);
        /**
         * @var NormalAttribute $oa
         */
        $oa = $doc->getAttribute($attrid);
        if ($oa) {
            $err = $oa->addEnum($dbaccess, str_replace('.', '\.', $key) , $key);
            if ($oa->repeat && (!$oa->inArray())) {
                $v = $doc->getRawValue($oa->id);
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
