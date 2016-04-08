<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * archive documents
 */
namespace Dcp\Core;
class Archiving extends \Dcp\Family\Dir
{
    /**
     * all document's folder are archieved
     * @apiExpose
     * @return string error message empty message if no error
     */
    function arc_close()
    {
        $err = $this->canEdit();
        if (!$err) {
            
            $s = new \SearchDoc($this->dbaccess);
            $s->dirid = $this->id;
            $s->orderby = '';
            $s->setObjectReturn();
            $s->search();
            
            setMaxExecutionTimeTo(3600);
            while ($doc = $s->getNextDoc()) {
                $doc->disableEditControl();
                $err.= $doc->archive($this);
                $doc->enableEditControl();
            }
            
            $err = $this->setValue("arc_status", "C");
            $err = $this->setValue("arc_clotdate", $this->getDate());
            if (!$err) $err = $this->modify();
            $this->addHistoryEntry(sprintf(_("Close archive")));
        }
        return $err;
    }
    /**
     * all document's archivied by it are unarchieved
     * @apiExpose
     * @return string error message empty message if no error
     */
    function arc_reopen()
    {
        $err = $this->canEdit();
        if (!$err) {
            $err = $this->setValue("arc_status", "O");
            $err = $this->clearValue("arc_clotdate");
            if (!$err) $err = $this->modify();
            if (!$err) {
                include_once ("FDL/Class.SearchDoc.php");
                
                $s = new \SearchDoc($this->dbaccess);
                $s->addFilter("archiveid=%d", $this->id);
                $s->orderby = '';
                $s->setObjectReturn();
                $s->search();
                
                setMaxExecutionTimeTo(3600);
                while ($doc = $s->getNextDoc()) {
                    $doc->disableEditControl();
                    $err.= $doc->unArchive($this);
                    $doc->enableEditControl();
                }
            }
            $this->addHistoryEntry(sprintf(_("Reopen archive")));
        }
        return $err;
    }
    /**
     * all document's archivied by it are unarchieved
     * @apiExpose
     * @return string error message empty message if no error
     */
    function arc_purge()
    {
        $err = $this->canEdit();
        if (!$err) {
            $err = $this->setValue("arc_status", "P");
            $err.= $this->setValue("arc_purgedate", $this->getDate());
            if (!$err) $err = $this->modify();
            if (!$err) {
                include_once ("FDL/Class.SearchDoc.php");
                
                $s = new \SearchDoc($this->dbaccess);
                $s->addFilter("archiveid=%d", $this->id);
                $s->orderby = '';
                $s->setObjectReturn();
                $s->search();
                
                setMaxExecutionTimeTo(3600);
                $t = "<ol>";
                while ($doc = $s->getNextDoc()) {
                    if ($doc->doctype != 'C') {
                        $t.= sprintf('<li><a href="?app=FDL&action=VIEWDESTROYDOC&id=%d">%s</a></li> ', $doc->id, $doc->title);
                        $doc->disableEditControl();
                        $doc->addHistoryEntry(sprintf(_("destroyed by archive purge from %s") , $this->getTitle()));
                        $err.= $doc->delete(true, false);
                        $doc->enableEditControl();
                    }
                }
                $t.= "</ol>";
                $err = $this->setValue("arc_purgemanif", $t);
                if (!$err) $err = $this->modify();
                $this->clear();
                $this->addHistoryEntry(sprintf(_("Purge archive")));
            }
        }
        return $err;
    }
    /**
     * delete all archive contain
     * @apiExpose
     * @return string
     */
    function arc_clear()
    {
        $err = $this->canEdit();
        if (!$err) {
            $err = $this->Clear();
        }
        return $err;
    }
    function postStore()
    {
        $err = parent::postStore();
        $err.= $this->createProfil();
        return $err;
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
    function preInsertDocument($docid, $multiple = false)
    {
        if ($this->getRawValue("arc_status") != "O") {
            return _("archieve status must be open to modify content");
        }
        return '';
    }
    function preRemoveDocument($docid, $multiple = false)
    {
        if ($this->getRawValue("arc_status") != "O") {
            return _("archieve status must be open to modify content");
        }
        return '';
    }
    /**
     * return specfic filters instead of normal content
     * @return array of sql filters
     */
    public function getSpecificFilters()
    {
        if ($this->getRawValue("arc_status") == "C") {
            return array(
                sprintf("archiveid=%d", $this->id)
            );
        }
        return array();
    }
    /**
     * create an init a profil to be use if document archived
     */
    function createProfil()
    {
        $err = '';
        $prfid = $this->getRawValue("arc_profil");
        if ($prfid) {
            $prf = new_doc($this->dbaccess, $prfid);
            if (!$prf->isAlive()) $prfid = 0; // redo the profil
            else {
                $prf->setValue("ba_title", sprintf(_("Profil for document's archive %s") , $this->getTitle()));
                $prf->modify();
            }
        }
        
        if (!$prfid) {
            $prf = createDoc($this->dbaccess, "PDIR", false);
            $prf->setValue("ba_title", sprintf(_("Profil for document's archive %s") , $this->getTitle()));
            $prf->add();
            $prf->setControl();
            $err = $this->setValue("arc_profil", $prf->id);
            if (!$err) $err = $this->modify();
        }
        
        return $err;
    }
}
