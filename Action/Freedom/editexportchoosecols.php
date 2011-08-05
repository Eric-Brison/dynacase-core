<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Export edition
 *
 * @author Anakeen 2007
 * @version $Id: editexportchoosecols.php,v 1.2 2008/11/14 12:41:43 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
/**
 */

include_once ("FDL/Class.Doc.php");
include_once ("FDL/Lib.Dir.php");
include_once ("GENERIC/generic_util.php");

function editexportchoosecols(&$action)
{
    $dbaccess = $action->GetParam("FREEDOM_DB");
    $docid = GetHttpVars("id");
    
    $doc = new_doc($dbaccess, $docid);
    if (!$doc->isAlive()) $action->exitError(sprintf(_("family %s not exists") , $docid));
    
    $toa = $doc->getNormalAttributes();
    $tattr = array();
    $pref = getFamilyParameter($action, $doc->id, "FREEDOM_EXPORTCOLS");
    $tpref = explode(";", $pref);
    
    $before = false;
    $beforetab = false;
    foreach ($toa as $k => $oa) {
        if ($oa->type == "array") continue;
        $newframe = false;
        $newtab = false;
        if (($before != $oa->fieldSet->id) && ($before != $oa->fieldSet->type = 'frame')) {
            $newframe = ($before != $oa->fieldSet->id);
            $framename = $oa->fieldSet->getLabel();
        }
        if ($oa->fieldSet->fieldSet && ($oa->fieldSet->fieldSet->type == 'tab')) {
            $newtab = ($beforetab != $oa->fieldSet->fieldSet->id);
            $beforetab = $oa->fieldSet->fieldSet->id;
            $tabname = $oa->fieldSet->fieldSet->getLabel();
        }
        
        $tattr[] = array(
            "attrid" => $oa->id,
            "newframe" => $newframe,
            "newtab" => $newtab,
            "need" => $oa->needed,
            "hidden" => $oa->visibility == 'H',
            "selected" => in_array($oa->id, $tpref) ,
            "tabname" => $tabname,
            "framename" => $framename,
            "attrname" => $oa->getLabel()
        );
        $before = $oa->fieldSet->id;
    }
    
    $action->lay->setBlockData("ATTRS", $tattr);
    $action->lay->set("famid", $doc->id);
    $action->lay->set("famtitle", $doc->getTitle());
    $action->lay->set("famicon", $doc->getIcon());
}
?>