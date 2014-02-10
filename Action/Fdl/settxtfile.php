<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Update file text which comes from transformation engine
 *
 * @author Anakeen
 * @version $Id: settxtfile.php,v 1.13 2007/12/12 15:22:36 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Doc.php");
include_once ("FDL/Class.TaskRequest.php");
include_once ("WHAT/Class.TEClient.php");
/**
 * Modify the attrid_txt attribute
 * @param Action &$action current action
 * @global string $docid Http var : document identifier to modify
 * @global string $attrid Http var : the id of attribute to modify
 * @global string $index Http var : the range in case of array
 * @global string $tid Http var : task identifier
 *
 */
function settxtfile(Action & $action)
{
    $docid = $action->getArgument("docid");
    $attrid = $action->getArgument("attrid");
    $index = $action->getArgument("index", -1);
    $tid = $action->getArgument("tid");
    $dbaccess = $action->GetParam("FREEDOM_DB");
    $err = '';
    if (!$tid) $err = _("no task identifier found");
    else {
        $ot = new TransformationEngine($action->getParam("TE_HOST") , $action->getParam("TE_PORT"));
        
        $err = $ot->getInfo($tid, $info);
        if ($err == "") {
            $tr = new TaskRequest($dbaccess, $tid);
            if ($tr->isAffected()) {
                $tr->delete(); // no need now
                $outfile = $info["outfile"];
                $status = $info["status"];
                $sem = fopen(getTmpDir() . "/fdl$docid.lck", "a+");
                
                if (flock($sem, LOCK_EX)) {
                    //fwrite($sem,'fdl'.posix_getpid().":lock\n");
                    $doc = new_Doc($dbaccess, $docid);
                    if (!$doc->isAffected()) $err = sprintf(_("cannot see unknow reference %s") , $docid);
                    if ($err == "") {
                        
                        if (($status == 'D') && ($outfile != '')) {
                            $filename = uniqid(getTmpDir() . "/txt-" . $doc->id . '-');
                            $err = $ot->getTransformation($tid, $filename);
                            //$err=$ot->getAndLeaveTransformation($tid,$filename);
                            if ($err == "") {
                                $at = $attrid . '_txt';
                                if (file_exists($filename) && $info['status'] == 'D') {
                                    if ($index == - 1) {
                                        $doc->$at = file_get_contents($filename);
                                    } else {
                                        if ($doc->AffectColumn(array(
                                            $at
                                        ))) {
                                            
                                            $txts = Doc::rawValueToArray($doc->$at);
                                            $txts[$index] = file_get_contents($filename);
                                            $doc->$at = Doc::arrayToRawValue($txts);
                                        }
                                    }
                                    $av = $attrid . '_vec';
                                    $doc->fields[$av] = $av;
                                    $doc->$av = '';
                                    
                                    $doc->fulltext = '';
                                    $doc->fields[$at] = $at;
                                    $doc->fields['fulltext'] = 'fulltext';
                                    $err = $doc->modify(true, array(
                                        'fulltext',
                                        $at,
                                        $av
                                    ) , true);
                                    $doc->addHistoryEntry(sprintf(_("text conversion done for file %s") , $doc->vault_filename($attrid, false, $index)) , HISTO_NOTICE);
                                    if (($err == "") && ($doc->locked == - 1)) {
                                        // propagation in case of auto revision
                                        $idl = $doc->getLatestId();
                                        $ldoc = new_Doc($dbaccess, $idl);
                                        if ($doc->getRawValue($attrid) == $ldoc->getRawValue($attrid)) {
                                            $ldoc->$at = $doc->$at;
                                            $ldoc->$av = '';
                                            $ldoc->fulltext = '';
                                            $ldoc->fields[$at] = $at;
                                            $ldoc->fields[$av] = $av;
                                            $ldoc->fields['fulltext'] = 'fulltext';
                                            $err = $ldoc->modify(true, array(
                                                'fulltext',
                                                $at,
                                                $av
                                            ) , true);
                                        }
                                    }
                                } else {
                                    $err = sprintf(_("output file [%s] not found") , $filename);
                                }
                                @unlink($filename);
                            }
                        } else {
                            $err = sprintf(_("task %s is not done correctly") , $tid);
                        }
                        if ($err != "") $doc->addHistoryEntry(sprintf(_("conversion failed for %s: ") . $err, $doc->vault_filename($attrid, false, $index)) , HISTO_NOTICE);
                    } else {
                        $err = sprintf(_("document [%s] not found") , $docid);
                    }
                    //fwrite($sem,posix_getpid().":unlock\n");
                    fclose($sem);
                } else {
                    $err = sprintf(_("semaphore block") , $docid);
                }
            } else {
                $err = sprintf(_("task %s is not recorded") , $tid);
            }
        }
    }
    
    if ($err != '') $action->lay->template = $err;
    else $action->lay->template = "OK : " . sprintf(_("doc %d indexed") , $docid);
}
?>