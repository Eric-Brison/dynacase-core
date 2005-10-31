<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: ng_mail.php,v 1.3 2005/10/31 14:05:56 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
include_once("CORE/Lib.Ng.php");

function ng_mail(&$action) {

  $myportal = ng_myportal();
  $mailbox = $myportal->MailAccounts();
  $mbx = array();
  while (list($k,$v) = each($mailbox)) {
    $mbx[] = array( "imbox" => $k,
		    "name" => $v[0],
		    "account" => $v[3] );
  }
  $action->lay->setBlockData("MBOX", $mbx);
}

?>