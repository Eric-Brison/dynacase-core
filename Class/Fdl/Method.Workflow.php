<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000
 * @version $Id: Method.Workflow.php,v 1.4 2008/08/14 09:59:14 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
/**
 */
// ---------------------------------------------------------------
// $Id: Method.Workflow.php,v 1.4 2008/08/14 09:59:14 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Freedom/Method.Workflow.php,v $
// ---------------------------------------------------------------
var $defaultedit = "FREEDOM:EDIT_WORKFLOW";
var $defaultview = "FREEDOM:VIEW_WORKFLOW:S";

function edit_workflow($target = "finfo", $ulink = true, $abstract = "Y")
{
    global $action;
    include_once ("FDL/editutil.php");
    //$action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FREEDOM/Layout/workflow.js");
    $this->lay->Set("docid", $this->id);
    $this->lay->Set("TITLE", $this->title);
    if (($docid = $this->id) != 0) {
        $this->lay->Set("onclick", "edit_transition($docid);");
    } else {
        $this->lay->Set("onclick", "alert('veuillez valider le document avant d editer les transitions');");
    }
    
    $title = $this->Getattribute("BA_TITLE");
    $this->lay->Set("name1", $title->getLabel());
    $value = $this->GetValue($title->id);
    $this->lay->Set("inputtype1", getHtmlInput($this, $title, $value));
    
    $descrip = $this->Getattribute("WOR_DESCRIP");
    $this->lay->Set("name2", $descrip->getLabel());
    $value = $this->GetValue($descrip->id);
    $this->lay->Set("inputtype2", getHtmlInput($this, $descrip, $value));
    
    $etat = $this->Getattribute("WOR_ETAT");
    $this->lay->Set("name3", $etat->getLabel());
    $value = $this->GetValue($etat->id);
    $this->lay->Set("inputtype3", getHtmlInput($this, $etat, $value));
    
    $tt = $this->Getattribute("WOR_TT");
    $this->lay->Set("name4", $tt->getLabel());
    $value = $this->GetValue($tt->id);
    $this->lay->Set("inputtype4", getHtmlInput($this, $tt, $value));
    
    $tt = $this->Getattribute("WOR_FAM");
    $this->lay->Set("name5", $tt->getLabel());
    $value = $this->GetValue($tt->id);
    $this->lay->Set("inputtype5", getHtmlInput($this, $tt, $value));
}

function view_workflow()
{
    global $action;
    
    $nom_etats = explode("\n", $this->GetValue("wor_nometat"));
    $id_etats = explode("\n", $this->GetValue("wor_idetat"));
    $idetats_ini = explode("\n", $this->GetValue("wor_trans_idetat_ini"));
    $idetats_fin = explode("\n", $this->GetValue("wor_trans_idetat_fin"));
    $descriptions = explode("\n", $this->GetValue("wor_trans_descrip"));
    $tts = explode("\n", $this->GetValue("wor_trans_tt"));
    $tab_tts = explode("\n", $this->Getvalue("wor_tt"));
    
    while (list($k, $xmlencode) = each($tab_tts)) {
        $temp = base64_decode($xmlencode);
        $entete = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\" standalone=\"yes\" ?>";
        $xml = $entete;
        $xml.= $temp;
        $id = recup_argument_from_xml($xml, "id_doc");
        $tab_relation_tts[$id]["xml"] = $xmlencode;
        $title = recup_argument_from_xml($xml, "title");
        $tab_relation_tts[$id]["title"] = $title;
    }
    //print_r($tab_relation_tts);
    
    while (list($k, $v) = each($idetats_ini)) {
        $descrip[$v][$idetats_fin[$k]] = $descriptions[$k];
        $type_trans[$v][$idetats_fin[$k]] = $tts[$k];
    }
    // print_r($type_trans);
    $ligne1 = array();
    reset($nom_etats);
    $nom_etats2 = $nom_etats;
    $nom_etats[-1] = "transition_initiale";
    $id_etats[-1] = "-1";
    //to be in  first
    $lignes[-1]["etat"] = "transition_initiale";
    $lignes[-1]["LIGNEEE"] = "LIGNE_transition_initiale:-1";
    
    while (list($i, $etat) = each($nom_etats)) {
        if ($i != - 1) {
            $ligne1[$i]["nom_etat"] = $etat;
            // $ligne2[$i]["new_trans"]="";
            $lignes[$i]["etat"] = $etat;
            $lignes[$i]["LIGNEEE"] = "LIGNE_$etat";
        }
    }
    
    $this->lay->setBlockData("LIGNE1", $ligne1);
    $this->lay->setBlockData("LIGNE2", $ligne2);
    $this->lay->setBlockData("LIGNES", $lignes);
    
    reset($nom_etats);
    
    while (list($i, $etat) = each($nom_etats)) {
        reset($nom_etats2);
        while (list($x, $etat2) = each($nom_etats2)) {
            
            $etat_ini = $etat;
            // $idetat_ini=$idetats_ini[$i];
            $idetat_ini = $id_etats[$i];
            $etat_fin = $etat2;
            $idetat_fin = $id_etats[$x];
            // $idetat_fin=$idetats_fin[$x];
            $result = 100 / sizeof($nom_etats);
            // printf($result);
            $id_tt = $type_trans[$idetat_ini][$idetat_fin];
            
            if ($id_tt != "") {
                $inputlay = new Layout("FREEDOM/Layout/input_transition2.xml", $action);
                $inputlay->set("width", "$result");
                $inputlay->set("value_descrip", $descrip[$idetat_ini][$idetat_fin]);
                $xml_tt = $tab_relation_tts["$id_tt"]["xml"];
                $inputlay->set("xml", $xml_tt);
                $text_tt = $tab_relation_tts["$id_tt"]["title"];
                $inputlay->set("text_tt", $text_tt);
                $inputlay->set("famid", 555);
                
                $inte[$i][$x]["input"] = $inputlay->gen();
            } else {
                $inte[$i][$x]["input"] = "<td></td>";
            }
        }
    }
    reset($nom_etats);
    //$inte[0]["idtt"]="dd";
    while (list($i, $etat) = each($nom_etats)) {
        //  print_r($inte[$i]);printf("<BR>");
        $this->lay->setBlockData($lignes[$i]["LIGNEEE"], $inte[$i]);
    }
    //$action->lay->gen();
    
}
?>