<?php
/**
 * Modify item os enumerate attributes
 *
 * @author Anakeen 2000 
 * @version $Id: generic_modkind.php,v 1.8 2008/08/14 09:59:14 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



include_once("FDL/Class.Doc.php");
include_once("FDL/Class.DocAttr.php");
include_once("FDL/Lib.Attr.php");
include_once("GENERIC/generic_util.php"); 

// -----------------------------------
function generic_modkind(&$action) {
  // -----------------------------------

  
  $dbaccess = $action->GetParam("FREEDOM_DB");

  $aid    = GetHttpVars("aid");    // attribute id
  $famid  = GetHttpVars("fid");    // family id
  $tlevel = GetHttpVars("alevel"); // levels
  $tref   = GetHttpVars("aref");   // references
  $tlabel = GetHttpVars("alabel"); // label

  $tsref=array();
  $tsenum=array();
  $ref="";$ple = 1;
  if (is_array($tref)) {
    while (list($k, $v) = each($tref)) {
		$le = intval($tlevel[$k]);
		if ($le == 1){
			$ref = '';
		}
		else if ($ple < $le) {
			// add level ref index
			$ref = $ref . str_replace(".", "-dot-", $tref[$k - 1]) . '.';
		}
		else if ($ple > $le) {
			// suppress one or more level ref index
			for ($l = 0; $l < $ple - $le; $l++) {
				$ref = substr($ref, 0, strrpos($ref, '.') - 1);
			}
		}
		$ple = $le;
		$tsenum[$k] = stripslashes($ref . str_replace(".", "-dot-", $v) . "|" . $tlabel[$k]);
    }
  }

  $attr = new DocAttr($dbaccess, array($famid,$aid));
  if ($attr->isAffected()) {
  
    if (preg_match("/\[([a-z]+)\](.*)/",$attr->phpfunc, $reg)) {	 
      $funcformat=$reg[1];
    } else {	  
      $funcformat="";
    }
    $attr->phpfunc = str_replace("-dot-", "\\.", implode(",",str_replace(',','\,',($tsenum))));
    if ($funcformat != "") $attr->phpfunc="[$funcformat]".$attr->phpfunc;
    $attr->modify();

    refreshPhpPgDoc($dbaccess, $famid);
  }
		      
  $fdoc=new_doc($dbaccess,$famid);
  $a = $fdoc->getAttribute($aid);
  if ($a) { 
    $enum=$a->getenum();
    foreach ($enum as $kk=>$ki) {
	$tvkind[]=array("ktitle" => strstr($ki, '/')?strstr($ki, '/'):$ki,
			"level" =>  substr_count($kk, '.')*20,
			"kid" => $kk);
	
      }


    $action->lay->SetBlockData("vkind", $tvkind);
    
  }
  
  $action->lay->Set("desc", sprintf(_("Modification for attribute %s for family %s"),
				    $a->getLabel(),
				    $fdoc->title));

}


?>
