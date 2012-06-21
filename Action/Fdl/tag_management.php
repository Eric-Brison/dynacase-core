<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

include_once ("FDL/Class.Doc.php");
/**
 * @param Action $action
 */
function tag_management(Action & $action)
{
    $listSeparator = array(
        ",",
        ";",
        " "
    );
    $docid = $action->getArgument("id");
    $type = $action->getArgument("type");
    $tags = $action->getArgument("tags");
    $err = "";
    $data = array();
    
    $doc = new_Doc($action->dbaccess, $docid);
    if ($doc->isAlive()) {
        switch ($type) {
            case "add":
                if (trim($tags)) {
                    $listTags = preg_split("/[" . implode("", $listSeparator) . "]/", $tags);
                    foreach ($listTags as $tag) {
                        $tag = trim($tag);
                        if ($tag) {
                            $err.= ($err ? '\n' : '') . $doc->tag()->addTag($tag);
                        }
                    }
                    $data = $doc->tag()->getTagsValue($doc->tag()->getTag());
                }
                break;

            case "getAll":
                $tags = $doc->tag()->getAllTags();
                $dataTmp = array_unique($doc->tag()->getTagsValue($tags));
                foreach ($dataTmp as $tmp) {
                    $data[] = $tmp;
                }
                break;

            case "delete":
                $err = $doc->tag()->delTag($tags);
                $data = $doc->tag()->getTagsValue($doc->tag()->getTag());
                break;
        }
    } else {
        $action->exitError(sprintf(_("tagmanagement: document [%d] is not alive") , $docid));
    }
    //error_log("LIST TAG IS == " . var_export($data, true));
    $out['error'] = $err;
    $out['data'] = $data;
    
    $action->lay->template = json_encode($out);
    $action->lay->noparse = true;
    
    header('Content-type: application/json');
}
