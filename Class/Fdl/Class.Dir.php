<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Folder document definition
 *
 * @author Anakeen
 * @version $Id: Class.Dir.php,v 1.81 2008/09/03 08:35:24 marc Exp $
 * @package FDL
 */
/**
 */

include_once ("FDL/Class.PDir.php");
include_once ("FDL/Class.QueryDir.php");
/**
 * Folder document Class
 *
 */
class Dir extends PDir
{
    
    var $defDoctype = 'D';
    private $authfam = false;
    private $norestrict = false;
    
    public $eviews = array(
        "FDL:EDITBODYCARD",
        "FDL:EDITRESTRICTION"
    );
    function __construct($dbaccess = '', $id = '', $res = '', $dbid = 0)
    {
        parent::__construct($dbaccess, $id, $res, $dbid);
        if ($this->fromid == "") $this->fromid = FAM_DIR;
    }
    /**
     * get the home and basket folder
     * @param bool $create set to false to disable auto creation
     * @return bool|\Dir false if the dir does not exists and $create is false, home document either
     */
    function GetHome($create = true)
    {
        global $action;
        
        include_once ("FDL/freedom_util.php");
        include_once ("FDL/Lib.Dir.php");
        $rq = internalGetDocCollection($this->dbaccess, 0, 0, 1, array(
            "owner = -" . $this->userid
        ) , $this->userid, "LIST", "DIR");
        
        if (count($rq) > 0) $home = $rq[0];
        else {
            if (!$create) return false;
            /** @var Dir $home */
            $home = createDoc($this->dbaccess, "DIR");
            
            if (!$home) $action->exitError(sprintf(_("no privilege to create this kind (%d) of document") , getFamIdFromName($this->dbaccess, "DIR")));
            
            $home->owner = - $this->userid;
            include_once ("Class.User.php");
            $user = new Account("", $this->userid);
            $home->title = $user->firstname . " " . $user->lastname;
            $home->setTitle($home->title);
            $home->icon = 'fldhome.gif';
            $home->name = 'FLDHOME_' . $this->getSystemUserId();
            $home->Add();
            /** @var DocSearch $privlocked */
            $privlocked = createDoc($this->dbaccess, "SEARCH");
            if (!$privlocked) $action->exitError(sprintf(_("no privilege to create this kind (%d) of document") , getFamIdFromName($this->dbaccess, "SEARCH")));
            
            $privlocked->title = (_("locked document of ") . $home->title);
            $privlocked->Add();
            $privlocked->AddQuery("select * from doc where (doctype!='Z') and" . " (locked=" . $this->userid . ") ");
            $home->insertDocument($privlocked->id);
        }
        // add basket in home
        if (getParam("FREEDOM_IDBASKET") == "") {
            
            $bas = createDoc($this->dbaccess, "BASKET");
            if (!$bas) $action->exitError(sprintf(_("no privilege to create this kind (%d) of document") , getFamIdFromName($this->dbaccess, "BASKET")));
            
            $query = new QueryDb($this->dbaccess, "_BASKET");
            $query->AddQuery("owner = " . $this->userid);
            $rq = $query->Query();
            if ($query->nb == 0) {
                $bas->setvalue("ba_title", _("Document basket"));
                $bas->setvalue("ba_desc", sprintf(_("basket of %s") , $home->title));
                $home->name = sprintf('FLDBASKET_%d', $this->getSystemUserId());
                $bas->Add();
                $home->insertDocument($bas->id);
                $basid = $bas->id;
            } else {
                $basid = $rq[0]->id;
            }
            global $action;
            $action->parent->param->Set("FREEDOM_IDBASKET", $basid, Param::PARAM_USER . $this->userid, $action->parent->GetIdFromName("FREEDOM"));
        }
        
        return $home;
    }
    /**
     * clear containt of this folder
     *
     * @return string error message, if no error empty string
     */
    function Clear()
    {
        if ($this->isLocked(true)) return sprintf(_("folder is locked. Cannot containt modification"));
        // need this privilege
        $err = $this->Control("modify");
        if ($err != "") return $err;
        $this->addHistoryEntry(_("Folder cleared"));
        $this->addLog('clearcontent');
        $err = $this->exec_query("delete from fld where dirid=" . $this->initid);
        $this->updateFldRelations();
        return $err;
    }
    /**
     * hook method use before insert document in folder
     *
     * @api hook method use before insert document in folder
     *
     * @param int $docid document identifier to insert
     * @param bool $multiple flag to indicate if the insertion is a part of grouped insertion
     * @return string error message if not empty the insertion will be aborted
     */
    function preInsertDocument($docid, $multiple = false)
    {
        return "";
    }
    /**
     * virtual method use before insert document in folder
     *
     * @deprecated hook use Dir::preInsertDocument instead
     *
     * @param int $docid document identifier to insert
     * @param bool $multiple flag to indicate if the insertion is a part of grouped insertion
     * @return string error message if not empty the insert will be aborted
     */
    function preInsertDoc($docid, $multiple = false)
    {
        deprecatedFunction("hook");
    }
    /**
     * virtual method use after insert document in folder
     * @api hook method called after insert document in folder
     * @see Dir::insertDocument
     * @param int $docid document identifier to insert
     * @param bool $multiple flag to indicate if the insertion is a part of grouped insertion
     * @return string error message
     */
    function postInsertDocument($docid, $multiple = false)
    {
        return "";
    }
    /**
     * virtual method use after insert document in folder
     *
     * @deprecated hook use Dir::postInsertDocument instead
     *
     * @param int $docid document identifier to insert
     * @param bool $multiple flag to indicate if the insertion is a part of grouped insertion
     * @return string error message
     */
    function postInsertDoc($docid, $multiple = false)
    {
        deprecatedFunction("hook");
    }
    /**
     * hook method use after insert multiple document in this folder
     * must be redefined to optimize algorithm
     *
     *
     * @api hook method called after insert several documents in folder
     * @see Dir::insertMultipleDocuments
     * @see Dir::postInsertDocument
     *
     * @param array $tdocid array of document identifier to insert
     * @return string warning message
     */
    function postInsertMultipleDocuments($tdocid)
    {
        return '';
    }
    /**
     * hook method use after insert multiple document in this folder
     * must be redefined to optimize algorithm
     *
     * @api hook method called before insert several documents in folder
     * @see Dir::preInsertDocument
     * @see Dir::insertMultipleDocuments
     *
     * @param array $tdocid array of document identifier to insert
     * @return string warning message
     */
    function preInsertMultipleDocuments($tdocid)
    {
        return '';
    }
    /**
     * hook method use after insert multiple document in this folder
     * must be redefined to optimize algorithm
     *
     * @deprecated hook use {@Dir::postInsertMultipleDocuments} instead
     *
     * @param array $tdocid array of document identifier to insert
     * @return string warning message
     */
    function postMInsertDoc($tdocid)
    {
        deprecatedFunction("hook");
        return $this->postInsertMultipleDocuments($tdocid);
    }
    /**
     * hook method use after unlink document in folder
     *
     * @api hook method use before remove document to folder
     *
     * @param int $docid document identifier to unlink
     * @param bool $multiple flag to indicate if the insertion is a part of grouped insertion
     * @return string error message if not empty the insert will be aborted
     */
    function preRemoveDocument($docid, $multiple = false)
    {
    }
    /**
     * hook method use after unlink document in folder
     *
     * @api hook method use after remove document of folder
     *
     * @param int $docid document identifier to unlink
     * @param bool $multiple flag to indicate if the insertion is a part of grouped insertion
     * @return string error message
     */
    function postRemoveDocument($docid, $multiple = false)
    {
        return "";
    }
    /**
     * hook method use after unlink document in folder
     *
     * @deprecated hook use {@link Doc::preRemoveDocument} instead
     *
     * @param int $docid document identifier to unlink
     * @param bool $multiple flag to indicate if the insertion is a part of grouped insertion
     * @return string error message if not empty the insert will be aborted
     */
    function preUnlinkDoc($docid, $multiple = false)
    {
        deprecatedFunction("hook");
    }
    /**
     * hook method use after unlink document in folder
     *
     * @deprecated hook use {@link Doc::postRemoveDocument} instead
     *
     * @param int $docid document identifier to unlink
     * @param bool $multiple flag to indicate if the insertion is a part of grouped insertion
     * @return string error message
     */
    function postUnlinkDoc($docid, $multiple = false)
    {
        deprecatedFunction("hook");
    }
    /**
     * Test if current user can add or delete document in this folder
     *
     * @return string error message, if no error empty string
     */
    function canModify()
    {
        if ($this->isLocked(true)) return sprintf(_("folder is locked. Cannot containt modification"));
        // need this privilege
        $err = $this->Control("modify");
        return $err;
    }
    /**
     * add a document reference in this folder
     *
     * if mode is latest the user always see latest revision
     * if mode is static the user see the revision which has been inserted
     *
     * @deprecated use {@link Dir::insertDocument} instead
     * @see Dir::insertDocument
     *
     * @param int $docid document ident for the insertion
     * @param string $mode latest|static
     * @param bool $noprepost if true if the virtuals methods {@link Dir::preInsertDoc()} and {@link Dir::postInsertDoc()} are not called
     * @param bool $forcerestrict if true don't test restriction (if have)
     * @param bool $nocontrol if true no test acl "modify"
     * @return string error message, if no error empty string
     */
    function AddFile($docid, $mode = "latest", $noprepost = false, $forcerestrict = false, $nocontrol = false)
    {
        deprecatedFunction();
        return $this->insertDocument($docid, $mode, $noprepost, $forcerestrict, $nocontrol);
    }
    /**
     * add a document reference in this folder
     *
     * if mode is latest the user always see latest revision
     * if mode is static the user see the revision which has been inserted
     *
     * @api add a document reference in this folder
     *
     * @param int $docid document ident for the insertion
     * @param string $mode latest|static
     * @param bool $noprepost if true if the virtuals methods {@link Dir::preInsertDoc()} and {@link Dir::postInsertDoc()} are not called
     * @param bool $forcerestrict if true don't test restriction (if have)
     * @param bool $nocontrol if true no test acl "modify"
     * @return string error message, if no error empty string
     */
    function insertDocument($docid, $mode = "latest", $noprepost = false, $forcerestrict = false, $nocontrol = false)
    {
        $err = '';
        if (!$nocontrol) {
            $err = $this->canModify();
            if ($err != "") return $err;
        }
        
        $doc = new_Doc($this->dbaccess, $docid);
        $qf = new QueryDir($this->dbaccess);
        switch ($mode) {
            case "static":
                $qf->qtype = 'F'; // fixed document
                $qf->childid = $doc->id; // initial doc
                break;

            case "latest":
            default:
                if (!$doc->isAffected()) {
                    return sprintf(_("Cannot add in %s folder, doc id (%d) unknown") , $this->title, $docid);
                }
                $qf->qtype = 'S'; // single user query
                $qf->childid = $doc->initid; // initial doc
                break;
        }
        $qf->dirid = $this->initid; // the reference folder is the initial id
        $qf->query = "";
        if (!$qf->Exists()) {
            // use pre virtual method
            if (!$noprepost) $err = $this->preInsertDocument($doc->id);
            if ($err != "") return $err;
            // verify if doc family is autorized
            if ((!$forcerestrict) && (!$this->isAuthorized($doc->fromid))) return sprintf(_("Cannot add %s in %s folder, restriction set to add this kind of document") , $doc->title, $this->title);
            
            $err = $qf->Add();
            if ($err == "") {
                AddLogMsg(sprintf(_("Add %s in %s folder") , $doc->title, $this->title));
                $this->addHistoryEntry(sprintf(_("Document %s inserted") , $doc->title));
                $doc->addHistoryEntry(sprintf(_("Document inserted in %s folder") , $this->title, HISTO_INFO, "MOVEADD"));
                
                $this->addLog('addcontent', array(
                    "insert" => array(
                        "id" => $doc->id,
                        "title" => $doc->title
                    )
                ));
                // add default folder privilege to the doc
                if ($doc->profid == 0) { // only if no privilege yet
                    switch ($doc->defProfFamId) {
                        case FAM_ACCESSDOC:
                            $profid = $this->getRawValue("FLD_PDOCID", 0);
                            if ($profid > 0) {
                                $doc->setProfil($profid);
                                $err = $doc->modify(true, array(
                                    "profid",
                                    "dprofid"
                                ) , true);
                                if ($err == "") $doc->addHistoryEntry(sprintf(_("Change profil to default document profil : %d") , $profid));
                            }
                            break;

                        case FAM_ACCESSDIR:
                            $profid = $this->getRawValue("FLD_PDIRID", 0);
                            if ($profid > 0) {
                                $doc->setProfil($profid);
                                // copy default privilege if not set
                                if ($doc->getRawValue("FLD_PDIRID") == "") {
                                    $doc->setValue("FLD_PDIRID", $this->getRawValue("FLD_PDIRID"));
                                    $doc->setValue("FLD_PDIR", $this->getRawValue("FLD_PDIR"));
                                }
                                if ($doc->getRawValue("FLD_PDOCID") == "") {
                                    $doc->setValue("FLD_PDOCID", $this->getRawValue("FLD_PDOCID"));
                                    $doc->setValue("FLD_PDOC", $this->getRawValue("FLD_PDOC"));
                                }
                                $err = $doc->modify();
                                if ($err == "") $doc->addHistoryEntry(sprintf(_("Change profil to default subfolder profil : %d") , $profid));
                            }
                            break;
                        }
                    }
            }
            if ($doc->prelid == "") {
                $doc->prelid = $this->initid;
                $doc->modify(true, array(
                    "prelid"
                ) , true);
            }
            
            if ($err == "") {
                global $action;
                $action->AddActionDone("ADDFILE", $this->initid);
                
                $this->updateFldRelations();
                // use post virtual method
                if (!$noprepost) $err = $this->postInsertDocument($doc->id, false);
            }
        }
        return $err;
    }
    // --------------------------------------------------------------------
    
