<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Parameters values
 *
 * @author Anakeen 2000
 * @version $Id: Class.Param.php,v 1.29 2008/11/13 16:43:11 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage CORE
 */
/**
 */

include_once ('Class.Log.php');
include_once ('Class.DbObj.php');
include_once ('Class.ParamDef.php');

define("PARAM_APP", "A");
define("PARAM_GLB", "G");
define("PARAM_USER", "U");
define("PARAM_STYLE", "S");

class Param extends DbObj
{
    var $fields = array(
        "name",
        "type",
        "appid",
        "val"
    );
    
    var $id_fields = array(
        "name",
        "type",
        "appid"
    );
    
    public $name;
    public $type;
    public $appid;
    public $val;
    var $dbtable = "paramv";
    
    var $sqlcreate = '
      create table paramv (
              name   varchar(50) not null,
              type   varchar(21),
              appid  int4,
              val    text);
      create index paramv_idx2 on paramv(name);
      create unique index paramv_idx3 on paramv(name,type,appid);
                 ';
    
    var $buffer = array();
    
    function PreInsert()
    {
        if (strpos($this->name, " ") != 0) {
            return _("Parameter name does not include spaces");
        }
        return '';
    }
    function PostInit()
    {
        $opd = new Paramdef();
        $opd->create();
    }
    function PreUpdate()
    {
        $this->PreInsert();
    }
    
    function SetKey($appid, $userid, $styleid = "0")
    {
        $this->appid = $appid;
        $this->buffer = array_merge($this->buffer, $this->GetAll($appid, $userid, $styleid));
    }
    
    function Set($name, $val, $type = PARAM_GLB, $appid = '')
    {
        global $action;
        if ($action) {
            $action->parent->session->unregister("sessparam" . $appid);
        }
        $this->name = $name;
        $this->val = $val;
        $this->type = $type;
        $pdef = new paramdef($this->dbaccess, $name);
        
        if ($pdef->isAffected()) {
            if ($pdef->isglob == 'Y') {
                $appid = $pdef->appid;
                if ($action) {
                    $action->parent->session->close(); // need to refresh all application parameters
                    $action->parent->session->set(); // reopen current session
                    
                }
            }
        }
        $this->appid = $appid;
        
        $paramt = new Param($this->dbaccess, array(
            $name,
            $type,
            $appid
        ));
        if ($paramt->isAffected()) $err=$this->Modify();
        else $err=$this->Add();
        
        $otype = '';
        if ($type == PARAM_GLB) $otype = PARAM_APP;
        elseif ($type == PARAM_APP) $otype = PARAM_GLB;
        if ($otype) {
            // delete incompatible parameter
            $paramo = new Param($this->dbaccess, array(
                $name,
                $otype,
                $appid
            ));
            if ($paramo->isAffected()) $paramo->delete();
        }
        
        $this->buffer[$name] = $val;
        return $err;
    }
    
    function SetVolatile($name, $val)
    {
        if ($val !== null) $this->buffer[$name] = $val;
        else unset($this->buffer[$name]);
    }
    
    function Get($name, $def = "")
    {
        if (isset($this->buffer[$name])) {
            return ($this->buffer[$name]);
        } else {
            return ($def);
        }
    }
    
    function GetAll($appid = "", $userid, $styleid = "0")
    {
        if ($appid == "") $appid = $this->appid;
        $psize = new Param($this->dbaccess, array(
            "FONTSIZE",
            PARAM_USER . $userid,
            "1"
        ));
        if ($psize->val != '') $size = $psize->val;
        else $size = 'normal';
        $size = 'SIZE_' . strtoupper($size);
        $query = new QueryDb($this->dbaccess, "Param");
        if ($appid) {
            if ($userid) {
                $list = $query->Query(0, 0, "TABLE", "select distinct on(paramv.name) paramv.* from paramv left join paramdef on (paramv.name=paramdef.name) where " . "(paramv.type = '" . PARAM_GLB . "') " . " OR (paramv.type='" . PARAM_APP . "' and paramv.appid=$appid)" . " OR (paramv.type='" . PARAM_USER . $userid . "' and paramv.appid=$appid)" . " OR (paramv.type='" . PARAM_USER . $userid . "' and paramdef.isglob='Y')" . " OR (paramv.type='" . PARAM_STYLE . $styleid . "' and paramv.appid=$appid)" . " OR (paramv.type='" . PARAM_STYLE . $styleid . "' and paramdef.isglob='Y')" . " OR (paramv.type='" . PARAM_STYLE . $size . "')" . " order by paramv.name, paramv.type desc");
            } else {
                $list = $query->Query(0, 0, "TABLE", sprintf("SELECT * from paramv where type='G' or (type='A' and appid=%d);", $appid));
            }
        }
        $out = array();
        if ($query->nb != 0) {
            while (list($k, $v) = each($list)) {
                $out[$v["name"]] = $v["val"];
            }
        } else {
            $this->log->debug("$appid no constant define for this application");
        }
        return ($out);
    }
    
