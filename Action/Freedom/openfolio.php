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

	if (!$folio->isAffected()) {
		$action->exitError(sprintf(_("document %s not exists"), $docid));
	}
	$action->lay->Set("dirid", $folio->initid);
	$action->lay->Set("docid", $docid);
	$action->lay->Set("title", $folio->title);

	$dir = new_Doc($dbaccess, $folio->initid);
	$savedtab = $dir->getParamValue('pfl_savetab');
	$pfctx = portfolio_get_context($dir);
	
	$action->lay->set("FRAMELISTWIDTH", '180');
	$action->lay->set("VIEWSTATE", 2);
	$action->lay->set("TABSELECTED", '');
	$action->lay->set("FRAME1", '&app=FDL&action=FDL_CARD&latest=Y&id='.$folio->initid);
	$action->lay->set("FRAME2", '&app=FREEDOM&action=FREEDOM_LOGO');
	$action->lay->set("FRAMELIST", '&app=FREEDOM&action=FOLIOLIST&dirid='.$folio->initid);

	if(isset($pfctx['framelistwidth'])) {
		$action->lay->set("FRAMELISTWIDTH", $pfctx['framelistwidth']);
	}

	if(isset($pfctx['viewstate'])) {
		$action->lay->set("VIEWSTATE", $pfctx['viewstate']);
	}
	
	if($savedtab) {
		if(isset($pfctx['tabselected'])) {
			$action->lay->set("TABSELECTED", $pfctx['tabselected']);
		}
		if(isset($pfctx['framelist'])) {
			$action->lay->set("FRAMELIST", '&app=FREEDOM&action=FOLIOLIST&dirid='.$pfctx['framelist']['dirid'].'&folioid='.$pfctx['framelist']['folioid']);
		}
	}
	if(isset($pfctx['frame1'])) {
		$doc1 = new_Doc($dbaccess, $pfctx['frame1']);
		if($doc1->isAlive()) {
			$action->lay->set("FRAME1", '&app=FDL&action=FDL_CARD&latest=Y&id='.$pfctx['frame1']);
		}
	}
	if(isset($pfctx['frame2'])) {
		$doc2 = new_Doc($dbaccess, $pfctx['frame2']);
		if($doc2->isAlive()) {
			$action->lay->set("FRAME2", '&app=FDL&action=FDL_CARD&latest=Y&id='.$pfctx['frame2']);
		}
	}
}

?>
