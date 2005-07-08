<?php
/**
 * update list of available style
 *
 * analyze sub-directories presents in STYLE directory
 * @author Anakeen 2002
 * @version $Id: import_style.php,v 1.3 2005/07/08 15:29:51 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage WSH
 */
/**
 */
// ---------------------------------------------------------------
// $Id: import_style.php,v 1.3 2005/07/08 15:29:51 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Api/import_style.php,v $
// ---------------------------------------------------------------
//  O   Anakeen - 2001
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


include_once("Class.Style.php");

$name = GetHttpVars("name");

$param = new Param();

if (file_exists($action->GetParam("CORE_PUBDIR",DEFAULT_PUBDIR)."/STYLE/{$name}/{$name}.sty")) {
     global $sty_desc,$sty_const;
     include("STYLE/{$name}/{$name}.sty");
     if (sizeof($sty_desc)>0) {
       $sty = new Style("",$name);
       reset($sty_desc);
       while (list($k,$v) = each ($sty_desc)) {
         $sty->$k = $v;
       }
       if (! $sty->isAffected()) $sty->Add();
       $sty->Modify();

     } 

     // delete first old parameters
     $query=new QueryDb("", "Param");
     $query->AddQuery("type='".PARAM_STYLE.$name."'");
     $list=$query->Query();
     if ($query->nb> 0) {       
       while(list($k,$v)=each($list)) {
	 $v->delete();
       }
     }
     // init param
     if (isset($sty_const)) {
       reset($sty_const);
       while (list($k,$v) = each ($sty_const)) {
            $param->Set($k,$v,PARAM_STYLE.$name,1);
       }
     }
     

     // update style list for STYLE parameter definition
       
     $query=new QueryDb("", "Style");
     $list=$query->Query();
     if ($query->nb> 0) {   
       $ndef="enum(";    
       while(list($k,$v)=each($list)) {
	 $ndef .= $v->name."|";
       }
       $ndef = substr($ndef,0,-1).")";
     } else $ndef="";

     $pdef = new ParamDef("","STYLE");
     $pdef->kind=$ndef;
     $pdef->modify();

   }
?>