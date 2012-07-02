<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

include_once ("Class.DocTag.php");
include_once ("Class.QueryDb.php");
/**
 * @class TagManager
 */
class TagManager {

    public $docid;
    /**
     * @var DocFam $doc
     */
    public $doc;
    public $dbaccess;

    public function __construct(Doc & $doc, $docid) {
        if ($doc->doctype === "C") {
            $this->doc = $doc;
        } else {
            $fam = $doc->getFamDoc();
            if ($fam->isAlive()) {
                $this->doc = $fam;
            }
        }
        $this->docid = $docid;
        $this->dbaccess = $doc->dbaccess;
    }

    /**
     * Get only value in all tag property
     * @static
     * @param string $tags
     * @return array
     */
    static public function getTagsValue($tags) {
        $res = array();
        foreach ($tags as $tag) {
            $res[] = $tag["tag"];
        }
        return $res;
    }

    /**
     * Get tag of current document
     * @return array|string
     */
    public function getTag() {
        if (!$this->doc->tagable) {
            return sprintf(_("Document %s is not tagable"), $this->docid);
        }
        $tags = array();
        simpleQuery($this->dbaccess, sprintf("SELECT DISTINCT ON (tag) * FROM DocTag WHERE initid=" . intval($this->docid)), $tags);
        return $tags;
    }

    /**
     * Delete a tag of current document
     * @param string $tag
     * @return string
     */
    public function delTag($tag) {
        if (!$this->doc->tagable) {
            return sprintf(_("Document %s is not tagable"), $this->docid);
        }
        if (!$this->docid) return _("Document id not found");
        $err = simpleQuery($this->dbaccess, sprintf("DELETE FROM DocTag WHERE initid=%d AND tag='%s'", intval($this->docid), pg_escape_string($tag)));
        return $err;
    }

    /**
     * Add a tag to current document
     * @param string $tag
     * @return string
     */
    public function addTag($tag) {
        global $action;

        if (!$this->doc->tagable) {
            return sprintf(_("Document %s is not tagable"), $this->docid);
        }
        if (!$this->docid) return _("Document id not found");
        if ($tag == "") return _("no tag specified");
        $docTags = $this->getTagsValue($this->getTag());
        if (in_array($tag, $docTags)) {
            return "";
        }
        $tagDb = new DocTag($this->dbaccess);
        $tagDb->initid = $this->docid;
        $tagDb->date = date("d-m-Y H:i:s");
        $tagDb->tag = $tag;
        $tagDb->fromuid = $action->user->id;
        $err = $tagDb->Add();
        return $err;
    }

    /**
     * Get list of all tags from $start to $slice orderby $orderby where tag ~* ^$query
     * @static
     * @param int $start
     * @param int $slice
     * @param string $query
     * @param string $orderby
     * @return array
     */
    static public function getAllTags($start = 0, $slice = 0, $query = "", $orderby = "") {
        $tags = array();
        if ($orderby) {
            $orderby = "ORDER BY $orderby";
        }
        if ($slice == 0) {
            $slice = "ALL";
        }

        $err = simpleQuery(getDbAccess(), sprintf("SELECT DISTINCT ON (tag) * FROM DocTag WHERE  strpos(tag, '%s') = 1 %s LIMIT %s OFFSET %s", pg_escape_string($query), $orderby, $slice, $start), $tags);
        if ($err) {
            return array(
                $err
            );
        }
        return $tags;
    }

    /**
     * Get number of all tags on all document
     * @static
     * @return array
     */
    static public function getAllCount() {
        $tags = array();
        $err = simpleQuery(getDbAccess(), "SELECT count(DISTINCT tag) as count FROM DocTag", $tags);
        if ($err) {
            return array(
                $err
            );
        }
        return $tags[0]["count"];
    }

    /**
     * Get tags information and number of tag per document for tags from $start to $slice orderby $orderby where tag ~* ^$query
     * @static
     * @param int $start
     * @param int $slice
     * @param string $query
     * @param string $orderby
     * @return array
     */
    static public function getAllTagsAndCount($start = 0, $slice = 0, $query = "", $orderby = "") {
        $tags = array();
        if ($orderby) {
            $orderby = "ORDER BY $orderby";
        }
        if ($slice == 0) {
            $slice = "ALL";
        }
        $err = simpleQuery(getDbAccess(), sprintf("SELECT tag,count(DISTINCT initid) as number FROM DocTag WHERE  strpos(tag, '%s') = 1 GROUP BY tag %s LIMIT %s OFFSET %s", pg_escape_string($query), $orderby, $slice, $start), $tags);
        if ($err) {
            return array(
                $err
            );
        }
        return $tags;
    }

    /**
     * Get number of document with the tag $tag
     * @static
     * @param string $tags
     * @return array
     */
    static public function getTagCount($tags) {
        $result = array();
        $where = "";
        if (is_array($tags)) {
            foreach ($tags as $tag) {
                if ($where) {
                    $where .= ",";
                } else {
                    $where = "in (";
                }
                $where .= sprintf("'%s'", pg_escape_string($tag));
            }
            $where .= ")";
        } else {
            $where = sprintf("= '%s'", pg_escape_string($tags));
        }
        simpleQuery(getDbAccess(), sprintf("select count(distinct initid) from doctag where tag %s", $where), $result, true, true);
        return $result;
    }

    /**
     * Delete tag on all documents
     * @static
     * @param string $oldTag
     * @return string
     */
    static public function deleteTagOnAllDocument($oldTag) {
        if (!$oldTag) return "";
        if (is_array($oldTag)) {
            $where = TagManager::constructWhereMultiple($oldTag);
        } else {
            $where = sprintf("tag = '%s'", pg_escape_string($oldTag));
        }
        $err = simpleQuery(getDbAccess(), sprintf("DELETE FROM Doctag WHERE %s", $where));
        return $err;
    }

    /**
     * Renmae tag on all document
     * @static
     * @param string $oldTag actual tag name
     * @param string $newTag new tag name we want to set
     * @return string
     */
    static public function renameTagOnAllDocument($oldTag, $newTag) {
        if ($newTag == "") return _("no tag specified");
        if ($oldTag === $newTag) return "";

        if (is_array($oldTag)) {
            $where = TagManager::constructWhereMultiple($oldTag);
        } else {
            $where = sprintf("tag = '%s'", pg_escape_string($oldTag));
        }
        $err = simpleQuery(getDbAccess(), sprintf("UPDATE Doctag SET tag = '%s' WHERE %s", pg_escape_string($newTag), $where));
        return $err;
    }

    /**
     * Contruct where query for multpile tags
     * @static
     * @param array $oldTag list of tag names
     * @return string
     */
    static private function constructWhereMultiple($oldTag) {
        $where = "";
        foreach ($oldTag as $tag) {
            if ($where) {
                $where .= " OR ";
            }
            $where .= sprintf("tag = '%s'", pg_escape_string($tag));
        }
        return $where;
    }

    /**
     * Rename tag on current document
     * @param string $oldTag Actual tag name
     * @param string $newTag New tag name we want to set
     * @return string
     */
    public function renameTag($oldTag, $newTag) {
        if ($newTag == "") return _("no tag specified");
        if ($oldTag === $newTag) return "";
        $err = $this->delTag($oldTag);
        if ($err == "") {
            $err = $this->addTag($newTag);
        }
        return $err;
    }
}
