<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Edition functions utilities
 *
 * @author Anakeen
 * @version $Id: editutil.php,v 1.182 2009/01/14 12:33:31 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
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
    $action->parent->AddJsRef(sprintf("%sapp=FDL&action=ALLEDITJS&wv=%s", $action->GetParam("CORE_SSTANDURL") , $action->GetParam("WVERSION")));
    $action->parent->AddCssRef(sprintf("%sapp=FDL&action=ALLEDITCSS&wv=%s", $action->GetParam("CORE_SSTANDURL") , $action->GetParam("WVERSION")));
}
?>
