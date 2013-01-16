<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Manage application parameters
 * Set and get application parameters
 * @class ApplicationParameterManager
 *
 * @see ApplicationParameterManager
 *
 */
class ApplicationParameterManager
{
    const CURRENT_APPLICATION = '##CURRENT APPLICATION##';
    const GLOBAL_PARAMETER = '##GLOBAL PARAMETER##';
    /**
     * @var array
     * @private
     */
    private static $cache = array();

    /**
     * for internal purpose only
     * @private
     */
    public static function resetCache()
    {
        self::$cache = array();
    }

    /**
     * Return the value of a user parameter
     *
     * @param string|int|Application $application logical name or id or object of the application, you can use
     * {@link ApplicationParameterManager::CURRENT_APPLICATION} or {@link ApplicationParameterManager::GLOBAL_PARAMETER}
     * @param string $parameterName logical name of the parameter
     * @param null|int $userId user login or account id, use it if you want the value for another user
     *
     * @throws Dcp\ApplicationParameterManager\Exception
     *
     * @return string|null the value of a user parameter (USER="Y") or if not exist
     */
    public static function getUserParameterValue($application, $parameterName, $userId = null)
    {
        if ($userId === null) {
            $userId = getCurrentUser()->id;
        }
        $applicationId = self::getApplicationId($application, $parameterName);
        if (isset(self::$cache[$applicationId . ' ' . $parameterName . ' ' . $userId])) {
            return self::$cache[$applicationId . ' ' . $parameterName . ' ' . $userId];
        }
        $sql = sprintf("select val from paramv where appid=%d and type='U%d' and name='%s';", $applicationId, $userId, pg_escape_string($parameterName));
        $return = null;
        simpleQuery("", $sql, $return, true, true, true);
        if ($return !== false) {
            self::$cache[$applicationId . ' ' . $parameterName . ' ' . $userId] = $return;
            return $return;
        } else {
            return null;
        }

    }

    /**
     * Return the default value of a user parameter
     *
     * @param string|int|Application $application logical name or id or object of the application, you can use
     * { @link ApplicationParameterManager::CURRENT_APPLICATION} or {@link ApplicationParameterManager::GLOBAL_PARAMETER}
     * @param string $parameterName logical name of the parameter
     *
     * @throws Dcp\ApplicationParameterManager\Exception
     *
     * @return string the value of a common parameter (USER="Y")
     */
    public static function getUserParameterDefaultValue($application, $parameterName)
    {
        return self::getCommonParameterValue($application, $parameterName);
    }

    /**
     * Return the value of a non-user (common) parameter
     *
     * @api Return the value of a common parameter
     *
     * @param string|int|Application $application logical name or id or object of the application, you can use
     * {@link ApplicationParameterManager::CURRENT_APPLICATION} or {@link ApplicationParameterManager::GLOBAL_PARAMETER}
     * @param string $parameterName logical name of the parameter
     *
     * @throws Dcp\ApplicationParameterManager\Exception
     *
     * @return string the value of a common parameter (USER="N")
     */
    public static function getCommonParameterValue($application, $parameterName)
    {
        $applicationId = self::getApplicationId($application, $parameterName);
        if (isset(self::$cache[$applicationId . ' ' . $parameterName])) {
            return self::$cache[$applicationId . ' ' . $parameterName];
        }
        $sql = sprintf("select val from paramv where appid=%d and type !~ '^U' and name='%s';",
            $applicationId, $parameterName, pg_escape_string($parameterName));
        $return = null;
        simpleQuery("", $sql, $return, true, true, true);
        if ($return !== false) {
            self::$cache[$applicationId . ' ' . $parameterName] = $return;
            return $return;
        } else {
            return null;
        }

    }

    /**
     * Set the user parameter value
     *
     * @api Set the user parameter value
     *
     * @param string|int|Application $application logical name or id or object of the application, you can use
     * { @link ApplicationParameterManager::CURRENT_APPLICATION} or {@link ApplicationParameterManager::GLOBAL_PARAMETER}
     * @param string $parameterName logical name of the parameter
     * @param string $value value of the parameter
     * @param null|int|string $userId user login or account id, use it if you want to set the value for another user
     *
     * @param bool $check
     * @throws Dcp\ApplicationParameterManager\Exception
     *
     * @return void
     */
    public static function setUserParameterValue($application, $parameterName, $value, $userId = null, $check = true)
    {
        $applicationId = self::getApplicationId($application, $parameterName);

        $action = self::getAction();
        if ($action) {
            $parameter = $action->parent->param;
        } else {
            $parameter = new Param(getDbAccess());
        }

        if ($userId === null) {
            $userId = getCurrentUser()->id;
        }

        if ($check) {
            /* if parameter exists and is user type */
            $type = self::getParameter($applicationId, $parameterName);

            if (empty($type) || $type["isuser"] === "N") {
                throw new \Dcp\ApplicationParameterManager\Exception("APM0006", $applicationId, $parameterName);
            }
        }

        /* Test if user really exist*/
        simpleQuery('', sprintf("select true from users where id=%d and accounttype='U'", $userId),
            $uid, true, true, true);

        if ($uid === false) {
            throw new \Dcp\ApplicationParameterManager\Exception("APM0007", $applicationId, $parameterName, $userId);
        }

        $err = $parameter->set($parameterName, $value, PARAM_USER . $userId, $applicationId);
        if ($err) {
            throw new \Dcp\ApplicationParameterManager\Exception("APM0006", $applicationId, $parameterName, $err);
        }
        self::$cache[$applicationId . ' ' . $parameterName . ' ' . $userId] = $value;

        if ($action) {
            $action->parent->session->closeAll($userId);
            if ($action->parent->session->isAffected()) {
                $session = $action->parent->session;
            } else {
                $session = new Session(Session::PARAMNAME);
            }

            $session->closeAll($userId);
        }

    }

