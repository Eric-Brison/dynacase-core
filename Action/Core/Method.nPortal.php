<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: Method.nPortal.php,v 1.1 2005/10/25 08:39:35 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */
var $defaultview= "FDL:VIEWBODYCARD:U";
var $defaultedit= "FDL:EDITBODYCARD:U";


function postCreated() {
    $this->SetProfil($this->id);
    $this->SetControl();
    $err = $this->Modify();
}

?>