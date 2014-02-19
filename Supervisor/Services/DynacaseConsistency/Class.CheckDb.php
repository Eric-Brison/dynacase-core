<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Verify several point for the integrity of the system
 *
 * @author Anakeen
 * @version $Id: checklist.php,v 1.8 2008/12/31 14:37:26 jerome Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage CORE
 */
/**
 */

class checkDb
{
    /**
     * @var resource $r
     */
    private $r;
    private $connect;
    /**
     * @var array;
     */
    private $tparam;
    /**
     * @var array
     */
    private $tout;
    
    const OK = "green";
    const KO = "red";
    const BOF = "orange";
    
    public function __construct($connect)
    {
        $r = @pg_connect($connect);
        $this->connect = $connect;
        
        if ($r) $this->r = $r;
    }
    
    public function checkConnection()
    {
        $this->tout["main connection db"] = array(
            "status" => $this->r ? self::OK : self::KO,
            "msg" => $this->connect
        );
        return ($this->r != null);
    }
    
    public function checkUnreferenceUsers()
    {
        $result = pg_query($this->r, "SELECT * from groups where iduser not in (select id from users);");
        $pout = array();
        while ($row = pg_fetch_array($result, NULL, PGSQL_ASSOC)) {
            $pout[$row["iduser"]][] = $row["idgroup"];
        }
        if (count($pout) > 0) $msg = sprintf("%d unreferenced users<pre>%s</pre>", count($pout) , print_r($pout, true));
        else $msg = "";
        $this->tout["unreferenced user in group"] = array(
            "status" => (count($pout) == 0) ? self::OK : self::BOF,
            "msg" => $msg
        );
    }
    
    public function checkDateStyle()
    {
        $result = pg_query($this->r, "show DateStyle;");
        $row = pg_fetch_array($result, NULL);
        $msg = $dateStyle = $row[0];
        if ($dateStyle == "ISO, DMY") $status = self::OK;
        else {
            $status = self::KO;
            $msg = sprintf("Wrong datestyle setting : database : %s, set to : %s", $dateStyle);
        }
        
        $this->tout["dateStyle"] = array(
            "status" => $status,
            "msg" => $msg
        );
    }
    public function checkUserAsGroup()
    {
        $result = pg_query($this->r, "SELECT distinct(idgroup) from groups where idgroup not in (select id from users where accounttype!='U');");
        $pout = array();
        while ($row = pg_fetch_array($result, NULL, PGSQL_ASSOC)) {
            $pout[] = $row["idgroup"];
        }
        if (count($pout) > 0) $msg = sprintf("%d users detected as group<br><kbd>%s</kbd>", count($pout) , implode(", ", $pout));
        else $msg = "";
        $this->tout["user as group"] = array(
            "status" => (count($pout) == 0) ? self::OK : self::KO,
            "msg" => $msg
        );
    }
    
    public function checkUnreferencedAction()
    {
        $result = pg_query($this->r, "SELECT * from action where id_application not in (select id from application);");
        $pout = array();
        while ($row = pg_fetch_array($result, NULL, PGSQL_ASSOC)) {
            $pout[] = $row["name"];
        }
        if (count($pout) > 0) $msg = sprintf("%d unreferenced actions<br><kbd>%s</kbd>", count($pout) , implode(", ", $pout));
        else $msg = "";
        $this->tout["unreferenced actions"] = array(
            "status" => (count($pout) == 0) ? self::OK : self::BOF,
            "msg" => $msg
        );
    }
    
    public function checkUnreferencedParameters()
    {
        $result = pg_query($this->r, "SELECT * from paramdef where appid  not in (select id from application);");
        $pout = array();
        while ($row = pg_fetch_array($result, NULL, PGSQL_ASSOC)) {
            $pout[] = $row["name"];
        }
        $result = pg_query($this->r, "SELECT * from paramv where appid  not in (select id from application);");
        while ($row = pg_fetch_array($result, NULL, PGSQL_ASSOC)) {
            $pout[] = $row["name"];
        }
        if (count($pout) > 0) $msg = sprintf("%d unreferenced parameters<br><kbd>%s</kbd>", count($pout) , implode(", ", $pout));
        else $msg = "";
        $this->tout["unreferenced parameters"] = array(
            "status" => (count($pout) == 0) ? self::OK : self::BOF,
            "msg" => $msg
        );
    }
    