    /**
     * insert multiple document reference in this folder
     *
     * if mode is latest the user always see latest revision
     * if mode is static the user see the revision which has been inserted
     *
     * @deprecated use {@link Dir::InsertMultipleDocuments} instead
     * @see insertMultipleDocuments::InsertMultipleDocuments
     *
     * @param $tdocs
     * @param string $mode latest|static
     * @param boolean $noprepost not call preInsert and postInsert method (default if false)
     * @param array $tinserted
     * @param array $twarning
     * @internal param \doc $array array document  for the insertion
     * @return string error message, if no error empty string
     */
    function InsertMDoc($tdocs, $mode = "latest", $noprepost = false, &$tinserted = array() , &$twarning = array())
    {
        deprecatedFunction();
        return $this->insertMultipleDocuments($tdocs, $mode, $noprepost, $tinserted, $twarning);
    }
    /**
     * insert multiple document reference in this folder
     *
     * if mode is latest the user always see latest revision
     * if mode is static the user see the revision which has been inserted
     *
     * @api insert multiple document reference in this folder
     *
     * @param array $tdocs documents  for the insertion
     * @param string $mode latest|static static is not implemented yet
     * @param boolean $noprepost not call preInsert and postInsert method (default if false)
     * @param array $tinserted
     * @param array $twarning
     * @param array $info
     * @return string error message, if no error empty string
     */
    function insertMultipleDocuments(array $tdocs, $mode = "latest", $noprepost = false, &$tinserted = array() , &$twarning = array() , &$info = array())
    {
        $insertError = array();
        if (!$noprepost) {
            $tdocids = array();
            $isStatic = ($mode === "static");
            foreach ($tdocs as $v) {
                if (!empty($v["initid"])) {
                    $tdocids[] = ($isStatic) ? $v["id"] : $v["initid"];
                }
            }
            $err = $this->preInsertMultipleDocuments($tdocids);
            $info = array(
                "error" => $err,
                "preInsertMultipleDocuments" => $err,
                "postInsertDocument" => array() ,
                "postInsertMultipleDocuments" => '',
                "preInsertDocument" => array() ,
                "modifyError" => ""
            );
            if ($err != "") return $err;
        }
        $err = $this->canModify();
        if ($err != "") {
            $info = array(
                "error" => $err,
                "preInsertMultipleDocuments" => "",
                "postInsertDocument" => array() ,
                "postInsertMultipleDocuments" => '',
                "preInsertDocument" => array() ,
                "modifyError" => $err
            );
            return $err;
        }
        $tAddeddocids = array();
        // verify if doc family is autorized
        $qf = new QueryDir($this->dbaccess);
        $tmsg = array();
        foreach ($tdocs as $tdoc) {
            if (!$this->isAuthorized($tdoc["fromid"])) {
                $warn = sprintf(_("Cannot add %s in %s folder, restriction set to add this kind of document") , $tdoc["title"], $this->title);
                $twarning[$tdoc['id']] = $warn;
            } else {
                switch ($mode) {
                    case "static":
                        
                        $qf->qtype = 'F'; // fixed document
                        $docid = $tdoc["id"];
                        $qf->childid = $tdoc["id"]; // initial doc
                        break;

                    case "latest":
                    default:
                        
                        $qf->qtype = 'S'; // single user query
                        $docid = $tdoc["initid"];
                        $qf->childid = $tdoc["initid"]; // initial doc
                        break;
                }
                
                $insertOne = "";
                $qf->dirid = $this->initid; // the reference folder is the initial id
                $qf->query = "";
                // use post virtual method
                if (!$noprepost) $insertOne = $this->preInsertDocument($tdoc["initid"], true);
                
                if ($insertOne == "") {
                    $insertOne = $qf->Add();
                    if ($insertOne == "") {
                        AddLogMsg(sprintf(_("Add %s in %s folder") , $tdoc["title"], $this->title));
                        $this->addHistoryEntry(sprintf(_("Document %s inserted") , $tdoc["title"]) , HISTO_INFO, "MODCONTAIN");
                        
                        $this->addLog('addcontent', array(
                            "insert" => array(
                                "id" => $tdoc["id"],
                                "title" => $tdoc["title"]
                            )
                        ));
                        $tAddeddocids[] = $docid;
                        $tinserted[$docid] = sprintf(_("Document %s inserted") , $tdoc["title"]);
                        // use post virtual method
                        if (!$noprepost) {
                            $tmsg[$docid] = $this->postInsertDocument($tdoc["initid"], true);
                        }
                    }
                } else {
                    $twarning[$docid] = $insertOne;
                }
                $insertError[$docid] = $insertOne;
            }
        }
        // use post virtual method
        $msg = '';
        if (!$noprepost) {
            $this->updateFldRelations();
            $msg = $this->postInsertMultipleDocuments($tAddeddocids);
            $err.= $msg;
        }
        // integrate pre insert errors
        foreach ($insertError as $oneError) {
            if ($oneError) {
                $err.= ($err) ? ', ' : '';
                $err.= $oneError;
            }
        }
        // integrate postInsert Error
        foreach ($tmsg as $oneError) {
            if ($oneError) {
                $err.= ($err) ? ', ' : '';
                $err.= $oneError;
            }
        }
        $info = array(
            "error" => $err,
            "preInsertMultipleDocuments" => '',
            "postInsertDocument" => $tmsg,
            "postInsertMultipleDocuments" => $msg,
            "preInsertDocument" => $insertError,
            "modifyError" => ""
        );
        return $err;
    }
    /**
     * insert multiple static document reference in this folder
     * be carreful : not verify restriction folders
     * to be use when many include (verification constraint must ne set before by caller)
     *
     * @param array $tdocids identifier documents  for the insertion
     * @return string error message, if no error empty string
     */
    function QuickInsertMSDocId($tdocids)
    {
        
        $err = $this->canModify();
        if ($err != "") return $err;
        $qf = new QueryDir($this->dbaccess);
        $qf->qtype = 'S'; // single user query
        $qf->dirid = $this->initid; // the reference folder is the initial id
        $qf->query = "";
        foreach ($tdocids as $docid) {
            $tcopy[$docid]["childid"] = $docid;
        }
        
        $err = $qf->Adds($tcopy, true);
        $this->updateFldRelations();
        
        return $err;
    }
    /**
     * insert all static document which are included in $docid in this folder
     * be carreful : not verify restriction folders
     * to be use when many include (verification constraint must ne set before by caller)
     *
     * @param int $docid identifier document  for the insertion  (must be initial id)
     * @return string error message, if no error empty string
     */
    function insertFolder($docid)
    {
        if (!is_numeric($docid)) return sprintf(_("Dir::insertFolder identifier [%s] must be numeric") , $docid);
        if ($this->isLocked(true)) return sprintf(_("folder is locked. Cannot containt modification"));
        // need this privilege
        $err = $this->Control("modify");
        if ($err != "") return $err;
        
        $err = $this->exec_Query(sprintf("insert INTO fld (select %d,query,childid,qtype from fld where dirid=%d);", $this->initid, $docid));
        
        $this->updateFldRelations();
        return $err;
    }
    // --------------------------------------------------------------------
    function getQids($docid)
    {
        // return array of queries id includes in a directory
        // --------------------------------------------------------------------
        $tableid = array();
        
        $doc = new_Doc($this->dbaccess, $docid);
        $query = new QueryDb($this->dbaccess, "QueryDir");
        $query->AddQuery("dirid=" . $this->id);
        $query->AddQuery("((childid=$docid) and (qtype='F')) OR ((childid={$doc->initid}) and (qtype='S'))");
        $tableq = $query->Query();
        
        if ($query->nb > 0) {
            foreach ($tableq as $k => $v) {
                $tableid[$k] = $v->id;
            }
            unset($tableq);
        }
        
        return ($tableid);
    }
    // --------------------------------------------------------------------
    
