<?php
/**
 * Full Text Search document
 *
 * @author Anakeen 2007
 * @version $Id: fullsearch.php,v 1.19 2007/10/22 07:30:17 marc Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
 /**
 */
include_once("FDL/Lib.Dir.php");
include_once("FDL/Class.DocSearch.php");
include_once("FDL/freedom_util.php");  
/**
 * Fulltext Search document 
 * @param Action &$action current action
 * @global keyword Http var : word to search in any values
 * @global famid Http var : restrict to this family identioficator
 * @global start Http var : page number 
 * @global dirid Http var : search identificator
 */
function fullsearch(&$action) {

  $famid=GetHttpVars("famid",0);
  $keyword=GetHttpVars("_se_key",GetHttpVars("keyword")); // keyword to search
  $target=GetHttpVars("target"); // target window when click on document
  $start=GetHttpVars("start",0); // page number
  $dirid=GetHttpVars("dirid",0); // special search

  redirect($action, "FGSEARCH", "FULLSEARCH&famid=$famid&keyword=$keyword&target=$target&start=$start&dirid=$dirid");
}

?>
