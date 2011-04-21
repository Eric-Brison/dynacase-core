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
            $wd->status='recorded';
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
        print_r($wd);
        if ($wd->isAffected()) {
            print_r($wd);
        }
         
    }
    
    public static function getTransaction() {
        $err=simpleQuery(getDbAccess(),"select nextval ('seq_waittransaction')",$transaction,true,true);
        return $transaction;
    }

}
?>