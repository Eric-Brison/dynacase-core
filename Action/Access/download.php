<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: download.php,v 1.5 2003/08/18 15:46:41 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage ACCESS
 */
/**
 */
// ---------------------------------------------------------------
// $Id: download.php,v 1.5 2003/08/18 15:46:41 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Access/download.php,v $
// ---------------------------------------------------------------
include_once ("Class.QueryDb.php");
include_once ("Class.Application.php");
include_once ("Class.User.php");
include_once ("Class.Acl.php");
include_once ("Class.Permission.php");
include_once ("Lib.Http.php");
include_once ("FDL/freedom_util.php");
// -----------------------------------
function download(&$action)
{
    // -----------------------------------
    $dbaccess_freedom = $action->getParam('FREEDOM_DB');
    $dbaccess_core = $action->getParam('CORE_DB');
    
    $cache = array(
        'app' => array() ,
        'acl' => array() ,
        'user' => array()
    );
    
    $q = new QueryDb($dbaccess_core, "permission");
    $aclList = $q->query(0, 0, "TABLE", sprintf("SELECT id_user, id_application, id_acl FROM permission WHERE computed IS NULL OR computed = FALSE;"));
    
    $aclExport = array();
    foreach ($aclList as $k => & $el) {
        $app_name = getApplicationNameFromId($dbaccess_core, $el['id_application'], $cache);
        if ($app_name === null) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " " . sprintf("Unknown name for application with id '%s'", $el['id_application']));
            continue;
        }
        
        $acl_name = getAclNameFromId($dbaccess_core, $el['id_acl'], $cache);
        if ($acl_name === null) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " " . sprintf("Uknown name for acl with id '%s'", $el['id_acl']));
            continue;
        }
        if ($el['id_acl'] < 0) {
            $acl_name = sprintf("-%s", $acl_name);
        }
        // Try to fetch the logical name of id_user
        $user_fid = getUserFIDFromWID($dbaccess_core, $el['id_user'], $cache);
        if ($user_fid === null) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " " . sprintf("Unknown fid for user with wid '%s'", $el['id_user']));
            continue;
        }
        $user_name = getNameFromId($dbaccess_freedom, $user_fid);
        // If there is no logical name, then keep the core id (id_user)
        if ($user_name == "") {
            $user_name = $el['id_user'];
        }
        
        array_push($aclExport, array(
            'fid' => $user_name,
            'app_name' => $app_name,
            'acl_name' => $acl_name
        ));
    }
    
    $action->lay->setBlockData("ACCESS", $aclExport);
    
    $tmpfile = tempnam(getTmpDir() , "access");
    if ($tmpfile === false) {
        $err = sprintf("Could not create temporary file!");
        error_log(__CLASS__ . "::" . __FUNCTION__ . " " . $err);
        return $err;
    }
    
    @unlink($tmpfile);
    $fp = @fopen($tmpfile, 'x');
    if ($fp === false) {
        $err = sprintf("Error opening temporary file '%s'", $tmpfile);
        error_log(__CLASS__ . "::" . __FUNCTION__ . " " . $err);
        return $err;
    }
    
    $content = $action->lay->gen();
    
    $ret = @fwrite($fp, $content);
    if ($ret === false) {
        $err = sprintf("Error writing to temporary file '%s'", $tmpfile);
        error_log(__CLASS__ . "::" . __FUNCTION__ . " " . $err);
        @fclose($fp);
        @unlink($tmpfile);
        return $err;
    }
    @fclose($fp);
    
    return Http_DownloadFile($tmpfile, "access.csv", "text/csv",
    /*inline*/
    false, /*cache*/
    false, /*deleteafter*/
    true);
}

function getApplicationNameFromId($dbaccess, $id, &$cache = null)
{
    if (is_array($cache) && array_key_exists('app', $cache)) {
        if (array_key_exists($id, $cache['app'])) {
            return $cache['app'][$id];
        }
    }
    
    $query = new QueryDb($dbaccess, "application");
    $query->addQuery(sprintf("id = %s", pg_escape_string($id)));
    $res = $query->query(0, 0, "TABLE");
    if (!is_array($res)) {
        return null;
    }
    
    $name = $res[0]['name'];
    if (is_array($cache) && array_key_exists('app', $cache)) {
        $cache['app'][$id] = $name;
    }
    
    return $name;
}

function getAclNameFromId($dbaccess, $id, &$cache = null)
{
    if (is_array($cache) && array_key_exists('acl', $cache)) {
        if (array_key_exists($id, $cache['acl'])) {
            return $cache['acl'][$id];
        }
    }
    
    $query = new QueryDb($dbaccess, "acl");
    $query->addQuery(sprintf("id = %s", pg_escape_string(abs($id))));
    $res = $query->query(0, 0, "TABLE");
    if (!is_array($res)) {
        return null;
    }
    
    $name = $res[0]['name'];
    if (is_array($cache) && array_key_exists('acl', $cache)) {
        $cache['acl'][$id] = $name;
    }
    
    return $name;
}

function getUserFIDFromWID($dbaccess, $wid, &$cache)
{
    if (is_array($cache) && array_key_exists('user_fid', $cache)) {
        if (array_key_exists($id, $cache['user_fid'])) {
            return $cache['user_fid'][$id];
        }
    }
    
    $query = new QueryDb($dbaccess, "user");
    $query->addQuery(sprintf("id = %s", pg_escape_string($wid)));
    $res = $query->query(0, 0, "TABLE");
    if (!is_array($res)) {
        return null;
    }
    
    $fid = $res[0]['fid'];
    if (is_array($cache) && array_key_exists('user_fid', $cache)) {
        $cache['user_fid'][$wid] = $fid;
    }
    
    return $fid;
}
?>
