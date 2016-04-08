<?php
/*
 * @author Anakeen
 * @package FDL
*/

include_once "FDL/freedom_util.php";

function get_enum_label(Action & $action)
{
    $keys = str_replace("\r\n", "\n", $action->getArgument("keys"));
    $famid = $action->getArgument("famid");
    $attrid = $action->getArgument("attrid");
    
    $alabels = array();
    $return = array(
        "success" => true,
        "error" => "",
        "data" => ""
    );
    
    $famdoc = new_Doc($action->dbaccess, $famid);
    if ($famdoc->isAlive()) {
        /**
         * @var NormalAttribute $attr
         */
        $attr = $famdoc->getAttribute($attrid);
        $alabels = $attr->getEnumLabel($keys);
    } else {
        $return = array(
            "success" => false,
            "error" => sprintf(_("Document %s is not alive") , $famid) ,
            "data" => ""
        );
    }
    if (!empty($alabels)) {
        $return["data"] = $alabels;
    }
    $action->lay->template = json_encode($return);
    $action->lay->noparse = true;
    header('Content-type: application/json');
}
