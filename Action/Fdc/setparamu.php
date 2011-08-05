<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Get Values in XML form
 *
 * @author Anakeen 2006
 * @version $Id: setparamu.php,v 1.4 2008/06/10 15:00:14 jerome Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage FDC
 */
/**
 */

include_once ("Class.Param.php");
/**
 * set an user attribute value
 * @param Action &$action current action
 * @global appname Http var : application name of the attribute
 * @global parname Http var : parameters name
 * @global parval Http var : new value
 */
function setparamu(&$action)
{
    header('Content-type: text/xml; charset=utf-8');
    
    $mb = microtime();
    $appname = GetHttpVars("appname");
    $parname = GetHttpVars("parname");
    
    $parval = GetHttpVars("parval");
    $dbaccess = $action->GetParam("FREEDOM_DB");
    
    $action->lay->set("warning", "");
    
    $appid = $action->parent->GetIdFromName($appname);
    
    $pdef = new QueryDb("", "ParamDef");
    $pdef->AddQuery("name='" . pg_escape_string($parname) . "'");
    $pdef->AddQuery("isuser='Y'");
    $pdef->AddQuery("appid=$appid");
    $list = $pdef->Query(0, 2);
    if ($pdef->nb == 0) {
        $err = sprintf(_("Attribute %s not found\n") , $parname);
    } elseif ($pdef->nb > 1) {
        $err = sprintf(_("Attribute %s found is not alone\nMust precise request with appname arguments\n") , $parname);
    } else {
        
        $param = new QueryDb("", "Param");
        $param->AddQuery("name='" . pg_escape_string($parname) . "'");
        $param->AddQuery("type='" . PARAM_USER . $action->user->id . "'");
        $param->AddQuery("appid=$appid");
        $list = $param->Query(0, 1);
        if ($param->nb == 0) {
            $p = new Param("");
            $p->name = $parname;
            $p->type = PARAM_USER . $action->user->id;
            $p->appid = $appid;
        } else {
            $p = $list[0];
        }
        
        $p->Set($p->name, $parval, $p->type, $p->appid);
    }
    
    if ($err) $action->lay->set("warning", $err);
    
    $action->lay->set("CODE", "OK");
    $action->lay->set("count", 1);
    $action->lay->set("delay", microtime_diff(microtime() , $mb));
}
