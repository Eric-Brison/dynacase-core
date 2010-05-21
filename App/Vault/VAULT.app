<?php
// ---------------------------------------------------------------
// $Id: VAULT.app,v 1.8 2008/02/26 13:49:05 marc Exp $
// $Source: /home/cvsroot/anakeen/freedom/vault/VAULT.app,v $
// ---------------------------------------------------------------
//  O   Anakeen - 2001
// O*O  Anakeen development team
//  O   dev@anakeen.com
// ---------------------------------------------------------------

// ---------------------------------------------------------------
$app_desc = array (
"name"		=>"VAULT",		//Name
"short_name"	=>N_("Vault"),		//Short name
"description"	=>N_("Vault Management"),//long description
"access_free"	=>"N",			//Access free ? (Y,N)
"icon"		=>"vault.gif",	//Icon
"displayable"	=>"Y",			//Should be displayed on an app list (Y,N)
"with_frame"	=>"Y",			//Use multiframe ? (Y,N)
"iorder"        =>140
);

$app_acl = array (
  array(
   "name"		=>"VAULT_MASTER",
   "description"	=>N_("Vault manager"),
   "admin"		=>TRUE),
  array(
   "name"               =>"VAULT_USER",
   "description"        =>N_("Vault user"),
   "group_default"       =>"Y")
);

$action_desc = array (
  array( 
   "name"		=>"VAULT_VIEW",
   "short_name"		=>N_("analyze vaults occupation"),
   "acl"		=>"VAULT_MASTER",
   "root"		=>"Y"
  ),
  array( 
   "name"		=>"VAULT_SHOWFS",
   "short_name"		=>N_("show vaults occupation"),
   "script"             =>"vault_view.php",
   "function"           =>"vault_view",
   "acl"		=>"VAULT_USER",
  ),
  array( 
   "name"		=>"VAULT_CREATEFS",
   "short_name"		=>N_("create new vault"),
   "acl"		=>"VAULT_MASTER"
  ),
  array( 
   "name"		=>"VAULT_CLEAN",
   "short_name"		=>N_("delete orphan"),
   "acl"		=>"VAULT_MASTER"
  ),
  array( 
   "name"		=>"VAULT_INCREASEFS",
   "short_name"		=>N_("increase size vault"),
   "acl"		=>"VAULT_MASTER"
  ),
  array( 
   "name"		=>"VAULT_DISKIMAGE",
   "short_name"		=>N_("view image for free size"),
   "acl"		=>"VAULT_USER"
  ),
  array( 
   "name"		=>"VAULT_MOVEFS",
   "short_name"		=>N_("move root directory vault"),
   "acl"		=>"VAULT_MASTER"
  )
                      );
   
?>
