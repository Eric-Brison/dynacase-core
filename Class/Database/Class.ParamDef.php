<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: Class.ParamDef.php,v 1.4 2005/10/31 11:52:17 eric Exp $
 * @package FDL
 * @subpackage CORE
 */
/**
 */
// ---------------------------------------------------------------------------
// Param
// ---------------------------------------------------------------------------
// Anakeen 2000 - yannick.lebriquer@anakeen.com
// ---------------------------------------------------------------------------
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or (at
//  your option) any later version.
//
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
// or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
// for more details.
//
// You should have received a copy of the GNU General Public License along
// with this program; if not, write to the Free Software Foundation, Inc.,
// 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
// ---------------------------------------------------------------------------
//  $Id: Class.ParamDef.php,v 1.4 2005/10/31 11:52:17 eric Exp $
//
include_once ('Class.Log.php');
include_once ('Class.DbObj.php');

$CLASS_PARAMDEF_PHP = '$Id: Class.ParamDef.php,v 1.4 2005/10/31 11:52:17 eric Exp $';

class ParamDef extends DbObj
{
    var $fields = array(
        "name",
        "isuser",
        "isstyle",
        "isglob",
        "appid",
        "descr",
        "kind"
    );
    public $name;
    public $isuser;
    public $isstyle;
    public $isglob;
    public $appid;
    public $descr;
    public $kind;
    
    var $id_fields = array(
        "name",
        "appid"
    );
    
    var $dbtable = "paramdef";
    
    var $sqlcreate = '
      create table paramdef (
              name    text,
              isuser   varchar(1),
              isstyle   varchar(1),
              isglob   varchar(1),
              appid  int4,
              descr    text,
              kind    text);
      create unique index paramdef_idxna on paramdef(name, appid);
                 ';
    /**
     * get Param def object from name
     * @param string $name parameter name
     * @param int $appid application id
     * @return ParamDef
     */
    public static function getParamDef($name, $appid = null)
    {
        $d = null;
        if ($appid == null) {
            simpleQuery('', sprintf("select * from paramdef where name='%s'", pg_escape_string($name)) , $paramDefValues, false, true);
        } else {
            $sql = <<< 'SQL'
            SELECT * from paramdef
            where name='%s'
              and (isglob='Y' or appid=%d or appid=1 or appid=(select id from application where name=(select childof from application where id=%d)));
SQL;
            $sqlp = sprintf($sql, pg_escape_string($name) , $appid, $appid);
            simpleQuery('', $sqlp, $paramDefValues, false, true);
        }
        if (!empty($paramDefValues)) {
            $d = new ParamDef();
            $d->Affect($paramDefValues);
        }
        return $d;
    }
}
?>