    /**
     * remove a document reference from this folder
     *
     * @deprecated use {@link Dir::removeDocument} instead
     * @see Dir::removeDocument
     *
     * @param int $docid document ident for the deletion
     * @param bool $noprepost if true then the virtuals methods {@link Dir::preUnlinkDoc()} and {@link Dir::postUnlinkDoc()} are not called
     * @param bool $nocontrol if true no test acl "modify"
     * @return string error message, if no error empty string
     */
    function DelFile($docid, $noprepost = false, $nocontrol = false)
    {
        deprecatedFunction();
        return $this->removeDocument($docid, $noprepost, $nocontrol);
    }
    /**
     * delete a document reference from this folder
     *
     * @api remove a document reference from this folder
     *
     * @param int $docid document ident for the deletion
     * @param bool $noprepost if true then the virtuals methods {@link Dir::preRemoveDocument()} and {@link Dir::postRemoveDocument()} are not called
     * @param bool $nocontrol if true no test acl "modify"
     * @return string error message, if no error empty string
     */
    function removeDocument($docid, $noprepost = false, $nocontrol = false)
    {
        $err = '';
        if (!$nocontrol) {
            $err = $this->canModify();
            if ($err != "") return $err;
        }
        // use pre virtual method
        if (!$noprepost) $err = $this->preRemoveDocument($docid);
        if ($err != "") return $err;
        
        $doc = new_Doc($this->dbaccess, $docid);
        $docid = $doc->initid;
        //if (count($qids) == 0) $err = sprintf(_("cannot delete link : link not found for doc %d in folder %d"),$docid, $this->initid);
        if ($err != "") return $err;
        // search original query
        $qf = new QueryDir($this->dbaccess, array(
            $this->initid,
            $docid
        ));
        if (!($qf->isAffected())) $err = sprintf(_("cannot delete link : initial query not found for doc %d in folder %d") , $docid, $this->initid);
        
        if ($err != "") return $err;
        
        if ($qf->qtype == "M") $err = sprintf(_("cannot delete link for doc %d in folder %d : the document comes from a user query. Delete initial query if you want delete this document") , $docid, $this->initid);
        
        if ($err != "") return $err;
        $qf->Delete();
        
        if ($doc->prelid == $this->initid) {
            $doc->prelid = "";
            $doc->modify(true, array(
                "prelid"
            ) , true);
        }
        
        AddLogMsg(sprintf(_("Delete %d in %s folder") , $docid, $this->title));
        
        $this->addLog('delcontent', array(
            "insert" => array(
                "id" => $doc->id,
                "title" => $doc->title
            )
        ));
        $this->addHistoryEntry(sprintf(_("Document %s umounted") , $doc->title) , HISTO_INFO, "MODCONTAIN");
        $doc->addHistoryEntry(sprintf(_("Document unlinked of %s folder") , $this->title, HISTO_INFO, "MOVEUNLINK"));
        // use post virtual method
        if (!$noprepost) {
            $this->updateFldRelations();
            $err = $this->postRemoveDocument($docid);
        }
        
        global $action;
        $action->AddActionDone("DELFILE", $this->initid);
        
        return $err;
    }
    /**
     * move a document from me to a folder
     * @param integer $docid the document identifier to move
     * @param integer $movetoid target destination
     * @return string error message (empty if null)
     */
    function moveDocument($docid, $movetoid)
    {
        $err = $this->canModify();
        if ($err == "") {
            $fromtoid = $this->initid;
            /** @var Dir $da */
            $da = new_doc($this->dbaccess, $movetoid);
            if ($da->isAlive()) {
                if (method_exists($da, "addFile")) {
                    $err = $da->insertDocument($docid);
                    if ($err == "") {
                        if (($fromtoid) && ($fromtoid != $movetoid)) {
                            if ($this->isAlive()) {
                                if (method_exists($this, "delFile")) {
                                    $err = $this->removeDocument($docid);
                                    if ($err == "") {
                                        $doc = new_doc($this->dbaccess, $docid, true);
                                        if ($doc->isAlive()) {
                                            $doc->prelid = $da->initid;
                                            $err = $doc->modify(true, array(
                                                "prelid"
                                            ) , true);
                                        }
                                    }
                                } else $err = sprintf(_("document %s is not a folder") , $this->getTitle());
                            }
                        } else {
                            if ($err == "") {
                                $doc = new_doc($this->dbaccess, $docid, true);
                                if ($doc->isAlive()) {
                                    $doc->prelid = $da->initid;
                                    $err = $doc->modify(true, array(
                                        "prelid"
                                    ) , true);
                                }
                            }
                        }
                    }
                } else $err = sprintf(_("document %s is not a folder") , $da->getTitle());
            }
        }
        return $err;
    }
    // --------------------------------------------------------------------
    function postStore()
    {
        // don't see restriction frame is not needed
        $allbut = $this->getRawValue("FLD_ALLBUT");
        $tfamid = $this->getMultipleRawValues("FLD_FAMIDS");
        
        if (($allbut === "0") && ((count($tfamid) == 0) || ((count($tfamid) == 1) && ($tfamid[0] == 0)))) {
            $this->clearValue("FLD_ALLBUT");
            $this->modify();
        }
        return "";
    }
    /**
     * @deprecated use postStore() instead
     * @return string
     */
    public function postModify()
    {
        deprecatedFunction();
        return self::postStore();
    }
    
