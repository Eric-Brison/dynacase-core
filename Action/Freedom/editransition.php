<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: editransition.php,v 1.4 2008/12/02 15:23:44 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: editransition.php,v 1.4 2008/12/02 15:23:44 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/editransition.php,v $
// ---------------------------------------------------------------
include_once('FDL/Class.Doc.php');
function editransition(&$action){ 
  $etats=GetHttpVars("state");
  $tab=explode(",",$etats);
  $tt=GetHttpVars("tt");
  $tabtt=explode(",",$tt);
  while (list($k, $v) = each($tabtt)){
    $tab_tt[$k]=explode("*",$v);
  }

  // print_r($tab_tt);
  while (list($i,$tt)=each($tab_tt)){
    $tab_relation_tt[$tt[1]]=$tt[0];
  }


  $docid=GetHttpVars("docid");
  $action->lay->set("id",$docid);
 
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $doc= new_Doc($dbaccess,$docid);
 




  $idetats_ini=explode("\n",$doc->GetValue("wor_trans_idetat_ini"));
  $idetats_fin=explode("\n",$doc->GetValue("wor_trans_idetat_fin"));
  $descriptions=explode("\n",$doc->GetValue("wor_trans_descrip"));
  $tts=explode("\n",$doc->GetValue("wor_trans_tt"));


  while (list($k, $v) = each($idetats_ini)) {
    $descrip[$v][$idetats_fin[$k]]=$descriptions[$k];
    $type_trans[$v][$idetats_fin[$k]]=$tts[$k];
  }

  $ligne1=array();
  reset($tab);
  $tab2=$tab;
  $tab[-1]="transition_initiale:-1";

  //to be in  first
  $lignes[-1]["etat"]="transition_initiale";
  $lignes[-1]["LIGNEEE"]="LIGNE_transition_initiale:-1";

  while (list($i,$etat)=each($tab)){
    $nom=explode(":",$etat);
    if($i!=-1){
      $ligne1[$i]["nom_etat"]=$nom[0];
      // $ligne2[$i]["new_trans"]="";
     
     
      $lignes[$i]["etat"]=$nom[0];
      $lignes[$i]["LIGNEEE"]="LIGNE_$etat";
    }
  }


  $action->lay->setBlockData("LIGNE1",$ligne1);
  $action->lay->setBlockData("LIGNE2",$ligne2);
  $action->lay->setBlockData("LIGNES",$lignes);

  reset($tab);

 while(list($i,$etat)=each($tab)){
   reset($tab2);
  while(list($x,$etat2)=each($tab2)){

   
   
      $inputlay=new Layout("FREEDOM/Layout/input_transition.xml",$action);

      $etat_ini=explode(":",$etat);
      $etat_fin=explode(":",$etat2);
      $result=100/sizeof($tab);
      // printf($result);
      $inputlay->set("width","$result");
      $inputlay->set("value_etat_ini",$etat_ini[0]);
      $inputlay->set("value_idetat_ini",$etat_ini[1]);
      $inputlay->set("value_etat_fin",$etat_fin[0]);
      $inputlay->set("value_idetat_fin",$etat_fin[1]);
      $inputlay->set("value_descrip",$descrip[$etat_ini[1]][$etat_fin[1]]);
      $value_tt=$type_trans[$etat_ini[1]][$etat_fin[1]];
      $inputlay->set("value_tt",$value_tt);
      $inputlay->set("text_tt",$tab_relation_tt[$value_tt]);
      $temp="trans";
      $temp.=$i;
      $temp.="_$x";
      $inputlay->set("id_tt",$temp);
      // $inte[$i][$x]["idtt"]= $temp;
      $inte[$i][$x]["input"]= $inputlay->gen();
     
      

   
  }

 }
 reset($tab);
 //$inte[0]["idtt"]="dd";
 while (list($i,$etat)=each($tab)){

   //  print_r($inte[$i]);printf("<BR>");
   $action->lay->setBlockData($lignes[$i]["LIGNEEE"],$inte[$i]);
 }

 reset($tab_tt);
 while (list($i,$tt)=each($tab_tt)){
   $option[$i]["titre"]=$tt[0];//nom $tt[1] correspond a l'id de l'attribut
   $option[$i]["id_tt"]=$tt[1];
 }
 $action->lay->setBlockData("OPTIONS",$option);
 $action->lay->gen();
}


?>