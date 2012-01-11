<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package DCP
*/

namespace PU;

require_once 'PU_testcase_dcp_application.php';

class TestAccess extends TestCaseDcpApplication
{
    public static function appConfig()
    {
        return array(
            "appRoot" => join(DIRECTORY_SEPARATOR, array(
                DEFAULT_PUBDIR,
                "DCPTEST",
                "app"
            )) ,
            "appName" => "TST_ACCESS",
            "import" => array(
                "PU_data_dcp_access.ods"
            ) ,
        );
    }
    /**
     * Test ACCESS on application/action
     * @param array $data test specification
     * @return void
     * @dataProvider dataTestAccessApplication
     */
    public function testAccessApplication($data)
    {
        $myAction = self::getAction();
        
        $appConfig = self::appConfig();
        $this->assertTrue(is_object(self::$app) , sprintf("Application '%s' is not an object.", $appConfig['appName']));
        
        if (isset($data['import'])) {
            $this->importDocument($data['import']);
        }
        if (isset($data['import:data'])) {
            $this->importCsvData($data['import:data']);
        }
        
        foreach ($data['tests'] as $testIdx => & $test) {
            if (isset($test['import'])) {
                $this->importDocument($test['import']);
            }
            if (isset($test['import:data'])) {
                $this->importCsvData($test['import:data']);
            }
            
            $this->assertTrue(isset($test['has:permission']) && is_array($test['has:permission']) , sprintf("test#%s> Invalid data supplied by provider.", $testIdx));
            
            foreach ($test['has:permission'] as $checkIdx => $check) {
                $user = new_doc(self::$dbaccess, $check['user']);
                $this->assertTrue($user->isAlive() , sprintf("test#%s/check#%s> Could not get user with id '%s'.", $testIdx, $checkIdx, $check['user']));
                $wuser = new \User(self::$dbaccess, $user->getValue('us_whatid'));
                $this->assertTrue(is_numeric($wuser->id) , sprintf("test#%s/check#%s> Invalid user what id '%s' for user '%s'.", $testIdx, $checkIdx, $wuser->id, $check['user']));
                
                $this->sudo($wuser->login);
                
                $perm = $myAction->hasPermission($check['acl'], $check['app']);
                if ($perm != $check['permission']) {
                    print $this->prettySqlRelation(sprintf("Groups test#%s/check#%s", $testIdx, $checkIdx) , "SELECT l.login AS user, r.login AS group FROM users AS l, groups AS g, users AS r WHERE g.iduser = l.id AND g.idgroup = r.id");
                    print $this->prettySqlRelation(sprintf("Permission test#%s/check#%s", $testIdx, $checkIdx) , "SELECT u.login AS user, a.name AS app, c.name AS acl, p.id_acl AS permission, p.computed AS computed FROM users AS u, permission AS p, application AS a, acl AS c WHERE u.id = p.id_user AND p.id_application = a.id AND abs(p.id_acl) = c.id AND a.name = 'TST_ACCESS'");
                }
                $this->assertTrue($perm == $check['permission'], sprintf("test#%s/check#%s> Unexpected permission %s (should be %s) for user %s on acl %s from app %s", $testIdx, $checkIdx, $perm ? 'true' : 'false', $check['permission'] ? 'true' : 'false', $check['user'], $check['acl'], $check['app']));
                
                $this->exitSudo();
            }
        }
        unset($test);
    }
    
    public function prettySqlRelation($title, $sql)
    {
        $res = pg_query(self::$odb->dbid, $sql);
        if ($res === false) {
            return false;
        }
        $res = pg_fetch_all($res);
        if (!is_array($res)) {
            return false;
        }
        
        $out = array();
        $colsWidth = array();
        /* Compute columns width */
        foreach ($res as $tuple) {
            foreach ($tuple as $k => $v) {
                if (!array_key_exists($k, $colsWidth)) {
                    $colsWidth[$k] = strlen($k);
                }
                $colsWidth[$k] = max($colsWidth[$k], strlen($v));
            }
        }
        /* Generate table */
        foreach ($res as $i => $tuple) {
            $line = array();
            foreach ($tuple as $k => $v) {
                $line[] = sprintf("%" . ($colsWidth[$k] + 2) . "s", $v);
            }
            if ($i == 0) {
                /* Generate table header */
                $header = array();
                foreach ($tuple as $k => $v) {
                    $header[] = sprintf("%" . ($colsWidth[$k] + 2) . "s", $k);
                }
                $header = join(" | ", $header);
                /* Generate title */
                if (strlen($title) > 0) {
                    $title = sprintf("%" . (int)(strlen($header) / 2 + strlen($title) / 2) . "s", $title);
                    $out[] = $title;
                    $out[] = str_repeat("-", strlen($header));
                }
                $out[] = $header;
                $out[] = str_repeat("-", strlen($header));
            }
            /* Add table line */
            $out[] = join(" | ", $line);
        }
        
        return join("\n", $out) . "\n";
    }
    
