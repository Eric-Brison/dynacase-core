<?php
/**
 * Edition to affect document
 *
 * @author Anakeen 2011
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

include_once("FDL/viewdocjs.php");

/**
 * All edit js scripts in one single file
 * @param Action &$action current action
 */
function allviewjs(Action &$action) {
	$jurl= "WHAT/Layout";
	
	$statics = array(
		"$jurl/AnchorPosition.js",
		"FDL/Layout/common.js",
		"FDC/Layout/inserthtml.js",
		"$jurl/resizeimg.js",
		"FDC/Layout/setparamu.js",
		"WHAT/Layout/DHTMLapi.js",
		"FDL/Layout/iframe.js",
		"FDL/Layout/verifycomputedfiles.js",
		"WHAT/Layout/geometry.js",
		"WHAT/Layout/subwindow.js"
	);

	$dynamics=array(
		"FDL/Layout/viewdoc.js",
	);

	viewdocjs($action);
	$action->lay->template="";

	RessourcePacker::pack_js($action, $statics, $dynamics);
}

?>