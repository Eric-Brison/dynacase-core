<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Core;
/**
 * Manage Shared documents through the global array $gdocs
 */
class SharedDocuments
{
    protected static $limit = MAXGDOCS;
    /**
     * @return int
     */
    public static function getLimit()
    {
        return self::$limit;
    }
    /**
     * @param int $limit
     * @throws \Dcp\Exception
     */
    public static function setLimit($limit)
    {
        if (!is_int($limit)) {
            throw new \Dcp\Exception("SharedDocuments limit must be a integer");
        }
        self::$limit = $limit;
    }
    /**
     * Retrieve object from key identifier
     * @param string $key document identifier
     * @return \Doc|null
     */
    public static function &get($key)
    {
        global $gdocs;
        $null = null;
        if ($key === '' or $key === null or (!is_scalar($key))) {
            return $null;
        }
        if ($gdocs && array_key_exists($key, $gdocs)) {
            return $gdocs[$key];
        }
        return $null;
    }
    /**
     * Add or update an object
     * @param string $key object identifier
     * @param \Doc $item object to add or update
     * @return bool (true if it is set to shared object)
     */
    public static function set($key, &$item)
    {
        global $gdocs;
        if ($key === '' or $key === null or (!is_scalar($key))) {
            return false;
        }
        if (count($gdocs) < self::$limit) {
            $gdocs[$key] = & $item;
            return true;
        }
        
        return false;
    }
    /**
     * Unset object
     * @param string $key object identifier
     * @return bool
     */
    public static function remove($key)
    {
        global $gdocs;
        if ($key === '' or $key === null or (!is_scalar($key))) {
            return false;
        }
        unset($gdocs[$key]);
        return true;
    }
    /**
     * unset all objects referenced in shared object
     * @return bool
     */
    public static function clear()
    {
        global $gdocs;
        $gdocs = array();
        return true;
    }
    /**
     * Return all keys referenced in shared object
     * @return array referenced keys returns
     */
    public static function getKeys()
    {
        global $gdocs;
        return array_keys($gdocs);
    }
    /**
     * Verify if a key is referenced in shared object
     * @param string $key object identifier
     * @return bool
     */
    public static function exists($key)
    {
        global $gdocs;
        return array_key_exists($key, $gdocs);
    }
    /**
     * Verify if a key is referenced in cached and object is same as item object
     * @param string $key object identifier
     * @param \Doc $item object item
     * @return bool true if $key and item match
     */
    public static function isShared($key, &$item)
    {
        global $gdocs;
        if ($key === '' or $key === null or (!is_scalar($key))) {
            return false;
        }
        return (isset($gdocs[$key]) && $gdocs[$key] === $item);
    }
}
