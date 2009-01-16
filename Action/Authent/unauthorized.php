<?php

/**
 * unauthorized function for the unauthorized layout
 *
 * @author Anakeen 2009
 * @version $Id: unauthorized.php,v 1.4 2009/01/16 13:33:00 jerome Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage
 */
 /**
 */

function unauthorized(&$action) {
  $action->lay->set("msg", "Vous n'êtes pas autorisé à consulter cette ressource.");

  echo $action->lay->gen();

  $action->session->close();

  exit(0);
}

?>