<?php
/**
 * Manage Waiting Doc
 *
 * @author Anakeen
 * @version $Id:  $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 */

include_once ("FDL/Class.DocWait.php");
include_once ("FDL/Class.Doc.php");

class DocWaitManager
{
    /**
     * create or update a new entry in docWait
     * 
     * @param Doc $doc
     * @return string error message
     */
    public static function saveWaitingDoc(Doc &$doc, $domainId=null, $transaction=null) {
        $err='';
        
        if ($doc) {
            $wd=new DocWait($doc->dbaccess, array($doc->initid, $doc->getSystemUserId()));
            
            $wd->uid=$doc->getSystemUserId();
            $wd->values=serialize($doc->getValues());
            $wd->status=docWait::recording;
            $wd->title=$doc->getTitle();
            $wd->fromid=$doc->fromid;
            $wd->refererid=$doc->id;
            $wd->refererinitid=$doc->initid;
            $wd->domain=$domainId;
            $wd->transaction=$transaction;
            $wd->date=date('Y-m-d H:i:s.u');
            
            if ($wd->isAffected()) {
                $err=$wd->modify();
            } else {
                $orivalues=$doc->getValues();
                $ori=new_doc($doc->dbaccess, $doc->id, true);
                if ($ori->isAlive()) {
                $wd->orivalues=serialize($ori->getValues());
                $wd->status=docWait::upToDate;
                }
                $err=$wd->add();
            }
        } else {
            $err=sprintf(_("waiting : document not set"));
        }
        return $err;
    }
    
    /**
     * Return waiting doc 
     * @param int $id doc identificator
     * @return Doc
     */
    public static function getWaitingDoc($id) {
        $wd=new DocWait(getDbAccess(), array($id, Doc::getSystemUserId()));
        
        if ($wd->isAffected()) {
            return $wd;
        }
         return null;
    }
    

   /**
     * return waiting doc for a transaction
     * @param int $transaction transaction identificator
     * @return DbObjectList docWait list
     */
    public static function getWaitingDocs($transaction)
    {
        $q=new QueryDb(getDbAccess(), "docWait");
        $q->addQuery(sprintf("transaction = %d", $transaction));
        
        return $q->Query(0,0,'ITER');
    }
    public static function getTransaction() {
        $err=simpleQuery(getDbAccess(),"select nextval ('seq_waittransaction')",$transaction,true,true);
        return $transaction;
    }

}
?>