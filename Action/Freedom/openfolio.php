<?php

/**
 * Open port folio document
 *
 * @author Anakeen 2000 
 * @version $Id: openfolio.php,v 1.6 2005/06/28 08:37:46 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage GED
 */
/**
 */
include_once("FDL/Class.Doc.php");
include_once('FREEDOM/Lib.portfolio.php');

// -----------------------------------
// -----------------------------------
function openfolio(&$action) {
// -----------------------------------
	// Set the globals elements

	$docid = GetHttpVars("id", 0);		// document to edit
	$dbaccess = $action->GetParam("FREEDOM_DB");

	$folio = new_Doc($dbaccess, $docid);

	if (!$folio->isAffected())
		$action->exitError(sprintf(_("document %s not exists"), $docid));
	$action->lay->Set("dirid", $folio->initid);
	$action->lay->Set("docid", $docid);
	$action->lay->Set("title", $folio->title);

	$dir = new_Doc($dbaccess, $folio->initid);
	$pfctx = portfolio_get_context($dir);
	
	$action->lay->set("FRAMELISTWIDTH", '180');
	$action->lay->set("VIEWSTATE", 2);
	$action->lay->set("TABSELECTED", '');
	$action->lay->set("FRAME1", '');
	$action->lay->set("FRAME2", '');
	$action->lay->set("FRAMELIST", '');
	foreach($pfctx as $key => $value) {
			$action->lay->set(strtoupper($key), $value);
	}
}

?>
