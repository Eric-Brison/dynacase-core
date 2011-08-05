<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000
 * @version $Id: Class.Style.php,v 1.5 2003/08/18 15:46:42 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage CORE
 */
/**
 */

include_once ('Class.DbObj.php');
include_once ('Class.QueryDb.php');
include_once ('Class.Param.php');

class Style extends DbObj
{
    var $fields = array(
        "name",
        "description",
        "parsable"
    );
    
    var $id_fields = array(
        "name"
    );
    
    var $sqlcreate = "
    create table style (
        name text not null,
        primary key (name),
        description text,
        parsable char default 'N'
    );
    create sequence SEQ_ID_STYLE start 10000;
";
    
    var $dbtable = "style";
    
    function Set(&$parent)
    {
        $this->parent = & $parent;
    }
    
    function GetImageUrl($img, $default)
    {
        $root = $this->parent->Getparam("CORE_PUBDIR");
        
        $socStyle = $this->parent->Getparam("CORE_SOCSTYLE");
        // first see if i have an society style
        if (($socStyle != "") && file_exists($root . "/STYLE/" . $socStyle . "/Images/" . $img)) {
            return ("STYLE/" . $socStyle . "/Images/" . $img);
        }
        
        if (file_exists($root . "/STYLE/" . $this->name . "/Images/" . $img)) {
            return ("STYLE/" . $this->name . "/Images/" . $img);
        } else {
            return ($default);
        }
    }
    
    function GetLayoutFile($layname, $default = "")
    {
        $root = $this->parent->Getparam("CORE_PUBDIR");
        
        $socStyle = $this->parent->Getparam("CORE_SOCSTYLE");
        // first see if i have an society style
        if ($socStyle != "") {
            $file = $root . "/STYLE/" . $socStyle . "/Layout/" . $layname;
            if (file_exists($file)) return ($file);
        }
        
        $file = $root . "/STYLE/" . $this->name . "/Layout/" . $layname;
        if (file_exists($file)) return ($file);
        
        return ($default);
    }
}
?>
