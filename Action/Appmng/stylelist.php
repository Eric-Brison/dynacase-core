<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: stylelist.php,v 1.3 2003/08/18 15:46:41 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage APPMNG
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: stylelist.php,v 1.3 2003/08/18 15:46:41 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Appmng/stylelist.php,v $
// ---------------------------------------------------------------
//  O   Anakeen - 2000
// O*O  Anakeen development team
//  O   dev@anakeen.com
// ---------------------------------------------------------------
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
// ---------------------------------------------------------------
// $Log: stylelist.php,v $
// Revision 1.3  2003/08/18 15:46:41  eric
// phpdoc
//
// Revision 1.2  2002/05/27 14:51:30  eric
// ajout gestion des styles
//
// Revision 1.1  2002/01/08 12:41:33  eric
// first
//
// Revision 1.3  2001/10/11 13:59:07  eric
// mise à jour pour libwhat 0.4.8
//
// Revision 1.2  2001/02/26 16:57:14  yannick
// remove tablelayout bug
//
// Revision 1.1  2001/01/29 15:50:59  marianne
// prise en compte de la gestion des parametres
//
// ---------------------------------------------------------------
include_once("Class.Param.php");
// -----------------------------------
function stylelist(&$action) {
// -----------------------------------


 $styleid=GetHttpVars("styleid");


  $action->register("PARAM_ACT","STYLELIST&styleid=$styleid");


    $query = new QueryDb($action->dbaccess,"Style");
  $list = $query->Query(0,0,"TABLE");
  // select the wanted style
    while (list($k,$v)=each($list)) {
	if ($v["name"] == $styleid) $list[$k]["selected"]="selected";
	else $list[$k]["selected"]="";
    }
  $action->lay->SetBlockData("SELSTYLE",$list);
    return;

}
?>
