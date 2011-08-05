<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Relation Navigation
 *
 * @author Anakeen 2005
 * @version $Id: rnavigate.php,v 1.7 2008/06/03 16:31:53 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
/**
 */

include_once ("FDL/Class.Doc.php");
include_once ("FDL/Lib.Dir.php");
include_once ("FDL/Class.DocRel.php");

function rnavigate(&$action)
{
    $dbaccess = $action->GetParam("FREEDOM_DB");
    $docid = GetHttpVars("id");
    $action->parent->AddJsRef($action->GetParam("CORE_PUBURL") . "/FDL/Layout/common.js");
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/subwindow.js");
    $limit = 100;
    
    $doc = new_Doc($dbaccess, $docid);
    if ($doc->locked == - 1) $doc = new_Doc($dbaccess, $doc->LatestId());
    $idocid = $doc->initid;
    
    $rdoc = new DocRel($dbaccess, $idocid);
    $rdoc->sinitid = $idocid;
    
    $action->lay->set("Title", $doc->title);
    $tlay = array();
    
    $trel = $rdoc->getIRelations("", "", $limit);
    if (count($trel) == 0) {
        $tlay['_F'] = array(
            "iconsrc" => "",
            "initid" => 0,
            "title" => "",
            "aid" => "",
            "alabel" => "",
            "type" => _("Referenced from")
        );
    }
    foreach ($trel as $k => $v) {
        $tlay[$v["sinitid"] . '_F'] = array(
            "iconsrc" => $doc->getIcon($v["sicon"]) ,
            "initid" => $v["sinitid"],
            "title" => $v["stitle"],
            "aid" => $v["type"],
            "alabel" => $v["type"] ? _($v["type"]) : "",
            "type" => _("Referenced from")
        );
    }
    
    $trel = $rdoc->getRelations("", "", $limit);
    if (count($trel) == 0) {
        $tlay['_T'] = array(
            "iconsrc" => "",
            "initid" => 0,
            "title" => "",
            "aid" => "",
            "alabel" => "",
            "type" => _("Reference")
        );
    }
    foreach ($trel as $k => $v) {
        $tlay[$v["cinitid"] . '_T'] = array(
            "iconsrc" => $doc->getIcon($v["cicon"]) ,
            "initid" => $v["cinitid"],
            "title" => $v["ctitle"],
            "aid" => $v["type"],
            "alabel" => $v["type"] ? _($v["type"]) : "",
            "type" => _("Reference")
        );
    }
    
    if (count($tlay) > 0) {
        foreach ($tlay as $k => $v) {
            $taid[$v["aid"]] = $v["aid"];
        }
        $q = new QueryDb($dbaccess, "DocAttr");
        $q->AddQuery(GetSqlCond($taid, "id"));
        $l = $q->Query(0, 0, "TABLE");
        if ($l) {
            $la = array();
            foreach ($l as $k => $v) {
                $la[$v["id"]] = $v["labeltext"];
            }
            foreach ($tlay as $k => $v) {
                if ($la[$v["aid"]]) $tlay[$k]["alabel"] = $la[$v["aid"]];
                else if ($tlay[$k]["aid"] == 'folder') $tlay[$k]["alabel"] = _("folder");
            }
        }
    }
    // Verify visibility for current user
    $tids = array();
    foreach ($tlay as $k => $v) {
        $tids[] = $v["initid"];
    }
    $vdoc = getVisibleDocsFromIds($dbaccess, $tids, $action->user->id);
    
    $tids = array();
    if (is_array($vdoc)) foreach ($vdoc as $k => $v) $tids[] = $v["initid"];
    
    foreach ($tlay as $k => $v) {
        if ((!in_array($v["initid"], $tids)) && ($v["initid"] != 0)) unset($tlay[$k]);
    }
    
    $action->lay->setBlockData("RELS", $tlay);
    $action->lay->set("docid", $docid);
}

function rnavigate2(&$action)
{
    header('Content-type: text/xml; charset=utf-8');
    rnavigate($action);
}
?>