    public function dataTestAccessApplication()
    {
        return array(
            array(
                array(
                    "tests" => array(
                        /* 0 */
                        array(
                            /*
                             * Compute and check all permissions
                            */
                            "has:permission" => array(
                                // Homer
                                array(
                                    "user" => "TST_U_HOMER_SIMPSON",
                                    "app" => "TST_ACCESS",
                                    "acl" => "TST_ACCESS_ACL_1",
                                    "permission" => true
                                ) ,
                                array(
                                    "user" => "TST_U_HOMER_SIMPSON",
                                    "app" => "TST_ACCESS",
                                    "acl" => "TST_ACCESS_ACL_2",
                                    "permission" => false
                                ) ,
                                // Marge
                                array(
                                    "user" => "TST_U_MARGE_SIMPSON",
                                    "app" => "TST_ACCESS",
                                    "acl" => "TST_ACCESS_ACL_1",
                                    "permission" => true
                                ) ,
                                array(
                                    "user" => "TST_U_MARGE_SIMPSON",
                                    "app" => "TST_ACCESS",
                                    "acl" => "TST_ACCESS_ACL_2",
                                    "permission" => true
                                ) ,
                                // Bart
                                array(
                                    "user" => "TST_U_BART_SIMPSON",
                                    "app" => "TST_ACCESS",
                                    "acl" => "TST_ACCESS_ACL_1",
                                    "permission" => false
                                ) ,
                                array(
                                    "user" => "TST_U_BART_SIMPSON",
                                    "app" => "TST_ACCESS",
                                    "acl" => "TST_ACCESS_ACL_2",
                                    "permission" => false
                                ) ,
                                // Lisa
                                array(
                                    "user" => "TST_U_LISA_SIMPSON",
                                    "app" => "TST_ACCESS",
                                    "acl" => "TST_ACCESS_ACL_1",
                                    "permission" => false
                                ) ,
                                array(
                                    "user" => "TST_U_LISA_SIMPSON",
                                    "app" => "TST_ACCESS",
                                    "acl" => "TST_ACCESS_ACL_2",
                                    "permission" => true
                                ) ,
                                // Maggie
                                array(
                                    "user" => "TST_U_MAGGIE_SIMPSON",
                                    "app" => "TST_ACCESS",
                                    "acl" => "TST_ACCESS_ACL_1",
                                    "permission" => false
                                ) ,
                                array(
                                    "user" => "TST_U_MAGGIE_SIMPSON",
                                    "app" => "TST_ACCESS",
                                    "acl" => "TST_ACCESS_ACL_2",
                                    "permission" => true
                                ) ,
                            )
                        ) ,
                        /* 1 */
                        array(
                            "import:data" => "ACCESS;TST_G_G2;TST_ACCESS;TST_ACCESS_ACL_1",
                            "has:permission" => array(
                                array(
                                    "user" => "TST_U_MAGGIE_SIMPSON",
                                    "app" => "TST_ACCESS",
                                    "acl" => "TST_ACCESS_ACL_1",
                                    "permission" => true
                                )
                            )
                        ) ,
                        /* 2 */
                        array(
                            "import:data" => "ACCESS;TST_G_G21;TST_ACCESS;-TST_ACCESS_ACL_1",
                            "has:permission" => array(
                                array(
                                    "user" => "TST_U_MAGGIE_SIMPSON",
                                    "app" => "TST_ACCESS",
                                    "acl" => "TST_ACCESS_ACL_1",
                                    "permission" => false
                                )
                            )
                        )
                    ) // tests
                    
                )
            )
        );
    }
}
?>