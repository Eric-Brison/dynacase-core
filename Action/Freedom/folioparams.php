<?php

/**
 * 
 *
 * @author Anakeen 2003
 * @version $Id: foliolist.php,v 1.16 2007/10/19 15:20:14 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage GED
 */
/**
 */
include_once('FREEDOM/freedom_view.php');
include_once('FREEDOM/Lib.portfolio.php');

/**
 * 
 * @param Action &$action current action
 * @global dirid Http var : separator identificator to see
 * @global folioid Http var : portfolio of separator
 */
function folioparams(&$action) {

	$docid = GetHttpVars("id", 0);		// document to edit
	$dbaccess = $action->GetParam("FREEDOM_DB");
	
	$folio = new_Doc($dbaccess, $docid);

	if (!$folio->isAffected()) {
		$action->exitError(sprintf(_("document %s not exists"), $docid));
	}
	else {

		$dir = new_Doc($dbaccess, $folio->initid);
		$pfctx = portfolio_get_context($dir);

		$var = GetHttpVars("viewstate", -1);
		if(is_numeric($var) && $var >= 0 && $var <= 4) {
			$pfctx['viewstate'] = $var;
		}

		$var = GetHttpVars("framelist", '');
		if(!empty($var) && preg_match('/^([0-9]+),([0-9]+)$/i', $var, $matches)) {
			$pfctx['framelist'] = array(
				'dirid' => $matches[1],
				'folioid' => $matches[2],
			);
		}

		foreach(array('tabselected', 'frame1', 'frame2', 'framelistwidth') as $param) {
			$var = GetHttpVars($param, '');
			if(!empty($var) && is_numeric($var)) {
				//error_log("SET $param => $var");
				if($pfctx['viewstate'] == 0 && in_array($param, array('frame1', 'frame2'))) {
					$pfctx['frame1'] = $var;
					$pfctx['frame2'] = $var;
				}
				else {
					$pfctx[$param] = $var;
				}
			}
		}

		//foreach(explode("\n", print_r($pfctx, true)) as $tmp ) {error_log($tmp);}

		portfolio_set_context($dir, $pfctx);
	}
}

?>
