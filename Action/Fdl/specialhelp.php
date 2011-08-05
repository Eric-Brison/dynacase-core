<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 * View special interface to set value in document form
 *
 * @author Anakeen 2010
 * @version $Id:  $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Dir.php");
/**
 * View a document
 * @param Action &$action current action
 * @global docid Http var : document identificator to see
 * @global attrid Http var :  the attribute comes from search
 */
function specialhelp(Action & $action)
{
    // -----------------------------------
    $docid = $action->getArgument("docid");
    $attrid = $action->getArgument("attrid");
    $dbaccess = $action->GetParam("FREEDOM_DB");
    
    if ($docid == "") $action->exitError(_("no document reference"));
    if (!is_numeric($docid)) $docid = getIdFromName($dbaccess, $docid);
    if (intval($docid) == 0) $action->exitError(sprintf(_("unknow logical reference '%s'") , $action->getArgument("id")));
    $doc = new_Doc($dbaccess, $docid);
    if (!$doc->isAffected()) $action->exitError(sprintf(_("cannot see unknow reference %s") , $docid));
    
    $oa = $doc->getAttribute($attrid);
    if (!$oa) $action->exitError(sprintf(_("unknow attribute '%s'") , $attrid));
    
    $phpfunc = $oa->phpfunc;
    if (preg_match('/([^:]+):([^(]+)\(([^)]*)\):(.*)/i', $phpfunc, $reg)) {
        $appname = $reg[1];
        $zone = $reg[2];
        $funarg = explode(",", $reg[3]);
        if ($reg[4]) $funres = explode(",", $reg[4]);
        else $funres = array();
        
        $action->lay = new Layout(getLayoutFile($appname, $zone . ".xml") , $action);
        
        $incfile = sprintf("EXTERNALS/%s", $oa->phpfile);
        if (file_exists($incfile)) {
            try {
                include_once ($incfile);
                if (function_exists(strtolower($zone))) {
                    include_once ("FDL/enum_choice.php");
                    $oa->phpfunc = substr($phpfunc, strpos($phpfunc, ':') + 1);
                    $res = getResPhpFunc($doc, $oa, $rargids, $tselect, $tval, true, $index, $zone);
                    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/geometry.js");
                    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/autoclose.js");
                    $action->parent->AddJsRef("FDL:specialhelp.js", true);
                    
                    $action->parent->AddJsCode("Ih.resultArguments=" . json_encode($funres)) . ";";
                } else {
                    $action->exitError(sprintf(_("Cannot find help function %s") , strtolower($zone)));
                }
            }
            catch(Exception $e) {
                $action->exitError(sprintf(_("Cannot include '%s'") , sprintf("%s/%s.php", $appname, strtolower($zone))));
            }
        }
    } else {
        $action->exitError(sprintf(_("declaration syntax does not match special help input '%s'") , $phpfunc));
    }
    
    if (GetHttpVars('extjs', '') != '') {
        $action->parent->AddCssRef("STYLE/DEFAULT/Layout/EXT-ADAPTER-SYSTEM.css");
        if (file_exists($action->parent->rootdir . "/STYLE/$style/Layout/EXT-ADAPTER-USER.css")) {
            $action->parent->AddCssRef("STYLE/$style/Layout/EXT-ADAPTER-USER.css");
        } else {
            $action->parent->AddCssRef("STYLE/DEFAULT/Layout/EXT-ADAPTER-USER.css");
        }
    }
}
?>
