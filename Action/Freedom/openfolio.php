<?php
/*
 * Open port folio document
 * @author Anakeen
 * @package FDL
*/
/**
 */
include_once ("FDL/Class.Doc.php");
include_once ('FREEDOM/Lib.portfolio.php');
// -----------------------------------
// -----------------------------------
function openfolio(Action & $action)
{
    // -----------------------------------
    // Set the globals elements
    $docid = GetHttpVars("id", 0); // document to edit
    $dbaccess = $action->dbaccess;
    
    $folio = new_Doc($dbaccess, $docid);
    
    if (!$folio->isAffected()) {
        $action->exitError(sprintf(_("document %s not exists") , $docid));
    }
    $action->lay->rSet("docid", (int)$folio->id);
    $action->lay->eSet("title", $folio->getTitle());
    
    $dir = new_Doc($dbaccess, $folio->initid);
    $savedTab = $dir->getFamilyParameterValue('pfl_savetab');
    $savedDispo = $dir->getFamilyParameterValue('pfl_savedispo', 1);
    $pfctx = portfolio_get_context($dir);
    
    $action->lay->eSet("FRAMELISTWIDTH", '180');
    $action->lay->rSet("VIEWSTATE", json_encode(2));
    $action->lay->rSet("TABSELECTED", json_encode(''));
    $action->lay->eSet("FRAME1", '&app=FDL&action=FDL_CARD&latest=Y&id=' . $folio->initid);
    $action->lay->eSet("FRAME2", '&app=FREEDOM&action=FREEDOM_LOGO');
    $action->lay->eSet("FRAMELIST", '&app=FREEDOM&action=FOLIOLIST&dirid=' . $folio->initid);
    
    if ($savedTab) {
        if (isset($pfctx['tabselected'])) {
            $action->lay->rSet("TABSELECTED", json_encode($pfctx['tabselected']));
        }
        if (isset($pfctx['framelist'])) {
            $action->lay->eSet("FRAMELIST", '&app=FREEDOM&action=FOLIOLIST&dirid=' . urlencode($pfctx['framelist']['dirid']) . '&folioid=' . urlencode($pfctx['framelist']['folioid']));
        }
    }
    
    if ($savedDispo) {
        if (isset($pfctx['framelistwidth'])) {
            $action->lay->eSet("FRAMELISTWIDTH", $pfctx['framelistwidth']);
        }
        
        if (isset($pfctx['viewstate'])) {
            $action->lay->rSet("VIEWSTATE", json_encode($pfctx['viewstate']));
        }
        if (isset($pfctx['frame1'])) {
            $doc1 = new_Doc($dbaccess, $pfctx['frame1']);
            if ($doc1->isAlive()) {
                $action->lay->eSet("FRAME1", '&app=FDL&action=FDL_CARD&latest=Y&id=' . urlencode($pfctx['frame1']));
            }
        }
        if (isset($pfctx['frame2'])) {
            $doc2 = new_Doc($dbaccess, $pfctx['frame2']);
            if ($doc2->isAlive()) {
                $action->lay->eSet("FRAME2", '&app=FDL&action=FDL_CARD&latest=Y&id=' . urlencode($pfctx['frame2']));
            }
        }
    }
}
