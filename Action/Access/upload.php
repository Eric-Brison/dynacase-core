<?php
// ---------------------------------------------------------------
// $Id: upload.php,v 1.1 2002/01/08 12:41:33 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Access/upload.php,v $
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
// $Log: upload.php,v $
// Revision 1.1  2002/01/08 12:41:33  eric
// first
//
// Revision 1.3  2001/10/08 12:11:19  eric
// correction import/export du aux modifs accessibilité (positif/négatif)
//
// Revision 1.2  2001/08/20 16:48:58  eric
// changement des controles d'accessibilites
//
// Revision 1.1  2000/10/24 17:15:22  yannick
// Import/Export
//
// Revision 1.2  2000/10/23 12:36:04  yannick
// Ajout de l'acces aux applications
//
// Revision 1.1  2000/10/23 09:10:27  marc
// Mise au point des utilisateurs
//
// Revision 1.1.1.1  2000/10/21 16:44:39  yannick
// Importation initiale
//
// Revision 1.2  2000/10/19 16:47:23  marc
// Evo TableLayout
//
// Revision 1.1.1.1  2000/10/19 10:35:49  yannick
// Import initial
//
//
//
// ---------------------------------------------------------------
include_once("Class.QueryDb.php");
include_once("Class.Application.php");
include_once("Class.User.php");
include_once("Class.Acl.php");
include_once("Class.Domain.php");
include_once("Class.Permission.php");
include_once("Lib.Http.php");

// -----------------------------------
function upload(&$action) {
// -----------------------------------

  global $HTTP_POST_FILES;
  $action->log->debug("UPLOAD");
  // select the first user if not set
  // What user are we working on ? ask session.
  $filename = ($HTTP_POST_FILES["upfile"]["tmp_name"]);


  if (!file_exists($filename)) {
    $action->ExitError("File not found : $filename : ".$HTTP_POST_FILES["upfile"]["name"]);
  }
  $content = file($filename);


  $prev_userid=0; // to detect change user & application
  $prev_appid=0; 


  $tnewacl=array();  
  while (list($k,$v) = each($content)) {
    $col = explode("|",$v);
    if (! is_array($col)) continue;
    if (count($col) != 3) continue;
    if (substr($v, 0, 1) == "#") continue; // comment line


    $app= new Application($action->dbaccess);
    $app->Set($col[0],$action->parent);

    
    $usedom = explode("@",$col[1]);

    $use= new User($action->dbaccess);
    $domain = new Domain($action->dbaccess);
    $domain-> Set($usedom[1]);

    $use->SetLogin($usedom[0],$domain->iddomain);

    if (($prev_userid > 0) && ($prev_appid > 0) &&
	(($use->id != $prev_userid ) ||
	 ($app->id != $prev_appid ))) {
      // update the permission in database
      // first remove then add
      $perm = new Permission($action->dbaccess,
      array($prev_userid,
      $prev_appid)); 
      if (! $perm-> IsAffected()) {
	$perm->Affect(array("id_user" => $prev_userid,
			    "id_application" =>$prev_appid ));
      } 

      if (count($tnewacl) > 0) {
	$perm->Delete();
	foreach ($tnewacl as $aclid ) {
	  $perm->id_acl = $aclid;
	  
	  
	  if ($aclid != 0) {
	    //print "ADD "."-".$perm->id_application."-". $perm->id_user."-". $perm->id_acl."<BR>";
	    $perm->Add();
	  }
	  
	}
      }
      $tnewacl=array();  // new array for new user
    }
    
    // update for next line
    $prev_userid = $use->id;
    $prev_appid = $app->id;



    //    print "<pre>         $v</pre><BR>";
    if (chop($col[2]) !="NONE") {
      $acl = new Acl($action->dbaccess);
      $aclname=chop($col[2]);
      $unp = false; // is negative privilege ?
      if (substr($aclname, 0, 1) == "-") {
	$aclname=substr($aclname, 1);
	$unp = true;
      };


      // search acl id
      $acl-> Set($aclname,$app->id);

      if ($acl->id == "") continue;      

      if ($unp) {
	$tnewacl[] = -$acl->id;
      } else {
	$tnewacl[] = $acl->id;

      }

      
    } else {
      $tnewacl[] = 0;
      
      
    }
     
  }


  redirect($action,"ACCESS","USER_ACCESS");
}
?>
