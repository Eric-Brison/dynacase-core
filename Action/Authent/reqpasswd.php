<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * reqpasswd function for the reqpasswd layout
 *
 * @author Anakeen
 * @version $Id: reqpasswd.php,v 1.2 2009/01/16 13:33:00 jerome Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

function reqpasswd(Action & $action)
{
    $action->parent->AddCssRef('AUTHENT:loginform.css', true);
    $action->parent->AddCssRef('AUTHENT:reqpasswd.css');
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/resizeimg.js");
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/geometry.js");
    $action->parent->addJsRef("AUTHENT:loginform.js", true);
    $lang = $action->getArgument("lang");

    $smtpfrom=$action->getParam("SMTP_FROM");
    $smtpport=$action->getParam("SMTP_PORT");
    $smtphost=$action->getParam("SMTP_HOST");
    if (!$smtpfrom || !$smtpport || !$smtphost) {
        $action->exitError(___("SMTP server not configured","authent"));
    }

    $action->lay->eSet("lang", $lang);
    setLanguage($lang);
    return "";
}
