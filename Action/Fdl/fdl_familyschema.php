<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * View family schema for import
 *
 * @author Anakeen
 * @version $Id: fdl_css.php,v 1.6 2007/11/08 15:53:59 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */
/**
 * View scheam family
 * @param Action &$action current action
 * @global string $id Http var : family identifier to see
 */
include_once ("FDL/Class.Doc.php");
// -----------------------------------
function fdl_familyschema(Action & $action)
{
    // -----------------------------------
    $docid = getHttpVars("id");
    $dbaccess = $action->dbaccess;
    /**
     * @var DocFam $doc
     */
    $doc = new_Doc($dbaccess, $docid);
    if (!$doc->isAlive()) $action->exitError(_("no family identificator"));
    if ($doc->doctype != 'C') $action->exitError(sprintf(_("document %s is not a family") , $docid));
    
    header('Content-Type: text/xml; charset="utf-8"');
    $action->lay->noparse = true;
    $action->lay->template = $doc->getXmlSchema();
}
