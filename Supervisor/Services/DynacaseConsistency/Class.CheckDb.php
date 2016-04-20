<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Verify several point for the integrity of the system
 *
 * @author Anakeen
 * @version $Id: checklist.php,v 1.8 2008/12/31 14:37:26 jerome Exp $
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
        $ret = array(
            'status' => self::OK,
            'msg' => ''
        );
        simpleQuery('', "SELECT current_database()", $dbname, true, true, true);
        simpleQuery('', "SHOW DateStyle", $dateStyle, true, true, true);
        if ($dateStyle !== 'ISO, DMY') {
            $ret['status'] = self::KO;
            $ret['msg'] = sprintf("Database's \"DateStyle\" should be set to 'ISO, DMY' (actual value is '%s')&nbsp;<br/><pre>ALTER DATABASE %s SET DateStyle = 'ISO, DMY';</pre>", htmlspecialchars($dateStyle, ENT_QUOTES) , htmlspecialchars(pg_escape_identifier($dbname)));
        }
        $this->tout["dateStyle"] = $ret;
    }
    
    public function checkStandardConformingStrings()
    {
        $res = array(
            "status" => self::OK,
            "msg" => ""
        );
        simpleQuery('', "SELECT current_database()", $dbname, true, true, true);
        simpleQuery('', "SHOW standard_conforming_strings", $value, true, true, true);
        if ($value !== 'off') {
            $res['status'] = self::KO;
            $res['msg'] = sprintf("Database's \"standard_conforming_strings\" should be set to 'off' (actual value is '%s')&nbsp;:<br/><pre>ALTER DATABASE %s SET standard_conforming_strings = off;</pre>", htmlspecialchars($value, ENT_QUOTES) , htmlspecialchars(pg_escape_identifier($dbname)));
        }
        $this->tout["standard_conforming_strings"] = $res;
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
        $result = pg_query($this->r, "SELECT * from (SELECT id, count(id) as c  from doc group by id) as Z where Z.c > 1;");
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
        $result = pg_query($this->r, "select * from (select name, count(name) as c from doc where name is not null and name != '' and locked != -1 group by name) as Z where Z.c >1");
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
        $result = pg_query($this->r, "select * from docfam");
        $pout = array();
        while ($row = pg_fetch_array($result, NULL, PGSQL_ASSOC)) {
            $fromid = intval($row["fromid"]);
            if ($fromid == 0) $fromid = "";
            $fid = intval($row["id"]);
            $test = pg_query($this->r, sprintf("SELECT relname from pg_class where oid in (SELECT inhparent from pg_inherits where inhrelid =(SELECT oid FROM pg_class where relname='doc%d'));", $fid));
            $dbfrom = pg_fetch_array($test, NULL, PGSQL_ASSOC);
            if ($dbfrom["relname"] != "doc$fromid") {
                $pout[] = sprintf("Family %s [%d]: fromid = %d, pg inherit=%s", $row["name"], $row["id"], $row["fromid"], $dbfrom["relname"]);
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
        $sql = "SELECT min(cdate) AS mincdate, count(id) AS count FROM doc WHERE doctype = 'T' AND cdate < now() - INTERVAL '24h'";
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
        if (!$oa->inArray()) {
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
            $sql = sprintf("select pg_attribute.attname,pg_type.typname FROM pg_attribute, pg_type where pg_type.oid=pg_attribute.atttypid and pg_attribute.attrelid=(SELECT oid from pg_class where relname='doc%d') order by pg_attribute.attname;", $fam->id);
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
                        $cr[] = sprintf("family %s, %s (%s) : %s\n", $fam->getTitle() , $aid, $oa->type, $err) . sprintf("\ttry : drop view family.%s; \n", strtolower($fam->name)) . sprintf("\tand : alter table doc%d alter column %s type %s using %s::%s; \n", $fam->id, $aid, $rtype, $aid, $rtype);
                    }
                }
            }
        } else {
            throw new Dcp\Exception("no family $famid");
        }
        return $cr;
    }
    
    public static function getOrphanAttributes($famid)
    {
        
        $d = new Doc();
        $fam = new_doc('', $famid);
        $sql = sprintf("select column_name from information_schema.columns where table_name = 'doc%d'", $fam->id);
        simpleQuery('', $sql, $res, true);
        
        $nAttributes = $fam->getNormalAttributes();
        $oasIds = array_keys($nAttributes);
        $oasIds = array_merge($oasIds, $d->fields, $d->sup_fields, array(
            "fulltext",
            "svalues"
        ));
        
        foreach ($nAttributes as $attrid => $oa) {
            if ($oa->type == "file") {
                $oasIds[] = $attrid . '_txt';
                $oasIds[] = $attrid . '_vec';
            }
        }
        
        $orphan = array();
        foreach ($res as $dbAttr) {
            if (!in_array($dbAttr, $oasIds)) {
                if ($dbAttr != "forumid") {
                    $orphan[] = $dbAttr;
                }
            }
        }
        return $orphan;
    }
    /**
     * verify attribute sql type
     * @return void
     */
    public function checkAttributeType()
    {
        $testName = 'attribute type';
        include_once ("../../../FDL/Class.Doc.php");
        $err = simpleQuery('', "select id from docfam", $families, true);
        
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
    public function checkAttributeOrphan()
    {
        $testName = 'attribute orphan';
        include_once ("../../../FDL/Class.Doc.php");
        if (($err = $this->computeDropColumns($treeNode)) != '') {
            $this->tout[$testName]['status'] = self::BOF;
            $this->tout[$testName]['msg'] = sprintf("<pre>%s</pre>", htmlspecialchars($err, ENT_QUOTES));
        }
        
        $this->getSQLDropColumns($treeNode, $cmds);
        
        $html = '';
        if (count($cmds) > 0) {
            $html.= "BEGIN;<br/>";
            $html.= "<br/>";
            $html.= implode("<br/>", array_map(function ($v)
            {
                return htmlspecialchars($v, ENT_QUOTES);
            }
            , $cmds)) . "<br/>";
            $html.= "<br/>";
            $html.= "COMMIT;";
        }
        
        $this->tout[$testName]['status'] = ($html !== '') ? self::BOF : self::OK;
        $this->tout[$testName]['msg'] = '<pre>' . $html . '</pre>';
        
        return;
    }
    /**
     * Recursively walk up the tree node to find if a specific column has
     * already been marked for deletion in a parent family.
     *
     * @param array $node The tree node starting point
     * @param string $column The column's name to lookup for
     * @return bool bool(true) if the column is already marked for deletion
     *               in a parent family or bool(false) if not
     */
    static function isDroppedInNode(&$node, $column)
    {
        if (isset($node['drop'][$column])) {
            return true;
        }
        if (isset($node['parent'])) {
            return self::isDroppedInNode($node['parent'], $column);
        }
        return false;
    }
    /**
     * Compute the required SQL commands to drop the columns marked for
     * deletion in the given family tree.
     *
     * @param array $node The family tree obtained with ::computeDropColumns()
     * @param array $cmds Resulting SQL commands
     * @param bool $combined bool(true) to combine multiple DROP instructions
     *                        into a single ALTER TABLE instruction,
     *                        bool(false) to generate multiple ALTER TABLE
     *                        instructions containing each a single DROP
     *                        instruction.
     */
    public function getSQLDropColumns(&$node, &$cmds = array() , $combined = true)
    {
        if (isset($node['drop']) && is_array($node['drop'])) {
            if (count($node['drop']) > 0) {
                $cmds[] = sprintf("-- Family '%s', table doc%d", $node['name'], $node['id']);
                $alter = sprintf("ALTER TABLE doc%d", $node['id']);
                $drops = array();
                foreach ($node['drop'] as $column) {
                    $drops[] = sprintf("DROP COLUMN IF EXISTS %s CASCADE", pg_escape_identifier($column));
                }
                if ($combined) {
                    $alter.= "\n\t" . join(",\n\t", $drops) . ";";
                    $cmds[] = $alter;
                } else {
                    foreach ($drops as $drop) {
                        $cmds[] = $alter . " " . $drop . ";";
                    }
                }
                $cmds[] = 'SELECT refreshFamilySchemaViews();';
                $cmds[] = "";
            }
        }
        if (isset($node['childs'])) {
            foreach ($node['childs'] as & $child) {
                $this->getSQLDropColumns($child, $cmds, $combined);
            }
        }
    }
    /**
     * Compute a tree of families with columns to drop
     *
     * @param array $node The families tree returned with columns to drop
     * @param int $fromId Family id to start from (default is 0)
     * @return string empty string on success, non-empty string containing the error message on failure
     * @throws \Dcp\Db\Exception
     */
    public function computeDropColumns(&$node = null, $fromId = 0)
    {
        /*
         * List of families ordered by parenthood:
         * parents on top, childs at bottom
        */
        $sql = <<<'EOF'
WITH RECURSIVE topfam(id, fromid) AS (
    SELECT id, fromid FROM docfam WHERE fromid = %d
    UNION
    SELECT docfam.id, docfam.fromid FROM topfam, docfam WHERE docfam.fromid = topfam.id
)
SELECT * FROM topfam;
EOF;
        $sql = sprintf($sql, $fromId);
        if (($err = simpleQuery('', $sql, $families, false, false, null)) != '') {
            return $err;
        }
        
        if ($node === null) {
            $node = array(
                'name' => '',
                'id' => $fromId,
                'childs' => array()
            );
        }
        foreach ($families as $fam) {
            $doc = new_Doc('', $fam['id']);
            if (!is_object($doc) || !$doc->isAlive()) {
                continue;
            }
            if ($fam['fromid'] != $fromId) {
                continue;
            }
            $drop = array();
            $orphans = self::getOrphanAttributes($fam['id']);
            foreach ($orphans as $column) {
                if (!self::isDroppedInNode($node, $column)) {
                    $drop[$column] = $column;
                }
            }
            $node['childs'][$fam['id']] = array(
                'name' => $doc->name,
                'id' => $fam['id'],
                'fromid' => $fam['fromid'],
                'drop' => $drop,
                'parent' => & $node,
                'childs' => array()
            );
        }
        foreach ($node['childs'] as & $child) {
            if (($err = $this->computeDropColumns($child, $child['id'])) != '') {
                return $err;
            }
        }
        return '';
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
            $this->checkStandardConformingStrings();
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
            $this->checkAttributeOrphan();
            $this->checkcleanContext();
            $this->checkMissingDocumentsInDocread();
            $this->checkSpuriousDocumentsInDocread();
            $this->checkUnnamedFamilies();
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
    public function checkUnnamedFamilies()
    {
        $sql = <<<'EOSQL'
SELECT docfam.id AS id, docfam.name AS docfam_name, docname.name AS docname_name, docread.name AS docread_name
FROM docfam
    LEFT JOIN docname ON docfam.id = docname.id
    LEFT JOIN docread ON docfam.id = docread.id
WHERE
    docfam.name IS NULL OR
    docname.name IS NULL OR
    docread.name IS NULL
ORDER BY docfam.id ASC
EOSQL;
        $result = pg_query($this->r, $sql);
        $unnamedDocfam = array();
        $unnamedDocname = array();
        $unnamedDocread = array();
        while ($row = pg_fetch_array($result, NULL, PGSQL_ASSOC)) {
            if ($row['docfam_name'] == '') {
                $unnamedDocfam[] = $row['id'];
            }
            if ($row['docname_name'] == '') {
                $unnamedDocname[] = $row['id'];
            }
            if ($row['docread_name'] == '') {
                $unnamedDocread[] = $row['id'];
            }
        }
        $unnamedDocfamCount = count($unnamedDocfam);
        $unnamedDocreadCount = count($unnamedDocread);
        $unnamedDocnameCount = count($unnamedDocname);
        $pout = array();
        if ($unnamedDocfamCount > 0) {
            $pout[] = sprintf("%s unnamed famil%s in docfam: <pre>{%s}</pre>", $unnamedDocfamCount, ($unnamedDocfamCount > 1 ? 'ies' : 'y') , join(', ', $unnamedDocfam));
        }
        if ($unnamedDocreadCount > 0) {
            $pout[] = sprintf("%s unnamed famil%s in docfam: <pre>{%s}</pre>", $unnamedDocreadCount, ($unnamedDocreadCount > 1 ? 'ies' : 'y') , join(', ', $unnamedDocread));
        }
        if ($unnamedDocnameCount > 0) {
            $pout[] = sprintf("%s unnamed famil%s in docfam: <pre>{%s}</pre>", $unnamedDocnameCount, ($unnamedDocnameCount > 1 ? 'ies' : 'y') , join(', ', $unnamedDocname));
        }
        $this->tout["missing family name"] = array(
            "status" => (count($pout) <= 0) ? self::OK : self::BOF,
            "msg" => (count($pout) <= 0) ? '' : '<ul><li>' . join('</li><li>', $pout) . '</li></ul>'
        );
    }
    public function checkMissingDocumentsInDocread()
    {
        $result = pg_query($this->r, "SELECT id FROM doc WHERE id < 1e9 AND NOT EXISTS (SELECT 1 FROM docread WHERE docread.id = doc.id)");
        $pout = array();
        while ($row = pg_fetch_array($result, NULL, PGSQL_ASSOC)) {
            $pout[] = $row['id'];
        }
        if (count($pout) > 0) {
            if (count($pout) > 10) {
                $pout = array_slice($pout, 0, 10);
                $pout[] = '…';
            }
            $msg = sprintf("%d missing document%s in docread: <pre>{%s}</pre>", count($pout) , (count($pout) > 1 ? 's' : '') , join(', ', $pout));
        } else $msg = "";
        $this->tout["missing documents in docread"] = array(
            "status" => (count($pout) == 0) ? self::OK : self::BOF,
            "msg" => $msg
        );
    }
    public function checkSpuriousDocumentsInDocread()
    {
        $result = pg_query($this->r, "SELECT id FROM docread WHERE id < 1e9 AND NOT EXISTS (SELECT 1 FROM doc WHERE docread.id = doc.id)");
        $pout = array();
        while ($row = pg_fetch_array($result, NULL, PGSQL_ASSOC)) {
            $pout[] = $row['id'];
        }
        if (count($pout) > 0) {
            if (count($pout) > 10) {
                $pout = array_slice($pout, 0, 10);
                $pout[] = '…';
            }
            $msg = sprintf("%d spurious document%s in docread: <pre>{%s}</pre>", count($pout) , (count($pout) > 1 ? 's' : '') , join(', ', $pout));
        } else $msg = "";
        $this->tout["spurious documents in docread"] = array(
            "status" => (count($pout) == 0) ? self::OK : self::BOF,
            "msg" => $msg
        );
    }
}
