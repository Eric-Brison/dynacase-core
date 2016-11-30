<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp;

class VaultManager
{
    
    protected static $vault = null;
    /**
     * @return \VaultFile
     */
    protected static function getVault()
    {
        
        if (self::$vault === null) {
            self::$vault = new \VaultFile("", "FREEDOM");
        }
        return self::$vault;
    }
    /**
     * return various informations for a file stored in VAULT
     * @param int $idfile vault file identifier
     * @param string $teng_name transformation engine name
     * @return \vaultFileInfo
     */
    public static function getFileInfo($idfile, $teng_name = "")
    {
        self::getVault()->Show($idfile, $info, $teng_name);
        if (!$info) {
            return null;
        }
        return $info;
    }
    /**
     * return various informations for a file stored in VAULT
     * @param string $filepath
     * @param string $ftitle
     * @param bool $public_access set to true to store uncontrolled files like icons
     * @return int
     * @throws Exception
     */
    public static function storeFile($filepath, $ftitle = "", $public_access = false)
    {
        
        $err = self::getVault()->store($filepath, $public_access, $vid);
        if ($err) {
            throw new Exception("VAULT0001", $err);
        }
        if ($ftitle != "") self::getVault()->rename($vid, $ftitle);
        return $vid;
    }
    /**
     * return various informations for a file stored in VAULT
     * @param string $filepath
     * @param string $ftitle
     * @throws Exception
     * @return int return vault identifier
     */
    public static function storeTemporaryFile($filepath, $ftitle = "")
    {
        if (!\AuthenticatorManager::$session || !\AuthenticatorManager::$session->id) {
            throw new Exception("VAULT0003");
        }
        $err = self::getVault()->store($filepath, false, $vid, $fsname = '', $te = "", 0, $tmp = \AuthenticatorManager::$session->id);
        
        if ($err) {
            throw new Exception("VAULT0002", $err);
        }
        if ($ftitle != "") self::getVault()->rename($vid, $ftitle);
        return $vid;
    }
    /**
     * Delete id_tmp propertty of identified files
     * @param array $vids vault identifiers list
     */
    public static function setFilesPersitent(array $vids)
    {
        if (count($vids) > 0) {
            $sql = sprintf("update vaultdiskstorage set id_tmp = null where id_tmp is not null and id_file in (%s)", implode(",", array_map(function ($x)
            {
                return intval($x);
            }
            , $vids)));
            simpleQuery("", $sql);
        }
    }
    /**
     * Destroy file from vault
     * The file is physicaly deleted
     * @param int $vid vault file identifier
     * @throws Exception
     */
    public static function destroyFile($vid)
    {
        $info = self::getFileInfo($vid);
        if ($info === null) {
            throw new Exception("VAULT0004", $vid);
        }
        
        self::getVault()->destroy($vid);
    }
    /**
     * Delete vault temporary files where create date is less than interval
     * @param int $dayInterval number of day
     */
    public static function destroyTmpFiles($dayInterval = 2)
    {
        $sql = sprintf("select id_file from vaultdiskstorage where id_tmp is not null and id_tmp != '' and cdate < (now() - INTERVAL '%d day');", $dayInterval);
        
        simpleQuery("", $sql, $result, true);
        foreach ($result as $vid) {
            self::destroyFile($vid);
        }
    }
    /**
     * Set access date to now
     * @param  int $idfile vault file identifier
     */
    public static function updateAccessDate($idfile)
    {
        self::getVault()->updateAccessDate($idfile);
    }
}
