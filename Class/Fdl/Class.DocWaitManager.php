<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
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
    public static function saveWaitingDoc(Doc & $doc, $domainId = null, $transaction = null, $extraData = null)
    {
        $err = '';
        
        if ($doc) {
            $wd = new DocWait($doc->dbaccess, array(
                $doc->initid,
                $doc->getSystemUserId()
            ));
            
            $wd->uid = $doc->getSystemUserId();
            $wd->localid = isset($doc->localid) ? $doc->localid : null;
            $wd->values = serialize($doc->getValues());
            $wd->status = docWait::recording;
            $wd->title = $doc->getTitle();
            $wd->fromid = $doc->fromid;
            $wd->refererid = $doc->id;
            $wd->refererinitid = $doc->initid;
            $wd->domain = $domainId;
            $wd->transaction = $transaction;
            $wd->date = date('Y-m-d H:i:s.u');
            if ($extraData !== null) $wd->extradata = json_encode($extraData);
            
            if ($wd->isAffected()) {
                $err = $wd->modify();
            } else {
                $orivalues = $doc->getValues();
                $ori = new_doc($doc->dbaccess, $doc->id, true);
                if ($ori->isAlive()) {
                    $wd->orivalues = serialize($ori->getValues());
                    $wd->status = docWait::upToDate;
                }
                $err = $wd->add();
            }
        } else {
            $err = sprintf(_("waiting : document not set"));
        }
        return $err;
    }
    /**
     * Return waiting doc
     * @param int $id doc identifier
     * @return Doc
     */
    public static function getWaitingDoc($id)
    {
        $wd = new DocWait(getDbAccess() , array(
            $id,
            Doc::getSystemUserId()
        ));
        
        if ($wd->isAffected()) {
            return $wd;
        }
        return null;
    }
    /**
     * return unresolved links
     * @param int $domain domain identifier
     * @param int $user user identifier
     * @return array index=localid, value=serverid
     */
    public static function getUnresolvedLocalLinks($domain = - 1, $user = - 1)
    {
        $q = new QueryDb(getDbAccess() , "docWait");
        $q->addQuery(sprintf("domain = %d", $domain));
        $q->addQuery(sprintf("uid = %d", $user));
        $q->addQuery("localid is not null");
        $q->addQuery("refererinitid < 0");
        
        $res = $q->Query(0, 0, 'TABLE');
        $out = array();
        if (is_array($res)) {
            foreach ($res as $k => $v) {
                $out[$v['localid']] = $v['refererinitid'];
            }
        }
        return $out;
    }
    /**
     * return waiting doc for a transaction
     * @param int $transaction transaction identifier
     * @return DbObjectList docWait list
     */
    public static function getWaitingDocs($transaction)
    {
        $q = new QueryDb(getDbAccess() , "docWait");
        $q->addQuery(sprintf("transaction = %d", $transaction));
        
        return $q->Query(0, 0, 'ITER');
    }
    /**
     * return waiting doc for a domain
     * @param int $transaction transaction identifier
     * @return DbObjectList docWait list
     */
    public static function getWaitingDocsByDomain($domainId)
    {
        $q = new QueryDb(getDbAccess() , "docWait");
        $q->addQuery(sprintf("domain = %d", $domainId));
        
        return $q->Query(0, 0, 'ITER');
    }
    /**
     * create a new transaction id
     * @return int transaction identifier
     */
    public static function getTransaction()
    {
        $err = simpleQuery(getDbAccess() , "select nextval ('seq_waittransaction')", $transaction, true, true);
        return $transaction;
    }
    /**
     * delete waiting document for a domain, an user or an user in a domain
     * @param int $domain domain identifier
     * @param int $user user identifier
     * @param int $docinitid initial document identifier
     */
    public static function clearWaitingDocs($domain = - 1, $user = - 1, $docinitid = - 1)
    {
        $err = '';
        $wheres = array();
        if ($domain >= 0) {
            $wheres[] = sprintf("domain = %d", $domain);
        }
        if ($user >= 0) {
            $wheres[] = sprintf("uid = %d", $user);
        }
        if ($docinitid >= 0) {
            $wheres[] = sprintf("refererinitid = %d", $docinitid);
        }
        //error_log("clearWaitingDocs $domain - $user - $docinitid");
        if (count($wheres) == 0) {
            $err = simpleQuery(getDbAccess() , "delete from docwait");
        } else {
            $err = simpleQuery(getDbAccess() , sprintf("delete from docwait where %s", implode(" and ", $wheres)));
        }
        return $err;
    }
}
?>
