<?php
/**
 * Waiting documents
 * Temporary saving
 *
 * @author Anakeen 
 * @version $Id:  $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 */
/**
 */

include_once ("Class.DbObj.php");
class DocWait extends DbObj
{
    public $fields = array(
        "refererid", // doc id
        "refererinitid", // doc initid
        "title", // doc title
        "fromid", // family
        "values", // values of document (serialized object)  
        "orivalues", // original values of document (serialized object)  
        "uid", // user id
        "domain", // domain id
        "status", // status code
        "statusmessage", // status message
        "transaction", // transaction id
        "date"
    );
    
    /**
     * identificator of referer document
     * @public int
     */
    public $refererid;
    
    /**
     * identificator system of the user
     * @public int
     */
    public $uid;
    /**
     * document title
     * @public string
     */
    public $title;
    /**
     * date record
     * @public date
     */
    public $date;
    /**
     * document status : ok|constraint|obsolete
     * @public string
     */
    public $status;
    
    /**
     * document status message
     * @public string
     */
    public $statusMessage;
    
    /**
     * arg of code
     * @public text serialize object
     */
    public $arg;
    
    public $id_fields = array(
        "refererinitid",
        "uid"
    );
    public $dbtable = "docwait";
    
    public $sqlcreate = "
create table docwait ( refererid int not null,   
                   refererinitid int not null,
                   fromid int,
                   title text,
                   uid int not null,
                   values text,
                   orivalues text,
                   date timestamp default now(),
                   domain int,
                   transaction int,
                   statusmessage text,
                   status text );
create index i_docwait on docwait(transaction);
create unique index iu_docwait on docwait(refererinitid, uid);
create sequence seq_waittransaction start 1;
";
    
    /**#@+
     * constant for waiting status"
     */
    const newDocument = "new";
    const notModified = "same";
    const modified = "modified";
    const conflict = "conflict";
    const constraint = "constraint";
    const invalid = "invalid";
    const indetermined = "indetermined";
    /** referer document @var Doc */
    private $refererDoc = null;
    /** referer document identificator @var int */
    private $refererDocId = null;
    /** waiting document @var Doc */
    private $waitingDoc = null;
    public function save()
    {
        $err = '';
        $this->status = $this->computeStatus();
        if ($this->status == self::conflict) $err = $this->statusMessage;
        else {
            $wdoc = $this->getWaitingDocument();
            $wdoc->doctype = $wdoc->defDoctype; // become consistent
            $info = null;
            $err = $wdoc->save($info);
            if ($err) {
                $this->status = self::constraint;
                $this->statusMessage = $info;
            } else {
                $this->resetWaitingDocument();
            }
        }
        //print "save [$this->status]" . $this->title;
        return $err;
    }
    
    public function resetWaitingDocument()
    {
        $doc = $this->getRefererDocument(true);
        if ($doc) {
            $this->orivalues = serialize($doc->getValues());
            $this->status = self::notModified;
            $this->statusMessage = '';
            $this->transaction = 0;
            $this->date = date('Y-m-d H:i:s.u');
            $this->modify();
        }
    }
    
    private function getWriteAttribute(Doc &$doc)
    {
        $attrs = $doc->getNormalAttributes();
        $wattr = array();
        foreach ( $attrs as $aid => $oa ) {
            if (($oa->mvisibility == 'W') || ($oa->mvisibility == 'O') || $oa->getOption("writted") == "yes") {
                $wattr[$aid] = $oa;
            }
        }
        return $wattr;
    }
    
    public function complete()
    {
        $this->refererDoc = null;
        $this->waitingDoc = null;
    }
    /**
     * the referer (null if new document)
     * @return Doc the referer
     */
    public function getRefererDocument($reset = false)
    {
        if ($reset) $this->refererDoc = null;
        if (!$this->refererDoc) {
            $this->refererDoc = new_doc($this->dbaccess, $this->refererid, true);
            $this->refererDocId = $this->refererDoc->id;
            if ($this->waitingDoc) {
                $this->values = serialize($this->waitingDoc->getValues());
                
                $this->waitingDoc = null;
            }
        } else {
            if (($this->refererDoc->id != $this->refererDocId) || $this->refererDoc->isFixed()) {
                $this->refererDoc = new_doc($this->dbaccess, $this->refererid, true);
                $this->refererDocId = $this->refererDoc->id;
                if ($this->waitingDoc) {
                    $this->values = serialize($this->waitingDoc->getValues());
                    $this->waitingDoc = null;
                }
            
            }
        }
        return $this->refererDoc;
    }
    
    /**
     * the referer with new values
     * document ready to update original
     * @return Doc the document
     */
    public function getWaitingDocument()
    {
        $cdoc = $this->getRefererDocument(); // refresh referer if needed
        if (!$this->waitingDoc) {
            
            $this->waitingDoc = clone $cdoc;
            $waitValues = unserialize($this->values);
            foreach ( $waitValues as $aid => $v ) {
                $this->waitingDoc->setValue($aid, $v);
            }
            $this->waitingDoc->doctype = 'I';
        }
        return $this->waitingDoc;
    }
    
    public function isValid()
    {
        return ($this->status == self::newDocument || $this->status == self::modified || $this->status == self::notModified);
    
    }
    
    /**
     * verify if origin values are same as current alive values
     * restrict to W/O visibilities values
     * @param int $mask identificator to use specific mask to detect W attributes
     */
    public function computeStatus($mask = 0)
    {
        if ($this->status != self::invalid) {
            if ($this->refererid) {
                $originValues = unserialize($this->orivalues);
                $currentDoc = $this->getRefererDocument();
                if ($currentDoc->isAlive()) {
                    $err = $currentDoc->canEdit();
                    if ($err) {
                        $this->statusMessage = $err;
                        $this->status = self::conflict;
                    } else {
                        if ($currentDoc->locked != $currentDoc->getSystemUserId()) {
                            $this->statusMessage = sprintf("document %s [%d] not locked");
                            $this->status = self::conflict;
                        } else {
                            if ($mask) $currentDoc->ApplyMask($mask);
                            $attrs = $this->getWriteAttribute($currentDoc);
                            $this->status = self::notModified;
                            $this->statusMessage = '';
                            /*print_r2(array(
                                "cur" => $currentDoc->getValues(),
                                "wai" => $this->getWaitingDocument()->getValues(),
                                "ori" => $originValues
                            ));
                            */
                            $waitingDoc = $this->getWaitingDocument();
                            foreach ( $attrs as $aid => $oa ) {
                                $ovalue = $originValues[$oa->id];
                                $cvalue = $currentDoc->getValue($oa->id);
                                if ($ovalue != $cvalue) {
                                    $this->status = self::conflict;
                                    $this->statusMessage .= sprintf(_("conflict %s [%s]: current=%s, modified=%s"), $oa->getLabel(), $oa->id, $cvalue, $ovalue) . "\n";
                                }
                            }
                            $this->statusMessage = substr($this->statusMessage, 0, -1);
                        }
                    }
                } else {
                    $this->statusMessage = sprintf("document %s [%d] not exists");
                    $this->status = self::conflict;
                }
            } else {
                $this->status = self::newDocument;
            }
            
            $this->modify();
        }
        return $this->status;
    }
}

?>