<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

include_once ("Class.DocTag.php");
include_once ("Class.QueryDb.php");
class TagManager
{
    
    public $docid;
    /**
     * @var DocFam $doc
     */
    public $doc;
    public $dbaccess;
    
    public function __construct(Doc & $doc, $docid)
    {
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
    
    public function getTagsValue($tags)
    {
        $res = array();
        foreach ($tags as $tag) {
            $res[] = $tag["tag"];
        }
        return $res;
    }
    
    public function getTag()
    {
        if (!$this->doc->tagable) {
            return sprintf(_("Document %s is not tagable") , $this->docid);
        }
        $tagDb = new QueryDb($this->dbaccess, "DocTag");
        $tagDb->AddQuery("initid=" . intval($this->docid));
        $tags = $tagDb->Query(0, 0, "TABLE");
        if ($tagDb->nb <= 0) {
            return array();
        }
        return $tags;
    }
    
    public function delTag($tag)
    {
        if (!$this->doc->tagable) {
            return sprintf(_("Document %s is not tagable") , $this->docid);
        }
        if (!$this->docid) return _("Document id not found");
        $err = simpleQuery($this->dbaccess, sprintf("DELETE FROM DocTag WHERE initid=%d AND tag='%s'", intval($this->docid) , pg_escape_string($tag)));
        return $err;
    }
    
    public function addTag($tag)
    {
        global $action;
        
        if (!$this->doc->tagable) {
            return sprintf(_("Document %s is not tagable") , $this->docid);
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
    
    public function getAllTags()
    {
        $tagDb = new QueryDb($this->dbaccess, "DocTag");
        $tags = $tagDb->Query(0, 0, "TABLE");
        if ($tagDb->nb <= 0) {
            return array();
        }
        return $tags;
    }
    
    public function renameTag($oldTag, $newTag)
    {
        if ($newTag == "") return _("no tag specified");
        if ($oldTag === $newTag) return "";
        $err = $this->delTag($oldTag);
        if ($err == "") {
            $err = $this->addTag($newTag);
        }
        return $err;
    }
}
