<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: freedom_view.php,v 1.12 2006/11/22 11:13:30 marc Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */




include_once('FDL/viewfolder.php');



// -----------------------------------
// -----------------------------------
function freedom_view(&$action) {
  // -----------------------------------
  // redirect layout icon if needed

  $prefview = getHttpvars("view");
  if ($prefview=="") $prefview=$action->getParam("FREEDOM_VIEW","list");
  switch ($prefview) {
  case "detail":
    $action->layout = $action->GetLayoutFile("freedom_listdetail.xml");
    $action->lay = new Layout($action->layout,$action);
  viewfolder($action, 2);
  break;
  case "icon":
    $action->layout = $action->GetLayoutFile("freedom_icons.xml");
    $action->lay = new Layout($action->layout,$action);
  viewfolder($action, false);
  break;
  case "column":
    $action->layout = $action->GetLayoutFile("freedom_column.xml");
    $action->lay = new Layout($action->layout,$action);
  viewfolder($action, false,true,true);
  break;
  case "rss":
    $action->layout = $action->GetLayoutFile("freedom_rss.xml");
    $action->lay = new Layout($action->layout,$action);
    setHttpVar("xml", 1);
    header('Content-type: text/xml; charset=utf-8');
    $action->lay->setEncoding("utf-8");
    viewfolder($action, false, false);
  break;
  default:
    $action->layout = $action->GetLayoutFile("freedom_list.xml");
    $action->lay = new Layout($action->layout,$action);
  viewfolder($action, false);
  break;
    
  }
  
  
}





?>
