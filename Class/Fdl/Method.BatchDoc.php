<?php
/**
 * Method for batch freedom processes
 *
 * @author Anakeen 2005
 * @version $Id: Method.BatchDoc.php,v 1.2 2005/09/21 16:02:21 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage 
 */
 /**
 */
function filterContent() {
  
}
/**
   * return document includes in portfolio an in each of its guide or searched inside portfolio
   * @param bool $controlview if false all document are returned else only visible for current user  document are return
   * @param array $filter to add list sql filter for selected document
   * @param int $famid family identificator to restrict search 
   * @param bool $insertguide if true merge each content of guide else same as a normal folder
   * @return array array of document array
   */
function getContent($controlview=true,$filter=array(),$famid="",$insertguide=true) {
  
  return parent::getContent($controlview,$filter,$famid,$insertguide);
}

?>