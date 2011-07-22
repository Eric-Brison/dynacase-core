<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: core_css.php,v 1.6 2005/09/27 15:01:15 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage CORE
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: core_css.php,v 1.6 2005/09/27 15:01:15 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Core/core_css.php,v $
// ---------------------------------------------------------------


function core_css(&$action) {
  
  $layout=getHttpVars("layout");

  if (preg_match("/([A-Z_-]+):([^:]+):{0,1}[A-Z]{0,1}/", $layout, $reg)) {
    $lfile= getLayoutFile($reg[1],strtolower($reg[2]));
    if ($lfile) $action->lay = new Layout(getLayoutFile($reg[1],strtolower($reg[2])), $action);
  }

  setHeaderCache();

}
?>