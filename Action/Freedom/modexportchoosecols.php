<?php
/**
 * Save export preferences
 *
 * @author Anakeen 2007
 * @version $Id: modexportchoosecols.php,v 1.2 2008/11/14 12:41:43 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
 /**
 */

include_once("FDL/Class.Doc.php");
include_once("FDL/Lib.Dir.php");
include_once("GENERIC/generic_util.php");

function modexportchoosecols(&$action) {
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $docid = GetHttpVars("id");
  $cols = GetHttpVars("ch");
  $clear = (GetHttpVars("clear")=="yes");

  
  $doc= new_doc($dbaccess,$docid);
  if (!$doc->isAlive()) $action->exitError(sprintf(_("family %s not exists"),$docid));

  if ($cols=="") $scols=array();
  else $scols=implode(';',$cols);

  if ($clear) {
    deleteFamilyParameter($action,$doc->id,"FREEDOM_EXPORTCOLS",$scols);
    $action->addWarningMsg(_("export preferences has been cleared"));
  } else {
    setFamilyParameter($action,$doc->id,"FREEDOM_EXPORTCOLS",$scols);
    $action->addWarningMsg(_("export preferences are saved"));
  }
  redirect($action,"FREEDOM",
	   "EDITEXPORTCHOOSECOLS&id=".$doc->id,
	   $action->GetParam("CORE_STANDURL"));
  
}