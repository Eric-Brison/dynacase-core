<?php
/**
 * Edition to affect document
 *
 * @author Anakeen 2011
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

/**
 * All edit css stylesheets in one single stylesheets
 * @param Action &$action current action
 */
function alleditcss(Action &$action) {
	$jurl= "WHAT/Layout";
	
	$static_css = array();
	$dynamic_css = array();

	$jsCalendarCssFile = $action->getLayoutFile('calendar.css');
	if('' == $jsCalendarCssFile){
		$jsCalendarCssFile = sprintf("jscalendar/Layout/calendar-win2k-2.css");
		$static_css[] = $jsCalendarCssFile;
	} else {
		$dynamic_css[] = $jsCalendarCssFile;
	}

	$dynamic_css[] = "CORE/Layout/core.css";
	$dynamic_css[] = "FDL/Layout/freedom.css";
	$dynamic_css[] = "FDL/Layout/editdoc.css";
	$dynamic_css[] = "FDL/Layout/autocompletion.css";
	$dynamic_css[] = "FDL/Layout/popup.css";

	setHeaderCache("text/css");
	$action->lay->template="";

	RessourcePacker::pack_css($action, $static_css, $dynamic_css);
}

?>