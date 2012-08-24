<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
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
     * @return string|bool|null
     */
    public static function getRelationTitle($docid, $latest = true, Doc $doc)
    {
        $uid = getCurrentUser()->id; // index by uid in case of sudo
        $isAdmin = ($uid == 1);
        if (!is_numeric($docid)) $docid = getIdFromName(getDbAccess() , $docid);
        if (!$docid) return null;
        $keyCache = intval($docid) . '-' . intval($latest);
        if (!isset(self::$relationCache[$uid][$keyCache])) {
            self::setRelationCache($doc, $uid);
        }
        
        if (isset(self::$relationCache[$uid][$keyCache])) {
            $relCache = self::$relationCache[$uid][$keyCache];
            
            if (!isset($relCache["rid"])) return null; // unknow document
            if ($relCache["canaccess"] === 'f' && (!$isAdmin)) {
                return false; //_("information access deny");
                
            } else {
                return $relCache["title"];
            }
        }
        
        return null;
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
            $latest = $oa->getOption("docrev", "latest") == "latest";
            if ($type == "docid" || $type == "account") {
                $ids = $doc->getTValue($oa->id);
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
                    $relationIds[$did . '-' . intval($latest) ] = array(
                        "docid" => $did,
                        "latest" => $latest
                    );
                }
            }
        }
        $latestId = array();
        foreach ($relationIds as $relid) {
            if ($relid["latest"]) $latestId[] = $relid["docid"];
        }
        if ($latestId) {
            $sql = sprintf("select id,initid from docread where initid in (%s) and locked != -1", implode(',', $latestId));
            simpleQuery($doc->dbaccess, $sql, $result);
            $tInitid = array();
            foreach ($result as $aRow) {
                $tInitid[$aRow["initid"]] = $aRow["id"];
            }
            foreach ($relationIds as $k => $relid) {
                if ($relid["latest"]) {
                    $relationIds[$k]["rid"] = empty($tInitid[$relid["docid"]]) ? null : $tInitid[$relid["docid"]];
                } else {
                    $relationIds[$k]["rid"] = $relid["docid"];
                }
            }
        }
        $realIds = array();
        foreach ($relationIds as $relid) {
            if ($relid["rid"]) $realIds[] = $relid["rid"];
        }
        if ($realIds) {
            $sql = sprintf("select id,initid,title,views && '%s' as canaccess from docread where id in (%s)", self::getUserVector() , implode(',', $realIds));
            
            simpleQuery($doc->dbaccess, $sql, $result);
            
            $accesses = array();
            foreach ($result as $access) {
                $accesses[$access["id"]] = $access;
            }
            foreach ($relationIds as $k => $relid) {
                $rid = $relid["rid"];
                if ($rid) {
                    $relationIds[$k]["title"] = $accesses[$rid]["title"];
                    $relationIds[$k]["canaccess"] = $accesses[$rid]["canaccess"];
                }
            }
        }
        if (!empty(self::$relationCache[$uid])) self::$relationCache[$uid] = array_merge($relationIds, self::$relationCache[$uid]);
        else self::$relationCache[$uid] = $relationIds;
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
