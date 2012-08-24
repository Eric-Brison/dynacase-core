<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Document Relation Class
 *
 * @author Anakeen
 * @version $Id: Class.DocRel.php,v 1.13 2008/12/03 13:55:14 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 */

include_once ("Class.DbObj.php");
class DocRel extends DbObj
{
    public $fields = array(
        "sinitid", // source id
        "cinitid", // cible id
        "ctitle", // title of cible
        "cicon", // icon of cible
        "stitle", // title of source
        "sicon", // icon of source
        "type", // relation kind
        "doctype"
    );
    /**
     * identifier of the source document
     * @public int
     */
    public $sinitid;
    /**
     * identifier of the cible document
     * @public int
     */
    public $cinitid;
    /**
     * title of the cible document
     * @public int
     */
    public $title;
    /**
     * relation kind
     * @public int
     */
    public $type;
    
    public $id_fields = array(
        "sinitid"
    );
    
    public $dbtable = "docrel";
    public $ctitle;
    public $cicon;
    public $stitle;
    public $sicon;
    public $doctype;
    
    public $sqlcreate = "
create table docrel ( sinitid int not null,                   
                   cinitid int not null,
                   stitle text,
                   ctitle text,
                   sicon text,
                   cicon text,
                   type text,
                   doctype text  );
create index i_docrelc on docrel(cinitid);
create index i_docrels on docrel(sinitid);
create unique index docrel_u on docrel(sinitid,cinitid,type);
";
    
    public function getRelations($reltype = "", $doctype = "", $limit = 0)
    {
        global $action;
        include_once ("Class.QueryDb.php");
        $q = new QueryDb($this->dbaccess, get_class($this));
        $q->AddQuery("sinitid=" . $this->sinitid);
        if ($reltype != "") $q->AddQuery("type='$reltype'");
        if ($doctype != "") $q->AddQuery("doctype='$doctype'");
        $userid = $action->user->id;
        //    if ($userid!=1) $q->AddQuery("(profid <= 0 or hasviewprivilege($userid, profid))");
        $l = $q->Query(0, $limit, "TABLE");
        if (is_array($l)) return $l;
        return array();
    }
    public function getIRelations($reltype = "", $doctype = "", $limit = 0)
    {
        include_once ("Class.QueryDb.php");
        $q = new QueryDb($this->dbaccess, get_class($this));
        $q->AddQuery("cinitid=" . $this->sinitid);
        if ($reltype != "") $q->AddQuery("type='$reltype'");
        if ($doctype != "") $q->AddQuery("doctype='$doctype'");
        $l = $q->Query(0, $limit, "TABLE");
        if (is_array($l)) return $l;
        return array();
    }
    /**
     * Delete document relations
     * @param string $type a special type of relation . Empty means all relations
     * @param int $sinitid document identifier of relation (initid)
     * @return void
     */
    public function resetRelations($type = "", $sinitid = 0)
    {
        if ($sinitid == 0) $sinitid = $this->sinitid;
        if ($sinitid > 0) {
            if ($type != "") $this->exec_query("delete from docrel where sinitid=" . $sinitid . " and type='$type'");
            else $this->exec_query("delete from docrel where sinitid=" . $sinitid . " and type != 'folder'");
        }
    }
    /**
     * Update document relations
     * @param Doc &$doc document to initialize relations
     * @param bool $force if force recomputing
     * @return void
     */
    function initRelations(&$doc, $force = false)
    {
        $nattr = $doc->GetNormalAttributes();
        foreach ($nattr as $k => $v) {
            if (isset($doc->$k) && ($doc->$k != "") && ($v->type == "docid" || $v->type == "account")) {
                
                if (!$force) {
                    if ($doc->getOldValue($v->id) === false) {
                        continue;
                    } else {
                        // reset old relations
                        $this->exec_query("delete from docrel where sinitid=" . $doc->initid . " and type = '" . $v->id . "'");
                    }
                }
                if ($v->inArray()) $tv = array_unique($doc->getTValue($v->id));
                else $tv = array(
                    $doc->$k
                );
                $tvrel = array();
                foreach ($tv as $relid) {
                    if (strpos($relid, '<BR>') !== false) {
                        $tt = explode('<BR>', $relid);
                        foreach ($tt as $brelid) {
                            if (is_numeric($brelid)) $tvrel[] = intval($brelid);
                        }
                    } elseif (is_numeric($relid)) {
                        $tvrel[] = intval($relid);
                    }
                }
                $this->copyRelations(array_unique($tvrel) , $doc, $v->id);
            }
        }
    }
    /**
     * copy in db document relations
     * @param array &$tv array of docid
     * @param Doc &$doc document source
     * @return void
     */
    function copyRelations(&$tv, &$doc, $reltype)
    {
        $tv = array_filter($tv, "notEmpty");
        if (count($tv) > 5) {
            // increase speed using pg_copy
            $t = $this->exec_query(sprintf("select initid,title,icon from docread where locked != -1 and %s", getsqlcond($tv, 'initid')));
            if ($this->numrows() > 0) {
                $c = 0;
                $tin = array();
                while ($row = @pg_fetch_array($this->res, $c, PGSQL_ASSOC)) {
                    $tin[] = sprintf("%d\t%d\t%s\t%s\t%s\t%s\t%s\t%s", $doc->initid, $row["initid"], str_replace("\t", " ", $doc->title) , str_replace("\t", " ", $row["title"]) , $doc->icon, $row["icon"], $reltype, $doc->doctype);
                    $c++;
                }
                pg_copy_from($this->dbid, "docrel", $tin);
            }
        } else {
            foreach ($tv as $vals) {
                $tval = explode("\n", $vals);
                foreach ($tval as $val) {
                    $t = getTDoc($this->dbaccess, $val);
                    $this->cinitid = $t["initid"];
                    if ($this->cinitid > 0) {
                        $this->sinitid = $doc->initid;
                        $this->ctitle = $t["title"];
                        $this->cicon = $t["icon"];
                        $this->stitle = $doc->title;
                        $this->sicon = $doc->icon;
                        $this->type = $reltype;
                        $this->doctype = $doc->doctype;
                        $err = $this->Add();
                    }
                }
            }
        }
    }
}
?>
