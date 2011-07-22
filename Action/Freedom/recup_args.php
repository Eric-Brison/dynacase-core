<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: recup_args.php,v 1.3 2005/06/28 08:37:46 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: recup_args.php,v 1.3 2005/06/28 08:37:46 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/recup_args.php,v $
// ---------------------------------------------------------------
include_once("FDL/Class.Doc.php");
include_once("FDL/Class.DocFam.php");
include_once("FDL/modcard.php");
function recup_args(&$action){
  

 $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
 

  $famid= GetHttpVars("famid");
  // printf($famid);
  $xml = GetHttpVars("xml");
  $temp=base64_decode($xml);
  // printf("hey");
  //printf($temp);
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $attrid=GetHttpVars("attrid");




  $action->lay->Set("attrid",$attrid);
 



  $docid=GetHttpVars("docid");
  //printf($docid);
  $dbaccess = $action->GetParam("FREEDOM_DB");
  
  $doc = new_Doc($dbaccess, $docid);
  //print_r($doc);

  if ($doc->GetValue("act_type")==1){//action de type action
    $idoc= createDoc($dbaccess,601);
    $action->lay->Set("famid",601);// familly action_implement
  }
  if ($doc->GetValue("act_type")==2){//action de type condition
    $idoc= createDoc($dbaccess,602);
    $action->lay->Set("famid",602);// familly condition_implement
  }

  $args_nom=$doc->GetValue("act_liste_noms");
  //printf($args_nom);
  $args_descrps=$doc->GetValue("act_liste_descrps");

  $idoc->SetValue("ai_args_nom", $args_nom);
  $idoc->SetValue("ai_args_descrip",  $args_descrps);

  $nom=GetHttpVars("nom_act");
  $titre=GetHttpVars("titre");
  //printf("titre : ");
  //printf($titre);

 $idoc->SetValue("ai_action", $nom);
 $idoc->SetValue("ba_title",  $titre);
 $idoc->SetValue("ai_idaction", $docid);

 //printf("ai_args_nom : ");
 //printf($idoc->GetValue("ai_args_nom"));

 // print_r($idoc);


  $xml2=$idoc->toxml(false,"");
  //printf($xml2);
  $xml_send=base64_encode($xml2);
  $action->lay->Set("xml2",$xml_send);

}
?>