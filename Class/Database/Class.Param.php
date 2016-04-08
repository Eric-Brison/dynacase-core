<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Parameters values
 *
 * @author Anakeen
 * @version $Id: Class.Param.php,v 1.29 2008/11/13 16:43:11 eric Exp $
 * @package FDL
 * @subpackage CORE
 */
/**
 */

include_once ('Class.Log.php');
include_once ('Class.DbObj.php');
include_once ('Class.ParamDef.php');
/** @deprecated use Param::PARAM_APP instead */
define("PARAM_APP", "A");
/** @deprecated use Param::PARAM_GLB instead */
define("PARAM_GLB", "G");
/** @deprecated use Param::PARAM_USER instead */
define("PARAM_USER", "U");
/** @deprecated use Param::PARAM_STYLE instead */
define("PARAM_STYLE", "S");

class Param extends DbObj
{
    
    const PARAM_APP = "A";
    const PARAM_GLB = "G";
    const PARAM_USER = "U";
    const PARAM_STYLE = "S";
    
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
    
    function Set($name, $val, $type = self::PARAM_GLB, $appid = '')
    {
        global $action;
        if ($action) {
            $action->parent->session->unregister("sessparam" . $appid);
        }
        $this->name = $name;
        $this->val = $val;
        $this->type = $type;
        
        $pdef = ParamDef::getParamDef($name, $appid);
        
        if ($pdef && $pdef->isAffected()) {
            if ($pdef->isglob == 'Y') {
                $appid = $pdef->appid;
            }
        }
        $this->appid = $appid;
        
        $paramt = new Param($this->dbaccess, array(
            $name,
            $type,
            $appid
        ));
        if ($paramt->isAffected()) $err = $this->Modify();
        else $err = $this->Add();
        
        $otype = '';
        if ($type == self::PARAM_GLB) $otype = self::PARAM_APP;
        elseif ($type == self::PARAM_APP) $otype = self::PARAM_GLB;
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
        require_once ('WHAT/Class.ApplicationParameterManager.php');
        
        if (($value = ApplicationParameterManager::_catchDeprecatedGlobalParameter($name)) !== null) {
            return $value;
        }
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
            self::PARAM_USER . $userid,
            "1"
        ));
        $out = array();
        if ($psize->val != '') $size = $psize->val;
        else $size = 'normal';
        $size = 'SIZE_' . strtoupper($size);
        
        if ($appid) {
            if ($userid) {
                $styleIdPG = pg_escape_string($styleid);
                $sql = sprintf("select distinct on(paramv.name) paramv.* from paramv left join paramdef on (paramv.name=paramdef.name) where
(paramv.type = '%s')  OR (paramv.appid=%d and (paramv.type='%s' or paramv.type='%s%d' or paramv.type='%s%s')) OR (paramdef.isglob='Y' and (paramv.type='%s%d' or paramv.type='%s%s')) OR
(paramv.type='%s%s') order by paramv.name, paramv.type desc", self::PARAM_GLB, $appid, self::PARAM_APP, self::PARAM_USER, $userid, self::PARAM_STYLE, $styleIdPG, self::PARAM_USER, $userid, self::PARAM_STYLE, $styleIdPG, self::PARAM_STYLE, pg_escape_string($size));
            } else {
                $sql = sprintf("SELECT * from paramv where type='G' or (type='A' and appid=%d);", $appid);
            }
            simpleQuery($this->dbaccess, $sql, $list);
            
            foreach ($list as $v) {
                $out[$v["name"]] = $v["val"];
            }
        } else {
            $this->log->debug("$appid no constant define for this application");
        }
        return ($out);
    }
    
    function GetUser($userid = Account::ANONYMOUS_ID, $styleid = "")
    {
        $query = new QueryDb($this->dbaccess, "Param");
        
        $tlist = $query->Query(0, 0, "TABLE", "select  distinct on(paramv.name, paramv.appid) paramv.*,  paramdef.descr, paramdef.kind  from paramv, paramdef where paramv.name = paramdef.name and paramdef.isuser='Y' and (" . " (type = '" . self::PARAM_GLB . "') " . " OR (type='" . self::PARAM_APP . "')" . " OR (type='" . self::PARAM_STYLE . $styleid . "' )" . " OR (type='" . self::PARAM_USER . $userid . "' ))" . " order by paramv.name, paramv.appid, paramv.type desc");
        
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
            $query->AddQuery("type='" . self::PARAM_STYLE . $styleid . "'");
            $tlist = $query->Query(0, 0, "TABLE");
        } else {
            $tlist = $query->Query(0, 0, "TABLE", "select  distinct on(paramv.name, paramv.appid) paramv.*,  paramdef.descr, paramdef.kind  from paramv, paramdef where paramv.name = paramdef.name and paramdef.isstyle='Y' and (" . " (type = '" . self::PARAM_GLB . "') " . " OR (type='" . self::PARAM_APP . "')" . " OR (type='" . self::PARAM_STYLE . $styleid . "' ))" . " order by paramv.name, paramv.appid, paramv.type desc");
        }
        return ($tlist);
    }
    
    function GetApps()
    {
        $query = new QueryDb($this->dbaccess, "Param");
        
        $tlist = $query->Query(0, 0, "TABLE", "select  paramv.*, paramdef.descr, paramdef.kind  from paramv, paramdef where paramv.name = paramdef.name and  (" . " (type = '" . self::PARAM_GLB . "') " . " OR (type='" . self::PARAM_APP . "'))" . " order by paramv.appid, paramv.name, type desc");
        
        return ($tlist);
    }
    
    function GetUParam($p, $u = Account::ANONYMOUS_ID, $appid = "")
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