    /**
     * Set the user parameter default value
     *
     * @param string|int|Application $application logical name or id or object of the application, you can use
     * {@link ApplicationParameterManager::CURRENT_APPLICATION} or {@link ApplicationParameterManager::GLOBAL_PARAMETER}
     * @param string $parameterName logical name of the parameter
     * @param string $value value of the parameter
     *
     * @return void
     */
    public static function setUserParameterDefaultValue($application, $parameterName, $value)
    {
        self::setCommonParameterValue($application, $parameterName, $value);
    }

    /**
     * Set the common parameter default value
     *
     * @param string|int|Application $application logical name or id or object of the application, you can use
     * { @link ApplicationParameterManager::CURRENT_APPLICATION} or {@link ApplicationParameterManager::GLOBAL_PARAMETER}
     * @param string $parameterName logical name of the parameter
     * @param string $value value of the parameter
     *
     * @throws Dcp\ApplicationParameterManager\Exception
     *
     * @return void
     */
    public static function setCommonParameterValue($application, $parameterName, $value)
    {
        $applicationId = self::getApplicationId($application, $parameterName);

        $isGlobal = false;
        $sql = sprintf("select type from paramv where (name='%s' and appid = %d);",
            pg_escape_string($parameterName), $applicationId);
        simpleQuery('', $sql, $isGlobal, true, true, true);

        if ($isGlobal === false) {
            throw new \Dcp\ApplicationParameterManager\Exception("APM0008", $parameterName);
        }

        $type = ($isGlobal === "G") ? PARAM_GLB : PARAM_APP;

        $action = self::getAction();
        if ($action) {
            $parameter = $action->parent->param;
        } else {
            $parameter = new Param(getDbAccess());
        }

        $err = $parameter->set($parameterName, $value, $type, $applicationId);

        if ($err) {
            throw new \Dcp\ApplicationParameterManager\Exception("APM0009", $parameterName, $applicationId, $err);
        }
        self::$cache[$applicationId . ' ' . $parameterName] = $value;

        if ($action) {
            $action->parent->session->closeAll();
            if ($action->parent->session->isAffected()) {
                $session = $action->parent->session;
            } else {
                $session = new Session(Session::PARAMNAME);
            }
            $session->closeAll();
        }

    }

    /**
     * Get a parameter value in the database
     *
     * @param string|int|Application $application logical name or id or object of the application, you can use
     * { @link ApplicationParameterManager::CURRENT_APPLICATION} or {@link ApplicationParameterManager::GLOBAL_PARAMETER}
     * @param string $parameterName logical name of the parameter
     *
     * @throws Dcp\ApplicationParameterManager\Exception
     *
     * @return string value of the parameter
     */
    public static function getParameterValue($application, $parameterName)
    {
        $applicationId = self::getApplicationId($application, $parameterName);

        $type = self::getParameter($applicationId, $parameterName);

        $return = null;

        if ($type["isuser"] === "Y") {
            $return = self::getUserParameterValue($applicationId, $parameterName);
            if ($return === null) {
                $return = self::getUserParameterDefaultValue($applicationId, $parameterName);
            }
        } else {
            $return = self::getCommonParameterValue($applicationId, $parameterName);
        }

        return $return;

    }

    /**
     * Set a parameter value
     *
     * @param string|int|Application $application logical name or id or object of the application, you can use
     * {@link ApplicationParameterManager::CURRENT_APPLICATION} or {@link ApplicationParameterManager::GLOBAL_PARAMETER}
     * @param string $parameterName logical name of the parameter
     * @param string $value value of the parameter
     *
     * @throws Dcp\ApplicationParameterManager\Exception
     *
     * @return void|string error string or void
     */
    public static function setParameterValue($application, $parameterName, $value)
    {

        $applicationId = self::getApplicationId($application, $parameterName);

        $type = self::getParameter($applicationId, $parameterName);

        $return = null;

        if ($type["isuser"] === "Y") {
            self::setUserParameterValue($applicationId, $parameterName, $value, null, false);
        } else {
            self::setCommonParameterValue($applicationId, $parameterName, $value);
        }

    }

