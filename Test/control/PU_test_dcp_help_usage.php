<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Pu;

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
        exec(getWshCmd() . " --api=" . $api . " --help 2> /dev/null", $output);
        $this->assertTrue($output[1] == "Usage :", "String usage not found for api " . $api);
    }
    
    public function dataTextHelpUsage()
    {
        return array(
            array(
                "manageApplications"
            ) ,
            array(
                "change_action"
            ) ,
            array(
                "manageContextCrontab"
            ) ,
            array(
                "csv2sql"
            ) ,
            array(
                "initializeDocrelTable"
            ) ,
            array(
                "generateDocumentClass"
            ) ,
            array(
                "processExecute"
            ) ,
            array(
                "destroyFamily"
            ) ,
            array(
                "fdl_execute"
            ) ,
            array(
                "fdl_export1nf"
            ) ,
            array(
                "fdl_resetprofiling"
            ) ,
            array(
                "fdl_sendmail"
            ) ,
            array(
                "fdl_trigger"
            ) ,
            array(
                "fixMultipleAliveRevision"
            ) ,
            array(
                "cleanContext"
            ) ,
            array(
                "freedom_import"
            ) ,
            array( //deprecated
                "freedom_refresh"
            ) ,
            array(
                "FullFileIndex"
            ) ,
            array(
                "fulltextReinit"
            ) ,
            array(
                "getApplicationParameter"
            ) ,
            array(
                "importDocuments"
            ) ,
            array(
                "import_size"
            ) ,
            array(
                "setStyle"
            ) ,
            array(
                "initViewPrivileges"
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
                "setApplicationParameter"
            ) ,
            array(
                "updateclass"
            ) ,
            array(
                "updatetitles"
            ) ,
            array(
                "refreshUserAccount"
            ) ,
            array(
                "refreshGroups"
            ) ,
            array(
                "checkVault"
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
