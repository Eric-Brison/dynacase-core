<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Edition functions utilities
 *
 * @author Anakeen
 * @version $Id: editutil.php,v 1.182 2009/01/14 12:33:31 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */
//
// ---------------------------------------------------------------
include_once ("FDL/Class.Doc.php");
include_once ("FDL/Class.DocAttr.php");
include_once ("VAULT/Class.VaultFile.php");
/**
 * Compose html code to insert input
 * @param Doc &$doc document to edit
 * @param NormalAttribute &$oattr attribute to edit
 * @param string $value value of the attribute
 * @param string $index in case of array : row of the array
 * @param string $jsevent add an javascript callback on input (like onblur or onmouseover)
 * @param string $notd not add cells in html input generated (by default inputs are in arrays)
 */
function getHtmlInput(&$doc, &$oattr, $value, $index = "", $jsevent = "", $notd = false)
{
    $form = new DocFormFormat($doc);
    $form->useTd(!$notd);
    $form->setJsEvents($jsevent);
    return $form->getHtmlInput($oattr, $value, $index);
}
/**
 * add different js files needed in edition mode
 * @param Action $action
 */
function editmode(Action & $action)
{
    $action->parent->AddJsRef($action->GetParam("CORE_PUBURL") . "/lib/jquery/jquery.js");

    $ckeditorPath = "lib/ckeditor/4";
    if ($action->getParam("ISIE6") || $action->getParam("ISIE7")) {
        $ckeditorPath = "lib/ckeditor/3";
    }
    $action->parent->addJsRef("$ckeditorPath/ckeditor.js");
    $action->parent->AddJsRef(sprintf("%sapp=FDL&action=ALLEDITJS&wv=%s", $action->GetParam("CORE_SSTANDURL") , $action->GetParam("WVERSION")));

    $action->parent->addJsRef("lib/tipsy/src/javascripts/jquery.tipsy.js");


    $action->parent->addJsCode( sprintf("CKEDITOR_BASEPATH = '%s/';", $ckeditorPath));

    $action->parent->addCssRef("lib/tipsy/src/stylesheets/tipsy.css");
    $action->parent->addCssRef("css/dcp/main.css");
    $action->parent->addCssRef("css/dcp/document-edit.css");
}
?>
