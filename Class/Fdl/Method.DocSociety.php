<?php
/**
 * Methods for society document
 *
 * @author Anakeen 2000 
 * @version $Id: Method.DocSociety.php,v 1.1 2004/01/15 10:12:02 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage USERCARD
 */
 /**
 */



function UpdateSite() {

  include_once("FDL/Lib.Dir.php");



  // contracts():SI_IDCONTRATS,SI_CONTRATS
  if ($this->initid > 0) {
    $filter[]="si_idsoc =  '".$this->initid."'";
    $tsite = getChildDoc($this->dbaccess, 0,0,"ALL", $filter,1,"TABLE","SITE");
    $idc=array();
    $tc=array();
    foreach ($tsite as $k=>$v) {
      $idc[] = $v["id"];
      $tc[] = $v["title"];
    }


    $this->setValue("SI_IDSITES",$idc);
    $this->setValue("SI_SITES",$tc);
  }
  
}
function SpecRefresh() {
  $this->UpdateSite();


}
?>