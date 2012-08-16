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
 * @version $Id: Class.ParamDef.php,v 1.4 2005/10/31 11:52:17 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
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
        "name"
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
      create unique index paramdef_idx1 on paramdef(name);
                 ';
}
?>
