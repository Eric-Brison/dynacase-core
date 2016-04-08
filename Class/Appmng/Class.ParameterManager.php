<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Manage application parameters
 * Set and get application parameters
 * @class ParameterManager
 *
 * @see ApplicationParameterManager
 *
 * @deprecated use { @link ApplicationParameterManager } instead
 *
 * @code
 * $v=ParameterManager::getParameter("CORE_CLIENT");
 * @endcode
 */
class ParameterManager
{
    private static $cache = array();
    /**
     * @return Action|null
     */
    private static function &getAction()
    {
        global $action;
        return $action;
    }
    /**
     * for internal purpose only
     * @deprecated use { @link ApplicationParameterManager } instead
     */
    public static function resetCache()
    {
        deprecatedFunction();
        self::$cache = array();
    }
    /**
     * return current value for a parameter
     * these values can depend of current user when it is a user parameter
     * @deprecated use { @link ApplicationParameterManager } instead
     *
     * @param string $name paramter name
     * @return string|null value of parameter (null if parameter not exists)
     */
    public static function getParameter($name)
    {
        deprecatedFunction();
        return getParam($name);
    }
    /**
     * get a specific value for an application
     * get user value for current user if it is a user parameter
     * @deprecated use { @link ApplicationParameterManager } instead
     * @param string $appName application name
     * @param string $name paramter
     * @return string|null value of parameter (null if parameter not exists)
     */
    public static function getApplicationParameter($appName, $name)
    {
        deprecatedFunction();
        self::getParameter('a');
        if (!isset(self::$cache[$appName])) {
            $sql = sprintf("select paramv.name,paramv.val from paramv, application where application.name='%s' and application.id=paramv.appid and paramv.type !~ '^U';", pg_escape_string($appName));
            simpleQuery('', $sql, $r);
            $t = array();
            foreach ($r as $values) {
                $t[$values["name"]] = $values["val"];
            }
            $uid = getCurrentUser()->id;
            $sql = sprintf("select paramv.name,paramv.val from paramv, application where application.name='%s' and application.id=paramv.appid and paramv.type = 'U%d';", pg_escape_string($appName) , $uid);
            
            simpleQuery('', $sql, $r);
            foreach ($r as $values) {
                $t[$values["name"]] = $values["val"];
            }
            self::$cache[$appName] = $t;
        }
        return isset(self::$cache[$appName][$name]) ? self::$cache[$appName][$name] : null;
    }
    /**
     * set new Value for a global application parameter
     * @deprecated use { @link ApplicationParameterManager } instead
     * @param string $name parameter name
     * @param string $value new value to set
     * @throws Dcp\PMGT\Exception
     */
    public static function setGlobalParameter($name, $value)
    {
        deprecatedFunction();
        // verify parameter exists
        $sql = sprintf("select paramdef.*, application.name as appname from paramdef, application where paramdef.isglob='Y' and application.id=paramdef.appid and paramdef.name='%s';", pg_escape_string($name));
        simpleQuery('', $sql, $r, false, true);
        if (empty($r)) {
            throw new \Dcp\PMGT\Exception("PMGT0003", $name);
        }
        self::setApplicationTypeParameter($r["isglob"] == "Y" ? PARAM_GLB : PARAM_APP, $r["appname"], $r["appid"], $r["name"], $value);
    }
    /**
     * set new Value for an application parameter
     * @deprecated use { @link ApplicationParameterManager } instead
     * @param string $appName application name
     * @param string $name parameter name
     * @param string $value new value to set
     * @throws Dcp\PMGT\Exception
     */
    public static function setApplicationParameter($appName, $name, $value)
    {
        deprecatedFunction();
        // verify if parameter exists
        $sql = sprintf("select paramdef.*, application.name as appname from paramdef, application where application.id = paramdef.appid  and paramdef.name='%s';", pg_escape_string($name));
        simpleQuery('', $sql, $r, false, true);
        
        if (empty($r)) {
            throw new \Dcp\PMGT\Exception("PMGT0001", $name, $appName);
        }
        if ($r["appname"] == $appName) {
            $appId = $r["appid"];
        } else {
            $sql = sprintf("select application.id  from application where application.name='%s'", pg_escape_string($appName));
            simpleQuery('', $sql, $appId, true, true);
            if (empty($appId)) {
                throw new \Dcp\PMGT\Exception("PMGT0006", $name, $appName);
            }
        }
        self::setApplicationTypeParameter($r["isglob"] == "Y" ? PARAM_GLB : PARAM_APP, $appName, $appId, $r["name"], $value);
    }
    /**
     * Update a user parameter value
     * @deprecated use { @link ApplicationParameterManager } instead
     * @param string $appName application name
     * @param string $name parameter name (must be declared as user)
     * @param string $value new value to set
     * @param int $userId user account identificator (if null use current user)
     * @throws Dcp\PMGT\Exception
     */
    public static function setUserApplicationParameter($appName, $name, $value, $userId = null)
    {
        deprecatedFunction();
        // verify if parameter exists
        $sql = sprintf("select paramdef.*, application.name as appname from paramdef, application where application.name in ('%s',(select childof from application where  name='%s'))  and application.id=paramdef.appid and paramdef.name = '%s' and isuser='Y';", pg_escape_string($appName) , pg_escape_string($appName) , pg_escape_string($name));
        simpleQuery('', $sql, $r, false, true);
        if (empty($r)) {
            throw new \Dcp\PMGT\Exception("PMGT0004", $name, $appName);
        }
        if ($r["appname"] == $appName) {
            $appId = $r["appid"];
        } else {
            $sql = sprintf("select application.id  from application where application.name='%s'", pg_escape_string($appName));
            simpleQuery('', $sql, $appId, true, true);
            if (empty($appId)) {
                throw new \Dcp\PMGT\Exception("PMGT0006", $name, $appName);
            }
        }
        if ($userId === null) $userId = getCurrentUser()->id;
        self::setUserApplicationTypeParameter($userId, $appName, $appId, $r["name"], $value);
    }
    /**
     * Update a global user parameter value
     * @deprecated use { @link ApplicationParameterManager } instead
     * @param string $name parameter name (must be declared as user and global)
     * @param string $value new value to set
     * @param int $userId user account identificator (if null use current user)
     * @throws Dcp\PMGT\Exception
     */
    public static function setGlobalUserParameter($name, $value, $userId = null)
    {
        deprecatedFunction();
        // verify if parameter exists
        $sql = sprintf("select paramdef.*, application.name as appname from paramdef, application where application.id=paramdef.appid and paramdef.name = '%s' and isuser='Y' and isglob='Y';", pg_escape_string($name));
        simpleQuery('', $sql, $r, false, true);
        if (empty($r)) {
            throw new \Dcp\PMGT\Exception("PMGT0007", $name);
        }
        
        if ($userId === null) $userId = getCurrentUser()->id;
        self::setUserApplicationTypeParameter($userId, $r["appname"], $r["appid"], $r["name"], $value);
    }
    private static function setApplicationTypeParameter($type, $appName, $appId, $name, $value)
    {
        $a = self::getAction();
        if ($a) {
            $p = $a->parent->param;
        } else {
            $p = new Param(getDbAccess());
        }
        
        $err = $p->set($name, $value, $type, $appId);
        
        if ($err) {
            throw new \Dcp\PMGT\Exception("PMGT0002", $name, $appName, $err);
        }
        if (isset(self::$cache[$appName][$name])) self::$cache[$appName][$name] = $value;
        
        $a = self::getAction();
    }
    private static function setUserApplicationTypeParameter($userId, $appName, $appId, $name, $value)
    {
        $a = self::getAction();
        if ($a) {
            $p = $a->parent->param;
        } else {
            $p = new Param(getDbAccess());
        }
        
        simpleQuery('', sprintf("select id from users where id=%d and accounttype='U'", $userId) , $uid, true, true);
        if (!$uid) {
            throw new \Dcp\PMGT\Exception("PMGT0008", $name, $appName, $userId);
        }
        
        $err = $p->set($name, $value, PARAM_USER . $userId, $appId);
        if ($err) {
            throw new \Dcp\PMGT\Exception("PMGT0005", $name, $appName, $err);
        }
        if (isset(self::$cache[$appName][$name])) self::$cache[$appName][$name] = $value;
    }
}
