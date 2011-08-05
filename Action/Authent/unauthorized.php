<?php

/**
 * unauthorized function for the unauthorized layout
 *
 * @author Anakeen 2009
 * @version $Id: unauthorized.php,v 1.4 2009/01/16 13:33:00 jerome Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
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