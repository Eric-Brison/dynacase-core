<?php

include_once ('WHAT/Class.Authenticator.php');

class apacheAuthenticator extends Authenticator
{
    function checkAuthentication()
    {
        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            header('HTTP/1.0 403 Forbidden');
            echo _("User must be authenticate");
            echo "\nApache authentication module does not seems to be active.";
            return self::AUTH_NOK;
        }
        return self::AUTH_OK;
    }

    function checkAuthorization($opt)
    {
        return TRUE;
    }

    function askAuthentication($args)
    {
        header('HTTP/1.0 403 Forbidden');
        echo _("User must be authenticate");
        echo "\nApache authentication module does not seems to be active.";
    }

    function getAuthUser()
    {
        return $_SERVER['PHP_AUTH_USER'];
    }

    function getAuthPw()
    {
        return $_SERVER['PHP_AUTH_PW'];
    }

    function logout($redir_uri = '')
    {
        if ($redir_uri == '') {
            $pUri = parse_url($_SERVER['REQUEST_URI']);
            if (preg_match(':(?P<path>.*/)[^/]*$:', $pUri['path'], $m)) {
                $redir_uri = $m['path'];
            }
        }
        header('Location: ' . $redir_uri);
        return true;
    }

    function setSessionVar($name, $value)
    {
        return true;
    }

    function getSessionVar($name)
    {
        return '';
    }
}