    function hasNoRestriction()
    {
        if (!$this->authfam) {
            $this->getAuthorizedFamilies();
        }
        return ($this->norestrict);
    }
    /**
     * return families that can be use in insertion
     * @param int $classid : restrict for same usefor families
     * @param bool $verifyCreate set to true if you want to get only families the user can create (icreate acl)
     * @return array
     */
    function getAuthorizedFamilies($classid = 0, $verifyCreate = false)
    {
        
        if (!$this->authfam) {
            $tfamid = $this->getMultipleRawValues("FLD_FAMIDS");
            $tfam = $this->getMultipleRawValues("FLD_FAM");
            $tsubfam = $this->getMultipleRawValues("FLD_SUBFAM");
            $allbut = $this->getRawValue("FLD_ALLBUT");
            
            if (($allbut != "1") && ((count($tfamid) == 0) || ((count($tfamid) == 1) && ($tfamid[0] == 0)))) {
                $this->norestrict = true;
                return array();
            }
            
            $this->norestrict = false;;
            $tclassdoc = array();
            if ($allbut != "1") {
                include_once ("FDL/Lib.Dir.php");
                $tallfam = GetClassesDoc($this->dbaccess, $this->userid, $classid, "TABLE");
                
                foreach ($tallfam as $cdoc) {
                    $tclassdoc[$cdoc["id"]] = $cdoc;
                    //	  $tclassdoc += $this->GetChildFam($cdoc["id"]);
                    
                }
                // suppress undesirable families
                reset($tfamid);
                foreach ($tfamid as $k => $famid) {
                    
                    unset($tclassdoc[intval($famid) ]);
                    if ($tsubfam[$k] != "yes") {
                        $tnofam = $this->GetChildFam(intval($famid));
                        foreach ($tnofam as $ka => $va) {
                            unset($tclassdoc[intval($ka) ]);
                        }
                    }
                }
            } else {
                //add families
                foreach ($tfamid as $k => $famid) {
                    $tfdoc = getTDoc($this->dbaccess, $famid);
                    if ($tfdoc && ((!$verifyCreate) || controlTdoc($tfdoc, 'icreate'))) {
                        $tclassdoc[intval($famid) ] = array(
                            "id" => ($tsubfam[$k] == "no") ? (-intval($famid)) : intval($famid) ,
                            "title" => $tfam[$k]
                        );
                    }
                    if ($tsubfam[$k] != "no") $tclassdoc+= $this->GetChildFam(intval($famid));
                }
            }
            $this->authfam = $tclassdoc;
        }
        return $this->authfam;
    }
    /**
     * return families that can be use in insertion
     * @param int $classid : restrict for same usefor families
     * @return bool
     */
    public function isAuthorized($classid)
    {
        if (!$this->authfam) {
            $this->getAuthorizedFamilies();
        }
        if ($this->norestrict) return true;
        if (!$classid) return true;
        
        if (isset($this->authfam[$classid])) return true;
        
        return false;
    }
    /**
     * return document includes in folder
     * @param bool $controlview if false all document are returned else only visible for current user  document are return
     * @param array $filter to add list sql filter for selected document
     * @param int|string $famid family identifier to restrict search
     * @param string $qtype type os result TABLE|LIST|ITEM
     * @param string $trash
     * @return array array of document array
     */
    public function getContent($controlview = true, array $filter = array() , $famid = "", $qtype = "TABLE", $trash = "")
    {
        include_once ("FDL/Lib.Dir.php");
        if ($controlview) $uid = $this->userid;
        else $uid = 1;
        $tdoc = internalGetDocCollection($this->dbaccess, $this->initid, 0, "ALL", $filter, $uid, $qtype, $famid, false, "title", true, $trash);
        return $tdoc;
    }
    /**
     * update folder relations
     */
    public function updateFldRelations()
    {
        return; //inhibit folder relation (too slow for great folder)
        
    }
    /**
     * return number of item in the static folder
     * @param bool $onlyprimary set to true if you wnat only document linked by primary relation
     * @return int -1 if it is not a static folder
     */
    public function count($onlyprimary = false)
    {
        if ($onlyprimary) {
            $tdoc = $this->getPrimaryChild();
            if ($tdoc) return count($tdoc);
        } else {
            $q = new QueryDb($this->dbaccess, "QueryDir");
            $tv = $q->Query(0, 0, "TABLE", "select childid from fld where dirid=" . $this->initid . " and qtype='S'");
            if (is_array($tv)) return count($tv);
        }
        return -1;
    }
    /**
     * return array of document identificators included in folder
     * @return array of initial identificators (initid)
     */
    public function getContentInitid()
    {
        $query = sprintf("select childid from fld where dirid=%d and qtype='S'", $this->initid);
        $initids = array();
        $err = simpleQuery($this->dbaccess, $query, $initids, true, false);
        if ($err == "") return $initids;
        
        return array();
    }
    /**
     * get  document which primary relation is this folder
     *
     *
     * @return array of doc  (array document)
     */
    public function getPrimaryChild()
    {
        $filter[] = "prelid=" . $this->initid;
        return $this->getContent(true, $filter);
    }
    