    function GetUser($userid = ANONYMOUS_ID, $styleid = "")
    {
        $query = new QueryDb($this->dbaccess, "Param");
        
        $tlist = $query->Query(0, 0, "TABLE", "select  distinct on(paramv.name, paramv.appid) paramv.*,  paramdef.descr, paramdef.kind  from paramv, paramdef where paramv.name = paramdef.name and paramdef.isuser='Y' and (" . " (type = '" . PARAM_GLB . "') " . " OR (type='" . PARAM_APP . "')" . " OR (type='" . PARAM_STYLE . $styleid . "' )" . " OR (type='" . PARAM_USER . $userid . "' ))" . " order by paramv.name, paramv.appid, paramv.type desc");
        
        return ($tlist);
    }
    /**
     * get list of parameters for a style
     * @param bool $onlystyle if false return all parameters excepts user parameters with style parameters
     * if true return only parameters redifined by the style
     * @return array of parameters values
     */
    function GetStyle($styleid, $onlystyle = false)
    {
        $query = new QueryDb($this->dbaccess, "Param");
        if ($onlystyle) {
            $query->AddQuery("type='" . PARAM_STYLE . $styleid . "'");
            $tlist = $query->Query(0, 0, "TABLE");
        } else {
            $tlist = $query->Query(0, 0, "TABLE", "select  distinct on(paramv.name, paramv.appid) paramv.*,  paramdef.descr, paramdef.kind  from paramv, paramdef where paramv.name = paramdef.name and paramdef.isstyle='Y' and (" . " (type = '" . PARAM_GLB . "') " . " OR (type='" . PARAM_APP . "')" . " OR (type='" . PARAM_STYLE . $styleid . "' ))" . " order by paramv.name, paramv.appid, paramv.type desc");
        }
        return ($tlist);
    }
    
    function GetApps()
    {
        $query = new QueryDb($this->dbaccess, "Param");
        
        $tlist = $query->Query(0, 0, "TABLE", "select  paramv.*, paramdef.descr, paramdef.kind  from paramv, paramdef where paramv.name = paramdef.name and  (" . " (type = '" . PARAM_GLB . "') " . " OR (type='" . PARAM_APP . "'))" . " order by paramv.appid, paramv.name, type desc");
        
        return ($tlist);
    }
    
    function GetUParam($p, $u = ANONYMOUS_ID, $appid = "")
    {
        if ($appid == "") $appid = $this->appid;
        $req = "select val from paramv where name='" . $p . "' and type='U" . $u . "' and appid=" . $appid . ";";
        $query = new QueryDb($this->dbaccess, "Param");
        $tlist = $query->Query(0, 0, "TABLE", $req);
        if ($query->nb != 0) return $tlist[0]["val"];
        return "";
    }
    // delete paramters that cannot be change after initialisation
    function DelStatic($appid)
    {
        
        $query = new QueryDb($this->dbaccess, "Param");
        $sql = sprintf("select paramv.*  from paramv, paramdef where paramdef.name=paramv.name and paramdef.kind='static' and paramdef.isuser!='Y' and paramv.appid=%d", $appid);
        $list = $query->Query(0, 0, "LIST", $sql);
        
        if ($query->nb != 0) {
            reset($list);
            /**
             * @var Param $v
             */
            foreach ($list as $k => $v) {
                $v->Delete();
                if (isset($this->buffer[$v->name])) unset($this->buffer[$v->name]);
            }
        }
    }
    
    function PostDelete()
    {
        if (isset($this->buffer[$this->name])) unset($this->buffer[$this->name]);
    }
    
    function DelAll($appid = "")
    {
        $query = new QueryDb($this->dbaccess, "Param");
        // delete all parameters not used by application
        $query->Query(0, 0, "TABLE", "delete from paramv where appid not in (select id from application) ");
        return;
    }
    // FIN DE CLASSE
    
}
?>