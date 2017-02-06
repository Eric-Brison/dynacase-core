<?php

namespace Dcp\Core;

class LibPhpini
{
    /**
     * @var \Application
     */
    static $coreApplication = null;

    public static function applyLimits()
    {
        $limits = [];
        $limits["memory_limit"] = self::applyLimit("memory_limit", "MEMORY_LIMIT", 64, -1);
        return $limits;
    }

    /**
     * @param \Application $coreApplication
     */
    public static function setCoreApplication(\Application $coreApplication)
    {
        self::$coreApplication = $coreApplication;
    }

    /**
     * Apply a limit to $phpIniValueName value by choosing "best" value between
     * php.ini and CORE parameter $dcpParameterName
     *
     * @param string $phpIniValueName name of the php.ini parameter
     * @param string $dcpParameterName name of the core parameter containing dcp value
     * @param int $defaultValue default value for the core parameter if empty
     * @param int $infinityValue infinity value (often -1 or 0)
     * @return array
     */
    protected static function applyLimit($phpIniValueName, $dcpParameterName, $defaultValue, $infinityValue = 0)
    {
        $changes = [];

        $phpIniLimit = ini_get($phpIniValueName);
        $changes["php.ini"] = $phpIniLimit;

        if ($infinityValue !== $phpIniLimit) {
            $changes["php.ini_is_infinity"] = false;

            $coreLimit = intval(self::getParam($dcpParameterName, $defaultValue));
            $changes["core"] = $coreLimit;

            if (self::return_bytes($phpIniLimit) < $coreLimit * 1024 * 1024) {
                $changes["best"] = $coreLimit . "M";
                $changes["success"] = false !==ini_set($phpIniValueName, $coreLimit . "M");
            } else {
                $changes["best"] = $phpIniLimit;
            }
        } else {
            $changes["php.ini_is_infinity"] = true;
            $changes["best"] = $phpIniLimit;
        }

        return $changes;
    }

    protected static function getParam($name, $defaultValue)
    {
        if(is_null(self::$coreApplication)) {
            global $action;
            if ($action instanceof \Action &&
                "CORE" === $action->parent->name) {
                self::$coreApplication = $action->parent;
                return self::$coreApplication->getParam($name, $defaultValue);
            } else {
                require_once 'Lib.Common.php';
                require_once 'Class.ApplicationParameterManager.php';
                $parameterValue = \ApplicationParameterManager::getParameterValue("CORE", $name);
                return (null === $parameterValue ? $defaultValue : $parameterValue);
            }
        } else {
            return self::$coreApplication->getParam($name, $defaultValue);
        }
    }

    /**
     * convert a php.ini value into bytes
     *
     * @param string|int $val the value from php.ini with optional unit)
     * @return int
     */
    protected static function return_bytes($val)
    {
        $val = trim($val);
        $last = strtolower(substr($val, -1));
        $val=intval($val);
        switch($last) {
            /** @noinspection PhpMissingBreakStatementInspection */
            case 'g':
                $val *= 1024;
            /** @noinspection PhpMissingBreakStatementInspection */
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }

        return $val;
    }
}