    protected function postAffect(array $data, $more, $reset)
    {
        $this->authfam = false;
        $this->norestrict = false;
    }
    /**
     * delete all document which primary relation is the folder (recurively)
     * different of {@link Dir::Clear()}
     * all document are put in the trash (zombie mode)
     * @return array of possible errors. Empty array means no errors
     */
    public function deleteItems()
    {
        $filter[] = "prelid=" . $this->initid;
        $lpdoc = $this->getContent(false, $filter, "", "ITEM");
        
        $terr = array();
        while ($doc = getNextDoc($this->dbaccess, $lpdoc)) {
            $coulddelete = true;
            if ($doc->doctype == 'D') {
                /** @var Dir $doc */
                $terr = array_merge($terr, $doc->deleteItems());
                foreach ($terr as $err) {
                    if ($err != "") $coulddelete = false;
                }
            }
            if ($coulddelete) $terr[$doc->id] = $doc->delete();
        }
        $this->addHistoryEntry(_("Folder cleared") , HISTO_INFO, "MODCONTAIN");
        return $terr;
    }
    /**
     * copy (clone) all documents which primary relation is the folder (recurively)
     * the others documents are just linked
     * all document are put in $indirid folder id
     * @param int $indirid the folder where put the copies
     * @return array of possible errors. Empty array means no errors
     */
    public function copyItems($indirid)
    {
        $filter = array();
        $lpdoc = $this->getContent(false, $filter, "", "ITEM");
        
        $terr = array();
        $fld = new_doc($this->dbaccess, $indirid);
        if ($fld->doctype == 'D') {
            /** @var Dir $fld */
            $err = $fld->control("modify");
            if ($err == "") {
                while ($doc = getNextDoc($this->dbaccess, $lpdoc)) {
                    if ($doc->prelid == $this->initid) {
                        // copy
                        $copy = $doc->duplicate();
                        if (is_object($copy)) {
                            $fld->insertDocument($copy->initid);
                            if ($doc->doctype == 'D') {
                                /** @var Dir $doc */
                                $terr = array_merge($terr, $doc->copyItems($copy->id));
                            }
                        }
                    } else {
                        // link
                        $fld->insertDocument($doc->initid);
                    }
                }
            }
        }
        return $terr;
    }
    /**
     * delete the folder and its containt
     * different of {@link Dir::Clear()}
     * all document are put in the trash (zombie mode)
     * @return string error message, if no error empty string
     */
    public function deleteRecursive()
    {
        $err = $this->predocdelete(); // test before try recursive deletion
        if ($err != "") return $err;
        $coulddelete = true;
        $terr = $this->deleteItems();
        $err = "";
        foreach ($terr as $err1) {
            if ($err1 != "") {
                $coulddelete = false;
                $err.= "\n$err1";
            }
        }
        if ($coulddelete) $err = $this->delete();
        return $err;
    }
    /**
     * restore all document which primary relation is the folder (recurively)
     *
     *
     * @return array an array of errors
     */
    function reviveItems()
    {
        $filter[] = "prelid=" . $this->initid;
        $lpdoc = $this->getContent(true, $filter, "", "ITEM", "only");
        $terr = array();
        while ($doc = getNextDoc($this->dbaccess, $lpdoc)) {
            if ($doc->defDoctype == 'D') {
                /**
                 * @var Dir $doc
                 */
                $terr = array_merge($terr, $doc->reviveItems());
            }
            $terr[$doc->id] = $doc->undelete();
        }
        return $terr;
    }
}
