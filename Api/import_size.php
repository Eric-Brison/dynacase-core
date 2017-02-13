<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * update list of available font style
 *
 * analyze sub-directories presents in STYLE directory
 * @author Anakeen
 * @version $Id: import_size.php,v 1.2 2007/02/21 11:07:12 eric Exp $
 * @package FDL
 * @subpackage WSH
 */
/**
 */
// ---------------------------------------------------------------
// $Id: import_size.php,v 1.2 2007/02/21 11:07:12 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Api/import_size.php,v $
// ---------------------------------------------------------------
include_once ("Class.Style.php");
include_once ("Lib.Color.php");
global $action;

$usage = new ApiUsage();

$usage->setDefinitionText("update list of available font style");

$usage->verify();

$param = new Param();

if (file_exists(DEFAULT_PUBDIR . "/WHAT/size.php")) {
    global $size;
    include ("WHAT/size.php");
    /*
     // delete first old parameters
     $query=new QueryDb("", "Param");
     $query->AddQuery("type='".PARAM_STYLE.$name."'");
     $list=$query->Query();
     if ($query->nb> 0) {       
       foreach($list as  $k => $v) {
    $v->delete();
       }
     }
    */
    
    if (isset($size)) {
        // compute all fonct size
        foreach ($size as $k => $v) {
            
            $stylename = "SIZE_" . strtoupper($k);
            
            print "stylename=$stylename\n";
            $sty = new Style("", $stylename);
            
            foreach ($v as $kf => $vf) {
                $kn = "SIZE_" . strtoupper($kf);
                if ($k == "normal") $param->Set($kn, $vf, Param::PARAM_GLB, 1); // put in default
                $param->Set($kn, $vf, Param::PARAM_STYLE . $stylename, 1);
                $action->parent->SetVolatileParam($kn, $vf); // to compose css with new paramters
                
            }
            
            if (!$sty->isAffected()) {
                $sty->name = $stylename;
                $sty->Add();
            } else $sty->Modify();
            
            $inputlay = new Layout("WHAT/Layout/size.css", $action);
            $out = $inputlay->gen();
            file_put_contents(DEFAULT_PUBDIR . "/WHAT/Layout/size-$k.css", $out);
        }
    }
}