    public function checkUnreferencedAcl()
    {
        $result = pg_query($this->r, "SELECT * from acl where id_application not in (select id from application);");
        $pout = array();
        while ($row = pg_fetch_array($result, NULL, PGSQL_ASSOC)) {
            $pout[] = $row["name"];
        }
        if (count($pout) > 0) $msg = sprintf("%d unreferenced acl<br><kbd>%s</kbd>", count($pout) , implode(", ", $pout));
        else $msg = "";
        $this->tout["unreferenced acl"] = array(
            "status" => (count($pout) == 0) ? self::OK : self::BOF,
            "msg" => $msg
        );
    }
    
    public function getUnreferencedPermission()
    {
        $result = pg_query($this->r, "SELECT * from permission where id_acl not in (select id from acl);");
        $nb = pg_num_rows($result);
        $result = pg_query($this->r, "SELECT * from permission where id_user not in (select id from users);");
        $nb+= pg_num_rows($result);
        $result = pg_query($this->r, "SELECT * from permission where id_application not in (select id from application);");
        $nb+= pg_num_rows($result);
        $msg = '';
        if ($nb > 0) $msg = sprintf("%d unreferenced permissions", ($nb));
        $this->tout["unreferenced permission"] = array(
            "status" => ($nb == 0) ? self::OK : self::BOF,
            "msg" => $msg
        );
    }
    
    public function checkDoubleFrom()
    {
        $result = pg_query($this->r, "SELECT * from (SELECT id, count(id) as c  from family.documents group by id) as Z where Z.c > 1;");
        $pout = array();
        while ($row = pg_fetch_array($result, NULL, PGSQL_ASSOC)) {
            $pout[$row["id"]] = $row["c"];
        }
        if (count($pout) > 0) $msg = sprintf("%d double id detected<pre>%s</pre>", count($pout) , print_r($pout, true));
        else $msg = "";
        $this->tout["double doc id"] = array(
            "status" => (count($pout) == 0) ? self::OK : self::KO,
            "msg" => $msg
        );
    }
    public function checkDoubleName()
    {
        $result = pg_query($this->r, "select * from (select name, count(name) as c from family.documents where name is not null and name != '' and locked != -1 group by name) as Z where Z.c >1");
        $pout = array();
        while ($row = pg_fetch_array($result, NULL, PGSQL_ASSOC)) {
            $pout[$row["name"]] = $row["c"];
        }
        if (count($pout) > 0) $msg = sprintf("%d double detected<pre>%s</pre>", count($pout) , print_r($pout, true));
        else $msg = "";
        $this->tout["double doc name"] = array(
            "status" => (count($pout) == 0) ? self::OK : self::KO,
            "msg" => $msg
        );
    }
    
    public function checkMultipleAlive()
    {
        $result = pg_query($this->r, "select id, title from docread where id in (SELECT m AS id  FROM (SELECT min(id) AS m, initid, count(initid) AS c  FROM docread WHERE locked != -1 AND doctype != 'T' GROUP BY docread.initid) AS z where z.c > 1);");
        $pout = array();
        while ($row = pg_fetch_array($result, NULL, PGSQL_ASSOC)) {
            $pout[$row["id"]] = $row["title"];
        }
        if (count($pout) > 0) $msg = sprintf("%d multiple alive<pre>%s</pre>", count($pout) , print_r($pout, true));
        else $msg = "";
        $this->tout["multiple alive"] = array(
            "status" => (count($pout) == 0) ? self::OK : self::KO,
            "msg" => $msg
        );
    }
    
    public function checkInheritance()
    {
        $result = pg_query($this->r, "select * from family.families");
        $pout = array();
        while ($row = pg_fetch_array($result, NULL, PGSQL_ASSOC)) {
            $fromid = intval($row["fromid"]);
            if ($fromid == 0) $fromid = "";
            $fid = intval($row["id"]);
            $fname = strtolower($row["name"]);
            $test = pg_query($this->r, sprintf("SELECT inhparent::regclass as inhparent from pg_inherits where inhrelid = 'family.%s'::regclass::oid", pg_escape_string($fname)));
            $dbfrom = pg_fetch_array($test, NULL, PGSQL_ASSOC);
            $fromname = 'family.' . ($fromid ? strtolower($row["fromname"]) : "documents");
            $inhparent = str_replace('"', '', $dbfrom["inhparent"]);
            
            if ($inhparent != $fromname) {
                $pout[] = sprintf("Family %s [%d]: fromname = (%s [%d]), pg inherit=%s", $row["name"], $row["id"], $fromname, $row["fromid"], $inhparent);
            }
        }
        $this->tout["family inheritance"] = array(
            "status" => (count($pout) == 0) ? self::OK : self::KO,
            "msg" => implode("<br/>", $pout)
        );
    }
    
