<?php
/**
 * Detailled search
 *
 * @author Anakeen 2000
 * @version $Id: Method.DetailSearch.php,v 1.73 2009/01/08 17:52:54 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage GED
 */
/**
 */


/**
 * @begin-method-ignore
 * this part will be deleted when construct document class until end-method-ignore
 */
Class _ARCHIVING extends Dir {
	/*
	 * @end-method-ignore
	 */
	
    
        /**
         * all document's folder are archieved
         * @return string error message empty message if no error
         */
        function arc_close() {
<<<<<<< HEAD
            $err=$this->setValue("arc_status","C");
            if (! $err) $err=$this->modify();
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> archive is a folder (refs #287)
            
=======
            $err="";
>>>>>>> Archive documents (refs #287)
            if (! $err) {
               include_once("FDL/Class.SearchDoc.php");
               
                $s=new SearchDoc($this->dbaccess);
                $s->dirid=$this->id;
                $s->orderby='';
                $s->setObjectReturn();
                $s->search();
                if (ini_get("max_execution_time") < 3600) ini_set("max_execution_time",3600);
                while ($doc=$s->nextDoc()) {
                    $doc->disableEditControl();  
                    $err.=$doc->archive($this);
                    $doc->enableEditControl();  
                }
            }
            
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> add family ARCHIVING (refs #297)
=======
>>>>>>> archive is a folder (refs #287)
=======
            $err=$this->setValue("arc_status","C");
            $err=$this->setValue("arc_clotdate",$this->getDate());
            if (! $err) $err=$this->modify();
            $this->addComment(sprintf(_("Close archive")));
>>>>>>> Archive documents (refs #287)
            return $err;
        }
        
        
         /**
         * all document's archivied by it are unarchieved
         * @return string error message empty message if no error
         */
        function arc_reopen() {
            $err=$this->setValue("arc_status","O");
            $err=$this->deleteValue("arc_clotdate");
            if (! $err) $err=$this->modify();  
                if (! $err) {
               include_once("FDL/Class.SearchDoc.php");
               
                $s=new SearchDoc($this->dbaccess);
                $s->addFilter("archiveid=%d",$this->id);
                $s->orderby='';
                $s->setObjectReturn();
                $s->search();
                if (ini_get("max_execution_time") < 3600) ini_set("max_execution_time",3600);
                while ($doc=$s->nextDoc()) {
                     $doc->disableEditControl();  
                    $err.=$doc->unArchive($this);
                    $doc->enableEditControl();  
                }
            }
            $this->addComment(sprintf(_("Reopen archive")));
            return $err;
        }
       /**
         * all document's archivied by it are unarchieved
         * @return string error message empty message if no error
         */
        function arc_purge() {
            $err=$this->setValue("arc_status","P");
            $err=$this->setValue("arc_purgedate",$this->getDate());
            if (! $err) $err=$this->modify();
            if (! $err) {
                include_once("FDL/Class.SearchDoc.php");
                 
                $s=new SearchDoc($this->dbaccess);
                $s->addFilter("archiveid=%d",$this->id);
                $s->orderby='';
                $s->setObjectReturn();
                $s->search();
                if (ini_get("max_execution_time") < 3600) ini_set("max_execution_time",3600);
                $t="<ol>";
                while ($doc=$s->nextDoc()) {
                    if ($doc->doctype!='C') {
                        $t.=sprintf('<li><a href="?app=FDL&action=VIEWDESTROYDOC&id=%d">%s</a></li> ',$doc->id,$doc->title);
                        $doc->disableEditControl();
                        $doc->addComment(sprintf(_("destroyed by archive purge from %s"),$this->getTitle()));
                        $err.=$doc->delete(true,false);
                        $doc->enableEditControl();
                    }
                }
                $t.="</ol>";
                $err=$this->setValue("arc_purgemanif",$t);
                if (! $err) $err=$this->modify();
                $this->clear();
            $this->addComment(sprintf(_("Purge archive")));
            }
            return $err;
        }
        function postModify() {
            $err=parent::postModify();
            $err.=$this->createProfil();
            return $err;
        }
        
        function preInsertDoc() {
            if ($this->getValue("arc_status") != "O") {
                return _("archieve status must be open to modify content");
            } 
        }
        function preUnlinkDoc() {
            if ($this->getValue("arc_status") != "O") {
                return _("archieve status must be open to modify content");
            } 
        }
        /**
         * return specfic filters instead of normal content
         * @return array of sql filters
         */
        public function getSpecificFilters() {
            if ($this->getValue("arc_status") == "C") {
                return array(sprintf("archiveid=%d",$this->id));
            } 
            return array();
        }
        /**
         * create an init a profil to be use if document archived
         */
        function createProfil() {
            $prfid=$this->getValue("arc_profil");
            if ($prfid) {
                $prf=new_doc($this->dbaccess,$prfid);
                if (! $prf->isAlive()) $prfid=0; // redo the profil
                else {   
                    $prf->setValue("ba_title",sprintf(_("Profil for document's archive %s"),$this->getTitle()));
                    $prf->modify();
                }
            }
            
            if (! $prfid) {
                $prf=createDoc($this->dbaccess,"PDIR",false);
                $prf->setValue("ba_title",sprintf(_("Profil for document's archive %s"),$this->getTitle()));
                $prf->add();
                $prf->setControl();
                $err=$this->setValue("arc_profil",$prf->id);
                if (! $err) $err=$this->modify();
            }
            
            return $err;
        }
	/**
	* @begin-method-ignore
	* this part will be deleted when construct document class until end-method-ignore
	*/
}

/*
 * @end-method-ignore
 */

?>