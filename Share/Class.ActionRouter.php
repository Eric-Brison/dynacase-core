<?php
/*
 * @author Anakeen
 * @package FDL
*/

require_once ('WHAT/autoload.php');

include_once ('WHAT/Lib.Main.php');

class ActionRouter
{
    /**
     * @var Action
     */
    private $action = null;
    
    public function __construct(Account & $account, Authenticator & $auth = null)
    {
        global $action;
        $this->action = null;
        $_SERVER['PHP_AUTH_USER'] = $account->login;
        getMainAction($auth, $this->action);
        $action = $this->action;
    }
    
    public function getAction()
    {
        return $this->action;
    }
    
    public static function inMaintenance()
    {
        return file_exists(DEFAULT_PUBDIR . DIRECTORY_SEPARATOR . 'maintenance.lock');
    }
    
    public function executeAction(&$capturedOutput = null)
    {
        executeAction($this->action, $capturedOutput);
    }
}