    /**
     * Get a parameter value in the scope (use cache, session cache, volatile param)
     *
     * Get the value of the parameter in the scope of the current action
     *
     * @param $parameter
     *
     * @return string value of the parameter
     */
    public static function getScopedParameterValue($parameter)
    {
        return getParam($parameter);
    }

    /**
     * Get a parameter object (object that describe the parameter)
     *
     * @param string|int|Application $application logical name or id or object of the application, you can use
     * {@link ApplicationParameterManager::CURRENT_APPLICATION} or {@link ApplicationParameterManager::GLOBAL_PARAMETER}
     * @param string $parameterName logical name of the parameter
     *
     * @throws Dcp\ApplicationParameterManager\Exception
     *
     * @return object the object parameter
     */
    public static function getParameter($application, $parameterName)
    {
        $applicationId = self::getApplicationId($application, $parameterName);

        $result = array();
        $sql = sprintf("SELECT
                paramdef.*
                FROM
                application AS app LEFT OUTER JOIN application AS parent ON (app.childof = parent.name),
                paramdef
                WHERE
                (paramdef.appid = app.id OR paramdef.appid = parent.id or paramdef.appid = 1)
                and paramdef.name = '%s'
                and app.id = %d;",
            pg_escape_string($parameterName), $applicationId);
        simpleQuery('', $sql, $result, false, true, true);
        if (empty($result)) {
            throw new \Dcp\ApplicationParameterManager\Exception("APM008", $parameterName, $applicationId);
        }
        return $result;
    }

    /**
     * Get the parameters objects of an application
     *
     * @param string|int|Application $application logical name or id or object of the application, you can use
     * {@link ApplicationParameterManager::CURRENT_APPLICATION} or {@link ApplicationParameterManager::GLOBAL_PARAMETER}
     *
     * @throws Dcp\ApplicationParameterManager\Exception
     *
     * @return object the object parameter
     */
    public static function getParameters($application)
    {
        $applicationId = self::getApplicationId($application);

        $result = array();
        $sql = sprintf("SELECT
                paramdef.*,
                app.name as applicationName
                FROM
                application AS app LEFT OUTER JOIN application AS parent ON (app.childof = parent.name),
                paramdef
                WHERE
                (paramdef.appid = app.id OR paramdef.appid = parent.id or paramdef.appid = 1)
                and app.id = %d;",
                 $applicationId);
        simpleQuery('', $sql, $result, false, false, true);
        if (empty($result)) {
            throw new \Dcp\ApplicationParameterManager\Exception("APM0003", $applicationId);
        }
        return $result;

    }

    /**
     * Get the application name
     *
     * @param string|int|Application $application Application
     * @param string $parameter used only in global detection
     *
     * @throws Dcp\ApplicationParameterManager\Exception
     * @return null|string|array null if not find, string if only id, array if id and name
     */
    private static function getApplicationId($application, $parameter = "")
    {
        $applicationName = "";
        $applicationId = "";

        if (empty($parameter) && $application === ApplicationParameterManager::GLOBAL_PARAMETER) {
            throw new \Dcp\ApplicationParameterManager\Exception("APM0010");
        } elseif ($application === ApplicationParameterManager::GLOBAL_PARAMETER) {
            $applicationId = self::getGlobalParameterApplicationName($parameter);
            if ($applicationId === false) {
                throw new \Dcp\ApplicationParameterManager\Exception("APM0002", $parameter);
            }
        } elseif ($application === ApplicationParameterManager::CURRENT_APPLICATION) {
            global $action;
            if ($action instanceof Action) {
                $applicationName = $action->parent->name;
                $applicationId = $action->parent->id;
            } else {
                throw new \Dcp\ApplicationParameterManager\Exception("APM0004");
            }
        } elseif ($application instanceof Application) {
            $applicationName = $application->name;
        } elseif ((!is_int($application) ? (ctype_digit($application)) : true)) {
            $applicationId = $application;
        } elseif (is_string($application)) {
            $applicationName = $application;
        } else {
            throw new \Dcp\ApplicationParameterManager\Exception("APM0004");
        }

        if ($applicationName && empty($applicationId)) {
            $applicationId = self::convertApplicationNameToId($applicationName);
            if ($applicationId === false) {
                throw new \Dcp\ApplicationParameterManager\Exception("APM0003", $application);
            }
        }

        return $applicationId;

    }

    private static function getGlobalParameterApplicationName($parameterName)
    {
        $sql = sprintf("select paramv.appid from paramv, application where paramv.type='G' and application.id=paramv.appid and paramv.name='%s';", pg_escape_string($parameterName));
        $result = null;
        simpleQuery("", $sql, $result, true, true, true);
        return $result;
    }

    private static function convertApplicationNameToId($applicationName)
    {
        $sql = sprintf("select id from application where name = '%s';", pg_escape_string($applicationName));
        $applicationId = null;
        simpleQuery("", $sql, $applicationId, true, true, true);
        return $applicationId;
    }

    private static function getAction()
    {
        global $action;
        return $action;
    }

}