    public function checkNetworkUser()
    {
        // Test User LDAP (NetworkUser Module)
        $appNameList = array();
        $result = pg_query($this->r, "SELECT name FROM application;");
        while ($row = pg_fetch_array($result, NULL, PGSQL_ASSOC)) {
            $appNameList[] = $row['name'];
        }
        $nuAppExists = (array_search('NU', $appNameList) === false) ? false : true;
        $ldaphost = $this->getGlobalParam("NU_LDAP_HOST");
        $ldapport = $this->getGlobalParam("NU_LDAP_PORT");
        $ldapmode = $this->getGlobalParam("NU_LDAP_MODE");
        if ($nuAppExists && $ldaphost) {
            include_once ('../../../NU/Lib.NU.php');
            
            $ldapBindDn = $this->getGlobalParam('NU_LDAP_BINDDN');
            $ldapPassword = $this->getGlobalParam('NU_LDAP_PASSWORD');
            
            $baseList = array();
            array_push($baseList, array(
                'dn' => $this->getGlobalParam('NU_LDAP_USER_BASE_DN') ,
                'filter' => $this->getGlobalParam('NU_LDAP_USER_FILTER')
            ));
            array_push($baseList, array(
                'dn' => $this->getGlobalParam('NU_LDAP_GROUP_BASE_DN') ,
                'filter' => $this->getGlobalParam('NU_LDAP_GROUP_FILTER')
            ));
            
            foreach ($baseList as $base) {
                $testName = sprintf("connection to '%s'", $base['dn']);
                $this->tout[$testName] = array();
                /** @noinspection PhpUndefinedFunctionInspection */
                $uri = getLDAPUri($ldapmode, $ldaphost, $ldapport);
                $conn = ldap_connect($uri);
                if ($conn === false) {
                    $this->tout[$testName]['status'] = self::KO;
                    $this->tout[$testName]['msg'] = sprintf("Could not connect to LDAP server '%s': %s", $uri, $php_errormsg);
                    continue;
                }
                
                ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
                ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);
                
                if ($ldapmode == 'tls') {
                    $ret = ldap_start_tls($conn);
                    if ($ret === false) {
                        $this->tout[$testName]['status'] = self::KO;
                        $this->tout[$testName]['msg'] = sprintf("Could not negotiate TLS with server '%s': %s", $uri, ldap_error($conn));
                        continue;
                    }
                }
                
                $bind = ldap_bind($conn, $ldapBindDn, $ldapPassword);
                if ($bind === false) {
                    $this->tout[$testName]['status'] = self::KO;
                    $this->tout[$testName]['msg'] = sprintf("Could not bind with bind DN '%s' on server '%s': %s", $ldapBindDn, $uri, ldap_error($conn));
                    ldap_close($conn);
                    continue;
                }
                
                $res = ldap_search($conn, $base['dn'], sprintf("(&(objectClass=*)%s)", $base['filter']));
                if ($res === false) {
                    $this->tout[$testName]['status'] = self::KO;
                    $this->tout[$testName]['msg'] = sprintf("LDAP search on base '%s' with filter '%s' failed: %s", $base['dn'], $base['filter'], ldap_error($conn));
                    ldap_close($conn);
                    continue;
                }
                
                $count = ldap_count_entries($conn, $res);
                if ($count === false) {
                    $this->tout[$testName]['status'] = self::KO;
                    $this->tout[$testName]['msg'] = sprintf("Error counting result entries: %s", ldap_error($conn));
                    ldap_close($conn);
                    continue;
                }
                if ($count <= 0) {
                    $this->tout[$testName]['status'] = self::BOF;
                    $this->tout[$testName]['msg'] = sprintf("Search returned 0 entries...");
                    ldap_close($conn);
                    continue;
                }
                
                $this->tout[$testName]['status'] = self::OK;
                $this->tout[$testName]['msg'] = sprintf("Search returned %s entries.", $count);
                ldap_close($conn);
            }
        }
    }
    
    public function checkcleanContext()
    {
        $testName = "cleanContext cron job execution";
        $sql = "SELECT min(cdate) AS mincdate, count(id) AS count FROM family.documents WHERE doctype = 'T' AND cdate < now() - INTERVAL '24h'";
        try {
            simpleQuery('', $sql, $res, false, false, true);
            
            if ($res[0]['count'] > 0) {
                $err = sprintf("<p>Oldest temporary document is &gt; 24 hours: <code>%s</code></p>", htmlspecialchars($res[0]['mincdate']));
                $err.= "<p>Dynacase crontab might not be active or correctly registered.</p>";
                $err.= "<ul>";
                $err.= "<li>Check that the Dynacase crontab 'FREEDOM/freedom.cron' is correctly registered in the Apache's user crontab: <pre>./wsh.php --api=manageContextCrontab --cmd=list</pre></li>";
                $err.= "<li>If the crontab is not registered, try to register it: <pre>./wsh.php --api=manageContextCrontab --cmd=register --file=FREEDOM/freedom.cron</pre>";
                $err.= "<li>If the crontab is correctly registered but not executed, check that the system's cron daemon is correctly running.</li>";
                $err.= "</ul>";
                throw new Exception($err);
            }
        }
        catch(Exception $e) {
            $this->tout[$testName] = array(
                'status' => self::KO,
                'msg' => $e->getMessage()
            );
            return;
        }
        $this->tout[$testName] = array(
            'status' => self::OK,
            'msg' => ''
        );
        return;
    }
    /**
     * @param NormalAttribute $oa
     * @param string $pgtype
     * @param string $rtype
     * @return string
     */
    private static function verifyDbAttr(&$oa, $pgtype, &$rtype)
    {
        $err = '';
        $rtype = 'text';
        
        switch ($oa->type) {
            case 'int':
            case 'integer':
                $rtype = 'int4';
                break;

            case 'money':
            case 'double':
            case 'float':
                $rtype = 'float8';
                break;

            case 'date':
                $rtype = 'date';
                break;

            case 'timestamp':
                $rtype = 'timestamp';
                break;

            case 'time':
                $rtype = 'time';
                break;
        }
        
        if ($oa->isMultiple()) {
            $rtype = '_' . $rtype;
        }
        if ($rtype != $pgtype) {
            $err = sprintf("expected [%s], found [%s]", $rtype, $pgtype);
        }
        return $err;
    }
    /**
     * detected sql type inconsistence with declaration
     * @param $famid
     * @param NormalAttribute $aoa if wan't test only one attribute
     * @throws Dcp\Exception
     * @return array empty array if no error, else an item string by error detected
     */
    public static function verifyDbFamily($famid, NormalAttribute $aoa = null)
    {
        $cr = array();
        
        $fam = new_doc('', $famid);
        if ($fam->isAlive()) {
            
            $sql = sprintf("select column_name as attname, udt_name as typname, data_type FROM information_schema.columns where table_schema='family' and table_name='%s'", strtolower($fam->name));
            
            simpleQuery('', $sql, $res);
            $pgtype = array();
            foreach ($res as $pgattr) {
                if ($pgattr["typname"] == "timestamptz") $pgattr["typname"] = "timestamp";
                $pgtype[$pgattr["attname"]] = $pgattr["typname"];
            }
            
            if (!$aoa) {
                $oas = $fam->getNormalAttributes();
            } else {
                $oas = array(
                    $aoa
                );
            }
            foreach ($oas as $oa) {
                $aid = $oa->id;
                if (($oa->docid == $fam->id) && ($oa->type != "array") && ($oa->type != "frame") && ($oa->type != "tab") && ($oa->type != "") && ($oa->type != "menu")) {
                    $err = self::verifyDbAttr($oa, $pgtype[$aid], $rtype);
                    if ($err) {
                        $cr[] = sprintf("family %s, %s (%s) : %s\n", $fam->getTitle() , $aid, $oa->type, $err) . sprintf("\tand : alter table family.%s alter column %s type %s using %s::%s cascade; \n", strtolower($fam->name) , $aid, $rtype, $aid, $rtype);
                    }
                }
            }
        } else {
            throw new Dcp\Exception("no family $famid");
        }
        return $cr;
    }
    
    public static function getOrpheanAttributes($famid)
    {
        $d = new doc();
        $fam = new docfam('', $famid);
        $sql = sprintf("select column_name from information_schema.columns where table_schema='family' and table_name = '%s'", strtolower($fam->name));
        simpleQuery('', $sql, $res, true);
        
        $nAttributes = $fam->getNormalAttributes();
        
        $oasIds = array_keys($nAttributes);
        $oasIds = array_merge($oasIds, $d->fields, $d->sup_fields);
        
        $orphean = array();
        foreach ($res as $dbAttr) {
            if (!in_array($dbAttr, $oasIds)) {
                if ($dbAttr != "forumid") {
                    $orphean[] = $dbAttr;
                }
            }
        }
        return $orphean;
    }
    /**
     * detected sql type inconsistence with declaration
     * @param int $famid
     * @throws Dcp\Exception
     * @internal param \NormalAttribute $aoa if wan't test only one attribute
     * @return array empty array if no error, else an item string by error detected
     */
    public static function verifyDbAttrOrphean($famid)
    {
        $cr = array();
        /**
         * @var DocFam $fam
         */
        $fam = new_doc('', $famid);
        if ($fam->isAlive()) {
            $orphean = self::getOrpheanAttributes($famid);
            if ($orphean) {
                $cr[] = sprintf("\nfamily \"%s\", column '%s' - not part of family", $fam->getTitle() , implode(",", $orphean));
                foreach ($orphean as $orpAttr) {
                    $cr[] = sprintf("\tand : alter table family.%s drop column %s cascade; ", strtolower($fam->name) , $orpAttr);
                }
                $cr[] = "\n";
            }
        } else {
            throw new Dcp\Exception("no family $famid");
        }
        return $cr;
    }
    /**
     * verify attribute sql type
     * @return void
     */
    public function checkAttributeType()
    {
        $testName = 'attribute type';
        include_once ("../../../FDL/Class.Doc.php");
        $err = simpleQuery('', "select id from family.families", $families, true);
        
        foreach ($families as $famid) {
            $cr = $this->verifyDbFamily($famid);
            if (count($cr) > 0) {
                $err.= implode("<br/>", $cr);
            }
        }
        
        $this->tout[$testName]['status'] = ($err) ? self::KO : self::OK;
        $this->tout[$testName]['msg'] = '<pre>' . $err . '</pre>';
    }
    /**
     * verify attribute sql type
     * @return void
     */
    public function checkAttributeOrphean()
    {
        $testName = 'attribute orphean';
        include_once ("../../../FDL/Class.Doc.php");
        $err = simpleQuery('', "select id from family.families", $families, true);
        
        foreach ($families as $famid) {
            $cr = $this->verifyDbAttrOrphean($famid);
            if (count($cr) > 0) {
                $err.= implode("<br/>", $cr);
            }
        }
        
        $this->tout[$testName]['status'] = ($err) ? self::BOF : self::OK;
        $this->tout[$testName]['msg'] = '<pre>' . $err . '</pre>';
    }
    /**
     * Do all analyses
     *
     * @return array
     */
    public function getFullAnalyse()
    {
        if ($this->checkConnection()) {
            $this->checkDateStyle();
            $this->checkUnreferenceUsers();
            $this->checkUserAsGroup();
            $this->checkUnreferencedAction();
            $this->checkUnreferencedParameters();
            $this->checkUnreferencedAcl();
            $this->getUnreferencedPermission();
            $this->checkDoubleFrom();
            $this->checkDoubleName();
            $this->checkInheritance();
            $this->checkMultipleAlive();
            $this->checkNetworkUser();
            $this->checkAttributeType();
            $this->checkAttributeOrphean();
            $this->checkcleanContext();
        }
        return $this->tout;
    }
    
    private function initGlobalParam()
    {
        $result = pg_query($this->r, "SELECT * FROM paramv where  type='G'");
        if (!$result) {
        }
        $this->tparam = array();
        while ($row = pg_fetch_array($result, NULL, PGSQL_ASSOC)) {
            $this->tparam[$row["name"]] = $row["val"];
        }
    }
    private function getGlobalParam($key)
    {
        if (!$this->tparam) {
            $this->initGlobalParam();
        }
        return isset($this->tparam[$key]) ? $this->tparam[$key] : null;
    }
}
?>
