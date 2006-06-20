<?php
/**
 * update list of available font style
 *
 * analyze sub-directories presents in STYLE directory
 * @author Anakeen 2002
 * @version $Id: import_size.php,v 1.1 2006/06/20 16:18:07 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage WSH
 */
/**
 */
// ---------------------------------------------------------------
// $Id: import_size.php,v 1.1 2006/06/20 16:18:07 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Api/import_size.php,v $
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
include_once("FDL/Lib.Color.php");

$name = GetHttpVars("name");

$param = new Param();

if (file_exists($action->GetParam("CORE_PUBDIR",DEFAULT_PUBDIR)."/WHAT/size.php")) {
      global $size;
     include("WHAT/size.php");
 
     /*
     // delete first old parameters
     $query=new QueryDb("", "Param");
     $query->AddQuery("type='".PARAM_STYLE.$name."'");
     $list=$query->Query();
     if ($query->nb> 0) {       
       while(list($k,$v)=each($list)) {
	 $v->delete();
       }
     }
     */
     
     if (isset($size)) {
       // compute all fonct size
       foreach ($size as $k=>$v) {

	 $stylename="SIZE_".strtoupper($k);

	 print "stylename=$stylename\n";
	 $sty = new Style("",$stylename);

       
	 foreach ($v as $kf=>$vf) {
	   $kn="SIZE_".strtoupper($kf);
	   if ($k == "normal") $param->Set($kn,$vf,PARAM_GLB,1); // put in default 
	   $param->Set($kn,$vf,PARAM_STYLE.$stylename,1);
	   $action->parent->SetVolatileParam($kn, $vf); // to compose css with new paramters
	 }

	 if (! $sty->isAffected()) {
	   $sty->name=$stylename;
	   $sty->Add();
	 } else	 $sty->Modify();

	 $inputlay=new Layout("WHAT/Layout/size.css",$action);
	 $out=$inputlay->gen();	 
	 file_put_contents($action->GetParam("CORE_PUBDIR")."/WHAT/Layout/size-$k.css",$out);
       }
     }

    }

   
?>