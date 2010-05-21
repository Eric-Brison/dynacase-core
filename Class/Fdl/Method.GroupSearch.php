<?php
/**
 * Grouped searches
 *
 * @author Anakeen 2004
 * @version $Id: Method.GroupSearch.php,v 1.3 2005/06/28 08:37:46 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */




var $defaultedit= "FDL:EDITBODYCARD";
var $defaultview= "FDL:VIEWBODYCARD";




function ComputeQuery($keyword="",$famid=-1,$latest="yes",$sensitive=false,$dirid=-1, $subfolder=true) {
  $tidsearch=$this->getTValue("SEG_IDCOND");
  $wsql=array();
  $query[]="select * from doc1 limit 0;"; // null query
  foreach ($tidsearch as $k=>$v) {
    $doc = new_Doc($this->dbaccess,$v);
    
    if (method_exists($doc,"getQuery")) {
      $doc->setValue("SE_IDCFLD",$this->getValue("SE_IDCFLD"));
      $q=$doc->getQuery();
          
      $wsql[]=$q[0];

    }
  }
  if (count($wsql)>0) {
    $query=$wsql;
  }
    
 

  return $query;
}

/**
 * return false : is never staticSql
 * @return bool
 */
function isStaticSql() {
  return false;
}


?>