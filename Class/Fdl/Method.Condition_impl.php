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
 * @version $Id: Method.Condition_impl.php,v 1.3 2008/08/14 09:59:14 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage GED
 */
/**
 */
// ---------------------------------------------------------------
// $Id: Method.Condition_impl.php,v 1.3 2008/08/14 09:59:14 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Freedom/Method.Condition_impl.php,v $
// ---------------------------------------------------------------
var $defaultedit = "FREEDOM:EDIT_IMPL";

function edit_impl($target = "finfo", $ulink = true, $abstract = "Y")
{
    global $action;
    include_once ("FDL/editutil.php");
    //$action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FREEDOM/Layout/workflow.js");
    $this->lay->Set("famid", 554);
    
    $this->lay->Set("docid", $this->id);
    $this->lay->Set("TITLE", $this->title);
    
    $title = $this->Getattribute("BA_TITLE");
    $this->lay->Set("name1", $title->getLabel());
    $value = $this->GetValue($title->id);
    $this->lay->Set("inputtype1", getHtmlInput($this, $title, $value));
    
    $descrip = $this->Getattribute("AI_ACTION");
    $this->lay->Set("name2", $descrip->getLabel());
    $value = $this->GetValue($descrip->id);
    $this->lay->Set("inputtype2", getHtmlInput($this, $descrip, $value));
    
    $etat = $this->Getattribute("AI_ARGS");
    $this->lay->Set("name3", $etat->getLabel());
    $value = $this->GetValue($etat->id);
    $this->lay->Set("inputtype3", getHtmlInput($this, $etat, $value));
    
    $etat = $this->Getattribute("AI_IDACTION");
    $value = $this->GetValue($etat->id);
    $this->lay->Set("inputtype4", getHtmlInput($this, $etat, $value));
}
?>