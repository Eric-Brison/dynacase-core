<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Insert rendering file which comes from transformation engine
 *
 * @author Anakeen
 * @version $Id: insertfile.php,v 1.8 2007/12/10 09:15:03 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Doc.php");
include_once ("FDL/Class.TaskRequest.php");
include_once ("Lib.FileMime.php");
/**
 * Modify the attrid_txt attribute
 * @param Action &$action current action
 * @global docid string Http var : document identifier to modify
 * @global attrid string Http var : the id of attribute to modify
 * @global index int Http var : the range in case of array
 * @global tid string Http var : task identifier
 *
 */
function insertfile(&$action)
{
    $vidin = GetHttpVars("vidin");
    $vidout = GetHttpVars("vidout");
    $tid = GetHttpVars("tid");
    $name = GetHttpVars("name");
    $docid = GetHttpVars("docid");
    $dbaccess = $action->dbaccess;
    
    if (!$tid) $err = _("no task identifier found");
    else {
        $filename = tempnam(getTmpDir() , 'txt-');
        if ($filename === false) {
            $err = sprintf(_("Error creating temporary file in '%s'.") , getTmpDir());
        } else {
            $err = getTEFile($tid, $filename, $info);
            if ($err == "") {
                
                $outfile = $info["outfile"];
                $status = $info["status"];
                $infoin = new VaultFileInfo();
                $infoout = new VaultFileInfo();
                
                if (($status == 'D') && ($outfile != '')) {
                    
                    $vf = newFreeVaultFile($dbaccess);
                    $vf->Retrieve($vidin, $infoin);
                    $vf->Retrieve($vidout, $infoout);
                    $vf->Save($filename, false, $vidout);
                    $err = $vf->Retrieve($vidout, $infoout); // relaod for mime
                    $ext = getExtension($infoout->mime_s);
                    if ($ext == "") $ext = $infoout->teng_lname;
                    //	  print_r($infoout);
                    // print_r($ext);
                    if ($name != "") {
                        $newname = $name;
                    } else {
                        $pp = strrpos($infoin->name, '.');
                        $newname = substr($infoin->name, 0, $pp) . '.' . $ext;
                    }
                    
                    $vf->Rename($vidout, $newname);
                    $vf->storage->teng_state = \Dcp\TransformationEngine\Client::status_done;
                    $vf->storage->modify();
                } else {
                    $vf = newFreeVaultFile($dbaccess);
                    $vf->Retrieve($vidin, $infoin);
                    $vf->Retrieve($vidout, $infoout);
                    
                    $filename2 = tempnam(getTmpDir() , 'txt-');
                    if ($filename2 === false) {
                        $err = sprintf(_("Error creating temporary file in '%s'.") , getTmpDir());
                    } else {
                        $error = sprintf(_("Conversion as %s has failed ") , $infoout->teng_lname);
                        $error.= "\n== " . _("See below information about conversion") . "==\n" . print_r($info, true);
                        file_put_contents($filename2, $error);
                        //$vf->rename($vidout,"toto.txt");
                        $vf->Retrieve($vidout, $infoout);
                        $err = $vf->Save($filename2, false, $vidout);
                        $basename = _("conversion error") . ".txt";
                        $vf->Rename($vidout, $basename);
                        $vf->storage->teng_state = \Dcp\TransformationEngine\Client::error_convert;
                        $vf->storage->modify();
                        if ($docid) {
                            $doc = new_doc($dbaccess, $docid);
                            if ($doc->isAlive()) {
                                $doc->addHistoryEntry(sprintf(_("convert file %s as %s failed") , $infoin->name, $infoout->teng_lname) , HISTO_ERROR);
                            }
                        }
                        unlink($filename2);
                    }
                }
            }
            unlink($filename);
        }
    }
    if ($err != '') $action->lay->template = htmlspecialchars($err, ENT_QUOTES);
    else $action->lay->template = htmlspecialchars("OK : " . sprintf(_("vid %s stored") , $vidout) , ENT_QUOTES);
    $action->lay->noparse = true;
}
/**
 * return filename where is stored produced file
 * need to delete after use it
 * @param $tid string
 * @param $filename string
 * @param $info array
 * @return string
 */
function getTEFile($tid, $filename, &$info)
{
    global $action;
    $dbaccess = $action->dbaccess;
    $ot = new \Dcp\TransformationEngine\Client($action->getParam("TE_HOST") , $action->getParam("TE_PORT"));
    
    $err = $ot->getInfo($tid, $info);
    if ($err == "") {
        $tr = new TaskRequest($dbaccess, $tid);
        if ($tr->isAffected()) {
            $outfile = $info["outfile"];
            $status = $info["status"];
            
            if (($status == 'D') && ($outfile != '')) {
                $err = $ot->getTransformation($tid, $filename);
                //$err=$ot->getAndLeaveTransformation($tid,$filename); // to debug
                
            }
        } else {
            $err = sprintf(_("task %s is not recorded") , $tid);
        }
    }
    return $err;
}
