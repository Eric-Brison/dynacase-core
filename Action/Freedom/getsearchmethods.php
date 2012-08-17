<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Get search methods for a given attribute and family
 */

include_once ("FDL/Class.Doc.php");

function getsearchmethods(Action & $action)
{
    $res = array(
        'error' => null,
        'data' => array()
    );
    
    $parms = array();
    foreach (array(
        'famid' => true,
        'attrid' => true
    ) as $p => $isRequired) {
        $parms[$p] = GetHttpVars($p, '');
        if ($parms[$p] == '' && $isRequired) {
            $res['error'] = sprintf("Missing or empty parameter '%s'.", $p);
            sendResponse($action, $res);
            return;
        }
    }
    /**
     * @var DocFam $fam
     */
    $fam = new_Doc($action->dbaccess, $parms['famid'], true);
    if ($parms['famid'] && (!$fam->isAlive())) {
        $res['error'] = sprintf("Could not get family with id '%s'.", $parms['famid']);
        sendResponse($action, $res);
        return;
    }
    
    $tmpDoc = createTmpDoc($action->dbaccess, $fam->id);
    if (!$tmpDoc) {
        $res['error'] = sprintf("Could not create temporary document from family '%s'.", $fam->name);
        sendResponse($action, $res);
        return;
    }
    
    $attrId = $parms['attrid'];
    if (isset(Doc::$infofields[$attrId])) {
        $attrType = Doc::$infofields[$attrId]['type'];
    } else {
        $attr = $fam->getAttribute($parms['attrid']);
        if (!is_object($attr)) {
            $res['error'] = sprintf("Could not get attribute '%s' from family '%s'.", $parms['attrid'], $fam->name);
            sendResponse($action, $res);
            return;
        }
        $attrType = $attr->type;
        if ($attr->format != '') {
            // Recompose full attr spec: <attrType>("<format>")
            $attrType = sprintf('%s("%s")', $attrType, $attr->format);
        }
    }
    
    $methods = $tmpDoc->getSearchMethods($attrId, $attrType);
    $res['data'] = $methods;
    sendResponse($action, $res);
    return;
}

function sendResponse($action, $response)
{
    if (headers_sent($file, $line)) {
        error_log(__METHOD__ . " " . sprintf("Oops... someone (%s:%s) already sent the headers!", $file, $line));
        exit;
    }
    header('Content-Type: application/json');
    $action->lay->template = json_encode($response);
    $action->lay->noparse = true;
}
