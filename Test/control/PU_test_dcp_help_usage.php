<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace PU;

require_once 'PU_testcase_dcp.php';

class TestHelpUsage extends TestCaseDcp
{
    /**
     * @dataProvider dataTextHelpUsage
     * @param string $api
     */
    public function testTextHelpUsage($api)
    {
        $output = array();
        exec(getWshCmd() . " --api=" . $api . " --help", $output);
        $this->assertTrue($output[1] == "Usage :", "String usage not found for api " . $api);
        //error_log("OUTPUT IS == ".var_export($output, true));
        
    }
    
    public function dataTextHelpUsage()
    {
        return array(
            array(
                "appadmin"
            ) ,
            array(
                "benchmark_search"
            ) ,
            array(
                "change_action"
            ) ,
            array(
                "cleanFileName"
            ) ,
            array(
                "crontab"
            ) ,
            array(
                "csv2sql"
            ) ,
            array(
                "DocRelInit"
            ) ,
            array(
                "export_useracl"
            ) ,
            array(
                "fdl_adoc"
            ) ,
            array(
                "fdl_cronexec"
            ) ,
            array(
                "fdl_dbaccess"
            ) ,
            array(
                "fdl_deletefamily"
            ) ,
            array(
                "fdl_execute"
            ) ,
            array(
                "fdl_export1nf"
            ) ,
            array(
                "fdl_pkey"
            ) ,
            array(
                "fdl_resetprofiling"
            ) ,
            array(
                "fdl_sendmail"
            ) ,
            array(
                "fdl_testmemory"
            ) ,
            array(
                "fdl_trigger"
            ) ,
            array(
                "fixMultipleAliveRevision"
            ) ,
            array(
                "freedom_clean"
            ) ,
            array(
                "freedom_convert"
            ) ,
            array(
                "freedom_import"
            ) ,
            array(
                "freedom_refresh"
            ) ,
            array(
                "FullFileIndex"
            ) ,
            array(
                "fulltextReinit"
            ) ,
            array(
                "get_param"
            ) ,
            array(
                "importDocuments"
            ) ,
            array(
                "import_size"
            ) ,
            array(
                "import_style"
            ) ,
            array(
                "import_useracl"
            ) ,
            array(
                "initViewPrivileges"
            ) ,
            array(
                "migr_2.5.1"
            ) ,
            array(
                "migr_sql2.0"
            ) ,
            array(
                "ods2csv"
            ) ,
            array(
                "refreshDocuments"
            ) ,
            array(
                "refreshjsversion"
            ) ,
            array(
                "SetDocVaultIndex"
            ) ,
            array(
                "set_param"
            ) ,
            array(
                "updateclass"
            ) ,
            array(
                "updatetitles"
            ) ,
            array(
                "usercard_csv2vcard"
            ) ,
            array(
                "usercard_iuser"
            ) ,
            array(
                "usercard_ldapinit"
            ) ,
            array(
                "usercard_refreshgroup"
            ) ,
            array(
                "VaultExamine"
            ) ,
            array(
                "vault_init"
            ) ,
            array(
                "wdoc_graphviz"
            )
        );
    }
}
