<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Get Title from ids
 * @class DocTitle
 *
 */
class DocTitle
{
    private static $viewUserVector = array();
    private static $relationCache = array();
    /**
     * return title of relation
     * return false if access deny
     * return null if relation not found
     * @param int $docid relation documentg id
     * @param bool $latest true if it is latest
     * @param Doc $doc document where comes from relation
     * @param string $docrevOption docrev attribute option
     * @param array $info more information about document target (revision, initid)
     * @return bool|null|string
     */
    public static function getRelationTitle($docid, $latest = true, Doc $doc, $docrevOption = "", array & $info = array())
    {
        $uid = getCurrentUser()->id; // index by uid in case of sudo
        $isAdmin = ($uid == 1);
        
        if ($docid && !is_numeric($docid)) {
            $docid = getIdFromName(getDbAccess() , $docid);
        }
        if (!$docid) return null;
        if ($docrevOption === "") {
            $docrevOption = $latest ? "latest" : "fixed";
        }
        $keyCache = intval($docid) . '-' . $docrevOption;
        
        if (!isset(self::$relationCache[$uid][$keyCache])) {
            self::setRelationCache($doc, $uid);
        }
        
        if (isset(self::$relationCache[$uid][$keyCache])) {
        } else {
            $keyCache = intval($docid) . '-' . $docrevOption;
        }
        if (isset(self::$relationCache[$uid][$keyCache])) {
            $relCache = self::$relationCache[$uid][$keyCache];
            if (!isset($relCache["rid"]) || !isset($relCache["title"])) {
                return null;
            } // unknow document
            if ($relCache["canaccess"] === 'f' && (!$isAdmin)) {
                return false; //_("information access deny");
                
            } else {
                $info = $relCache;
                return $relCache["title"];
            }
        }
        $title = self::getTitle($docid, $latest, $docrevOption, $info);
        return $title;
    }
    /**
     * get all title and access of document's relations
     * @static
     * @param Doc $doc
     * @param int $uid user identifier
     * @return void
     */
    protected static function setRelationCache(Doc $doc, $uid)
    {
        $la = $doc->getNormalAttributes();
        $relationIds = array();
        foreach ($la as $oa) {
            $type = $oa->type;
            $docRevOption = $oa->getOption("docrev", "latest");
            $latest = $docRevOption === "latest";
            $revState = "";
            if (preg_match('/^state\(([^\)]+)\)/', $docRevOption, $matches)) {
                $revState = $matches[1];
            }
            if ($type == "docid" || $type == "account" || $type == "thesaurus") {
                $ids = $doc->getMultipleRawValues($oa->id);
                $realId = array();
                foreach ($ids as $rid) {
                    if (is_numeric($rid)) $realId[] = intval($rid);
                    elseif (strpos($rid, '<BR>') !== false) {
                        $tt = explode('<BR>', $rid);
                        foreach ($tt as $brelid) {
                            if (is_numeric($brelid)) $realId[] = intval($brelid);
                        }
                    }
                }
                foreach ($realId as $did) {
                    $relationIds[$did . '-' . $docRevOption] = array(
                        "docid" => $did,
                        "rid" => $did,
                        "latest" => $latest,
                        "state" => $revState
                    );
                }
            }
        }
        $latestId = array();
        foreach ($relationIds as $k => $relid) {
            if ($relid["latest"]) $latestId[] = $relid["docid"];
            elseif ($relid["state"]) {
                
                simpleQuery($doc->dbaccess, sprintf("select id from docread where initid=(select initid from docread where id=%d) and state = '%s' and locked = -1 order by id desc limit 1", $relid["docid"], pg_escape_string($relid["state"])) , $stateId, true, true);
                if ($stateId) {
                    $relationIds[$k]["rid"] = $stateId;
                }
            }
        }
        if ($latestId) {
            $sql = sprintf("select id,initid from docread where initid in (%s) and locked != -1", implode(',', $latestId));
            //$sql = sprintf("select id,initid from docread where initid in (select initid from docread where id in (%s)) and  locked != -1", implode(',', $getLatestId));
            simpleQuery($doc->dbaccess, $sql, $result);
            $tInitid = array();
            foreach ($result as $aRow) {
                $tInitid[$aRow["initid"]] = $aRow["id"];
            }
            foreach ($relationIds as $k => $relid) {
                if ($relid["latest"]) {
                    $relationIds[$k]["rid"] = empty($tInitid[$relid["docid"]]) ? $relid["docid"] : $tInitid[$relid["docid"]];
                } else {
                    if (!$relid["state"]) {
                        $relationIds[$k]["rid"] = $relid["docid"];
                    }
                }
            }
        }
        $realIds = array();
        
        foreach ($relationIds as $relid) {
            if (!empty($relid["rid"])) $realIds[] = $relid["rid"];
        }
        if ($realIds) {
            $sql = sprintf("select id,initid,title,name,doctype,revision,icon,fromid,views && '%s' as canaccess from docread where id in (%s)", self::getUserVector() , implode(',', $realIds));
            simpleQuery($doc->dbaccess, $sql, $result);
            $accesses = array();
            foreach ($result as $access) {
                $accesses[$access["id"]] = $access;
            }
            
            foreach ($relationIds as $k => $relid) {
                $rid = $relid["rid"];
                if ($rid && isset($accesses[$rid])) {
                    
                    if ($accesses[$rid]["doctype"] === "C") {
                        $relationIds[$k]["title"] = DocFam::getLangTitle(array(
                            "name" => $accesses[$rid]["name"],
                            "title" => $accesses[$rid]["title"]
                        ));
                    } else {
                        $relationIds[$k]["title"] = $accesses[$rid]["title"];
                    }
                    $relationIds[$k]["canaccess"] = $accesses[$rid]["canaccess"];
                    $relationIds[$k]["revision"] = $accesses[$rid]["revision"];
                    $relationIds[$k]["initid"] = $accesses[$rid]["initid"];
                    $relationIds[$k]["fromid"] = $accesses[$rid]["fromid"];
                    $relationIds[$k]["icon"] = $accesses[$rid]["icon"];
                }
            }
        }
        if (!empty(self::$relationCache[$uid])) self::$relationCache[$uid] = array_merge($relationIds, self::$relationCache[$uid]);
        else self::$relationCache[$uid] = $relationIds;
    }
    /**
     * Get title from database if not found in cache
     * @param int $docid Document identifier
     * @param bool $latest
     * @param string $docrevOption
     * @param array $info
     * @return bool|null
     * @throws \Dcp\Db\Exception
     */
    public static function getTitle($docid, $latest = true, $docrevOption = "latest", array & $info = array())
    {
        
        if ($latest || $docrevOption === "latest") {
            $sql = sprintf("select id,initid,title,revision,name,doctype,fromid,icon,views && '%s' as canaccess from docread where initid = %d and locked != -1", self::getUserVector() , $docid);
        } else {
            if (preg_match('/^state\(([^\)]+)\)/', $docrevOption, $matches)) {
                $revState = $matches[1];
                $sql = sprintf("select id,initid,revision,title,name,doctype,fromid,icon,views && '%s' as canaccess from docread where initid=(select initid from docread where id=%d) and state = '%s' and locked = -1 order by id desc limit 1", self::getUserVector() , $docid, pg_escape_string($revState));
            } else {
                $sql = sprintf("select id,initid,revision,title,name,doctype,fromid,icon,views && '%s' as canaccess from docread where id = %d", self::getUserVector() , $docid);
            }
        }
        simpleQuery('', $sql, $result, false, true);
        if ($result) {
            $uid = getCurrentUser()->id;
            $keyCache = $result["id"] . '-' . intval($latest);
            self::$relationCache[$uid][$keyCache] = array(
                "docid" => $result["id"],
                "rid" => $result["id"],
                "initid" => $result["initid"],
                "latest" => $latest,
                "revision" => $result["revision"],
                "title" => $result["title"],
                "fromid" => $result["fromid"],
                "icon" => $result["icon"],
                "canaccess" => $result["canaccess"]
            );
            
            if ($result["canaccess"] === 't') {
                $info = $result;
                return $result["title"];
            } else {
                return false; //_("information access deny");
                
            }
        }
        return null;
    }
    /**
     * get user vector of current user
     * @static
     * @return string
     */
    private static function getUserVector()
    {
        $uid = getCurrentUser()->id;
        if (!isset(self::$viewUserVector[$uid])) {
            self::$viewUserVector[$uid] = SearchDoc::getUserViewVector($uid);
        }
        return self::$viewUserVector[$uid];
    }
}
