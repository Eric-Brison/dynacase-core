<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: freedom_ifld.php,v 1.7 2005/06/28 08:37:46 eric Exp $
 * @package FDL
 * @subpackage GED
 */
/**
 */
// ---------------------------------------------------------------
// $Id: freedom_ifld.php,v 1.7 2005/06/28 08:37:46 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/freedom_ifld.php,v $
// ---------------------------------------------------------------
include_once ('FDL/Class.Doc.php');
include_once ('FDL/Class.Dir.php');
// -----------------------------------
// search all folder where is docid
// -----------------------------------
function freedom_ifld(Action & $action)
{
    // -----------------------------------
    $docid = GetHttpVars("id");
    $dbaccess = $action->dbaccess;
    $doc = new_Doc($dbaccess, $docid);
    
    $lfather = array_reverse(fatherFld($dbaccess, $doc->initid));
    
    $lmax = 0;
    $lprev = 0;
    foreach ($lfather as $k => $v) {
        // recompute level for indentation
        if ($lprev == 0) $lmax = $lfather[$k]["level"];
        $lfather[$k]["level"] = - ($v["level"] - $lmax) * 15; // by 15px
        $lprev = $v["level"];
    }
    
    $action->lay->Set("TITLE", $doc->getHTMLTitle());
    $action->lay->SetBlockData("IFLD", $lfather);
}

function fatherFld($dbaccess, $docid, $level = 0, $lfldid = array() , $lcdoc = array())
{
    // compute all path to accessing  document
    $doc = new_doc($dbaccess, $docid);
    $flds = $doc->getParentFolderIds();
    $ldoc2 = array();
    if (count($flds) > 0) {
        
        foreach ($flds as $pfldid) {
            
            if (!in_array($pfldid, $lfldid)) {
                // avoid infinite recursion
                $fld = new_Doc($dbaccess, $pfldid);
                if ($fld->Control("view") == "") {
                    // permission view folder
                    $ldoc1 = array(
                        "level" => $level,
                        "ftitle" => $fld->getHTMLTitle() ,
                        "fid" => $fld->id
                    );
                    
                    $lcdoc1 = $lcdoc;
                    $lcdoc1[] = $ldoc1;
                    
                    $lfldid1 = $lfldid;
                    $lfldid1[] = $pfldid;
                    
                    $ldoc2 = array_merge(fatherFld($dbaccess, $pfldid, $level + 1, $lfldid1, $lcdoc1) , $ldoc2);
                } else $ldoc2 = $lcdoc;
            } else $ldoc2 = $lcdoc;
        }
    } else return $lcdoc;
    return $ldoc2;
}
