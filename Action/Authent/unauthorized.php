<?php

function unauthorized(&$action) {
  $action->lay->set("msg", "Vous n'êtes pas autorisé à consulter cette ressource.");
  return;
}

?>