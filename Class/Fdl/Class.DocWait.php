<?php
/*
 * @author Anakeen
 * @package FDL
*/

require_once "Class.DbObj.php";
/**
 * Waiting document
 *
 * @brief Temporary saving
 * @class DocWait
 * @package FDL
 */
class DocWait extends DbObj
{
    /**
     * database field
     *
     * @var array
     */
    public $fields = array(
        "refererid", // doc id
        "refererinitid", // doc initid
        "localid", // temporary id in case of creation
        "title", // doc title
        "fromid", // family
        "values", // values of document (serialized object)
        "orivalues", // original values of document (serialized object)
        "uid", // user id
        "domain", // domain id
        "status", // status code
        "statusmessage", // status message
        "transaction", // transaction id
        "date",
        "extradata"
    );
    /**
     * identifier of referer document
     *
     * @var integer
     */
    public $refererid;
    /**
     * initial identifier of referer document
     *
     * @var integer
     */
    public $refererinitid;
    /**
     * temporary identifier use before creation
     *
     * @var integer
     */
    public $localid;
    /**
     * original values (serialized)
     *
     * @var string
     */
    public $orivalues;
    /**
     * extra data (json)
     *
     * @var string
     */
    public $extradata;
    /**
     * family identifier
     *
     * @var int
     */
    public $fromid;
    /**
     * new values (serialized)
     *
     * @var string
     */
    public $values;
    /**
     * transaction identifier
     *
     * @var int
     */
    public $transaction;
    /**
     * identifier system of the user
     *
     * @var integer
     */
    public $uid;
    /**
     * document title
     *
     * @var string
     */
    public $title;
    /**
     * date record
     *
     * @var string
     */
    public $date;
    /**
     * document status : ok|constraint|obsolete
     *
     * @var string
     */
    public $status;
    /**
     * document status message
     *
     * @var string
     */
    public $statusmessage;
    /**
     * arg of code
     *
     * @var string serialize object
     */
    public $arg;
    /**
     * fields primary key
     *
     * @var array fields primary key
     */
    public $id_fields = array(
        "refererinitid",
        "uid"
    );
    /**
     * database table name
     *
     * @var array
     */
    public $dbtable = "docwait";
    /**
     * sql create table
     *
     * @var array
     */
    public $sqlcreate = "
create table docwait ( refererid int not null,   
                   refererinitid int not null,
                   localid text,
                   fromid int,
                   title text,
                   uid int not null,
                   values text,
                   orivalues text,
                   date timestamp default now(),
                   domain int,
                   transaction int,
                   statusmessage text,
                   status text,
                   extradata text );
create index i_docwait on docwait(transaction);
create unique index iu_docwait on docwait(refererinitid, uid);
create sequence seq_waittransaction start 1;
";
    /**#@+
     * constant for waiting status"
    */
    const newDocument = "new";
    const upToDate = "uptodate";
    const modified = "modified";
    const conflict = "conflict";
    const constraint = "constraint";
    const invalid = "invalid";
    const recording = "recording";
    /**
     * referer document
     *
     * @var Doc
     */
    private $refererDoc = null;
    /**
     * referer document identifier
     *
     * @var integer
     */
    private $refererDocId = null;
    /**
     * waiting document
     *
     * @var Doc
     */
    private $waitingDoc = null;
    /**
     * save waiting document
     *
     * @param mixed &$info informations for save
     *
     * @return string error message
     */
    public function save(&$info = null)
    {
        $err = '';
        $this->status = $this->computeStatus();
        if ($this->status == self::conflict) $err = $this->statusmessage;
        else {
            $wdoc = $this->getWaitingDocument();
            $wdoc->doctype = $wdoc->defDoctype; // become consistent
            if ($this->localid) {
                // create it
                $err = $wdoc->add();
                if (!$err) {
                    if ($this->localid) {
                        $this->refererid = $wdoc->id;
                        $this->refererinitid = $wdoc->initid;
                        // change primary key
                        //$this->exec_query(sprintf("delete from docwait where localid='%s'", pg_escape_string($this->localid)));
                        $this->exec_query(sprintf("update docwait set refererid=%d, refererinitid=%d, localid='' where localid='%s'", $this->refererid, $this->refererinitid, pg_escape_string($this->localid)));
                    }
                }
            }
            if (!$err) {
                $info = null;
                $err = $wdoc->save($info);
                clearCacheDoc($this->refererid);
                $this->refererDoc=$wdoc;
            }
            if ($err) {
                $this->status = self::constraint;
                $this->statusmessage = (!empty($info->error)) ? $info->label . ' : ' . $info->error : $err;
                // $this->statusmessage=json_encode($info->error);
                $this->modify();
            } else {
                $this->resetWaitingDocument();
            }
        }
        // error_log("try create $err".$this->localid."::".$wdoc->id);
        //print "save [$this->status]" . $this->title;
        return $err;
    }
    /**
     * get waiting document from database
     *
     * @return string error message
     */
    public function resetWaitingDocument()
    {
        $doc = $this->getRefererDocument(true);
        $err = '';
        if ($doc) {
            $this->refererinitid = $doc->initid;
            $this->refererid = $doc->id;
            $this->orivalues = serialize($doc->getValues());
            $this->status = self::upToDate;
            $this->statusmessage = '';
            $this->transaction = 0;
            $this->date = date('Y-m-d H:i:s.u');
            $err = $this->modify();
        }
        return $err;
    }
    /**
     * get write attribute of a doc
     *
     * @param Doc &$doc the doc
     *
     * @return array of docAttribute
     */
    private function getWriteAttribute(Doc & $doc)
    {
        $attrs = $doc->getNormalAttributes();
        $wattr = array();
        foreach ($attrs as $aid => $oa) {
            if (($oa->mvisibility == 'W') || ($oa->mvisibility == 'O') || $oa->getOption("writted") == "yes") {
                $wattr[$aid] = $oa;
            }
        }
        return $wattr;
    }
    /**
     * complete
     *
     * @return void
     */
    public function complete()
    {
        $this->refererDoc = null;
        $this->waitingDoc = null;
    }
    /**
     * the referer (null if new document)
     *
     * @param boolean $reset set to true to force update from database
     *
     * @return Doc the referer
     */
    public function getRefererDocument($reset = false)
    {
        if ($reset) $this->refererDoc = null;
        if ($this->refererid <= 0) return null;
        if (!$this->refererDoc) {
            $this->refererDoc = new_doc($this->dbaccess, $this->refererid, true);
            $this->refererDocId = $this->refererDoc->id;
            if ($this->waitingDoc) {
                $this->values = serialize($this->waitingDoc->getValues());
                $this->waitingDoc = null;
            }
        } else {
            if (($this->refererDoc->id != $this->refererDocId) || ($fix = $this->refererDoc->isFixed()) || ($fix === null)) {
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
     * the referer with new values, document ready to update original
     *
     * @return Doc the document
     */
    public function getWaitingDocument()
    {
        $cdoc = $this->getRefererDocument(); // refresh referer if needed
        if (!$this->waitingDoc) {
            if (!$cdoc) {
                $cdoc = createDoc($this->dbaccess, $this->fromid, false, false);
            }
            $this->waitingDoc = clone $cdoc;
            $waitValues = unserialize($this->values);
            foreach ($waitValues as $aid => $v) {
                if ($v == '') $v = ' ';
                $this->waitingDoc->setValue($aid, $v);
            }
            $this->waitingDoc->doctype = 'I';
        }
        return $this->waitingDoc;
    }
    /**
     * verify if waiting document status is valid
     *
     * @return boolean true if valid
     */
    public function isValid()
    {
        return ($this->status == self::newDocument || $this->status == self::modified || $this->status == self::upToDate);
    }
    /**
     * verify if origin values are same as current alive values
     *
     * @param integer $mask identifier to use specific mask to detect W attributes
     *
     * @brief restrict to W/O visibilities values
     * @return integer ths status code
     */
    public function computeStatus($mask = 0)
    {
        if ($this->status != self::invalid) {
            if ($this->refererid) {
                $originValues = unserialize($this->orivalues);
                $currentDoc = $this->getRefererDocument();
                if ($currentDoc->isAlive()) {
                    $err = $currentDoc->canEdit(false);
                    if ($err) {
                        $this->statusmessage = $err;
                        $this->status = self::conflict;
                    } else {
                        if ($currentDoc->locked != $currentDoc->getSystemUserId()) {
                            $this->statusmessage = sprintf("document %s [%d] not locked", $currentDoc->getTitle() , $currentDoc->id);
                            $this->status = self::conflict;
                        } else {
                            if ($mask) $currentDoc->ApplyMask($mask);
                            $attrs = $this->getWriteAttribute($currentDoc);
                            $this->status = self::upToDate;
                            $this->statusmessage = '';
                            /*print_r2(array(
                                "cur" => $currentDoc->getValues(),
                                "wai" => $this->getWaitingDocument()->getValues(),
                                "ori" => $originValues
                            ));
                            */
                            $waitingDoc = $this->getWaitingDocument();
                            /**
                             * @var NormalAttribute $oa
                             */
                            foreach ($attrs as $aid => $oa) {
                                $ovalue = $originValues[$oa->id];
                                $cvalue = $currentDoc->getRawValue($oa->id);
                                if ($ovalue != $cvalue) {
                                    $this->status = self::conflict;
                                    $this->statusmessage.= sprintf(_("conflict %s [%s]: referer=%s, modified=%s") , $oa->getLabel() , $oa->id, $cvalue, $ovalue) . "\n";
                                }
                            }
                            $this->statusmessage = substr($this->statusmessage, 0, -1);
                        }
                    }
                } else {
                    $this->statusmessage = sprintf("document with id %d does not exists", $this->refererid);
                    $this->status = self::conflict;
                }
            } else {
                $this->status = self::newDocument;
            }
            $this->modify();
        }
        return $this->status;
    }
    /**
     * get extrat data of waiting document
     *
     * @return string the data
     */
    public function getExtraData()
    {
        return ($this->extradata) ? json_decode($this->extradata) : null;
    }
}
?>
