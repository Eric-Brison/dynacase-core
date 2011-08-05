<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: Method.DocSite.php,v 1.5 2005/06/28 08:37:46 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage USERCARD
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: Method.DocSite.php,v 1.5 2005/06/28 08:37:46 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Usercard/Method.DocSite.php,v $
// ---------------------------------------------------------------


function PostModify() {
  // refresh mother society
  $ids = $this->getValue("SI_IDSOC");
  $soc= new_Doc($this->dbaccess, $ids);
  if ($soc->isAlive())   $soc->refresh();



}

	
?>