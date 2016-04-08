<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Pu;
/**
 * @author Anakeen
 * @package Dcp\Pu
 */

require_once 'PU_testcase_dcp_commonfamily.php';

class TestDocVaultIndex extends TestCaseDcpCommonFamily
{
    /**
     * import some documents
     * @static
     * @return string
     */
    protected static function getCommonImportFile()
    {
        return array();
    }
    /**
     * @dataProvider dataDocVaultIndex
     * @param array() $data
     * @throws \Exception
     */
    public function testDocVaultIndex($data)
    {
        foreach ($data['import:family'] as $file => $callback) {
            $oImport = new \ImportDocument();
            $oImport->importDocuments($this->getAction() , $file, $onlyAnalyze = false, $archive = false);
            $err = $oImport->getErrorMessage();
            $this->assertEmpty($err, sprintf("import error %s", $err));
            /*
             * Run post-import callback
            */
            $err = call_user_func_array($callback, array(
                self::$dbaccess
            ));
            $this->assertEmpty($err, sprintf("Callback for import file '%s' returned with error: %s", $file, $err));
        }
        /*
         * Set
        */
        foreach ($data['set'] as $famName => $set) {
            /**
             * @var \DocFam $docFam
             */
            $docFam = new_Doc(self::$dbaccess, $famName);
            $this->assertTrue($docFam->isAlive() , sprintf("Could not find family with name '%s'.", $famName));
            /*
             * docfam.param
            */
            if (isset($set['param']) && is_array($set['param'])) {
                foreach ($set['param'] as $attrName => $files) {
                    $vids = array();
                    foreach ($files as $file => $name) {
                        $vid = $docFam->vaultRegisterFile($file, $name, $info);
                        $vids[] = $vid;
                    }
                    $vids = join("\n", $vids);
                    $err = $docFam->setParam($attrName, $vids);
                    $this->assertEmpty($err, sprintf("Error setting param of '%s' with vids = {%s}: %s", $attrName, join(', ', explode("\n", $vids)) , $err));
                }
            }
            /*
             * docfam.defval
            */
            if (isset($set['defval']) && is_array($set['defval'])) {
                foreach ($set['defval'] as $attrName => $files) {
                    $vids = array();
                    foreach ($files as $file => $name) {
                        $vid = $docFam->vaultRegisterFile($file, $name, $info);
                        $vids[] = $vid;
                    }
                    $vids = join("\n", $vids);
                    $err = $docFam->setDefValue($attrName, $vids);
                    $this->assertEmpty($err, sprintf("Error setting defval of '%s' with vids = {%s}: %s", $attrName, join(', ', explode("\n", $vids)) , $err));
                }
            }
            /*
             * store
            */
            $err = $docFam->store();
            $this->assertEmpty($err, sprintf("Error storing modified family '%s': %s", $docFam->name, $err));
        }
        /*
         * Import documents
        */
        $oImport = new \ImportDocument();
        $oImport->importDocuments($this->getAction() , $data['import:documents'], $onlyAnalyze = false, $archive = true);
        $err = $oImport->getErrorMessage();
        $this->assertEmpty($err, sprintf("import error %s", $err));
        /*
        printf("%s", $this->dumpDocVaultIndex($this->getDocVaultIndex(array(
            'TST_DOCVAULTINDEX',
            'TST_DOCVAULTINDEX_CHILD_1',
            'TST_DOCVAULTINDEX_CHILD_2',
            'TST_DOCVAULTINDEX_01',
            'TST_DOCVAULTINDEX_02'
        )) , true));
        */
        /*
         * Check expectations
        */
        foreach ($data['expect'] as $docName => $expect) {
            $res = $this->getDocVaultIndex(array(
                $docName
            ));
            if (isset($expect['count'])) {
                $this->assertTrue((count($res) == $expect['count']) , sprintf("Unexpected count (expected %d got %d) from docvaultindex for '%s':\n%s", $expect['count'], count($res) , $docName, $this->dumpDocVaultIndex($res, true)));
            }
            if (isset($expect['files'])) {
                $files = array();
                foreach ($res as $row) {
                    $files[] = $row['filename'];
                }
                foreach ($expect['files'] as $expectedFile) {
                    $this->assertContains($expectedFile, $files, sprintf("Missing file '%s' for '%s' in docvaultindex: %s", $expectedFile, $docName, $this->dumpDocVaultIndex($res, true)));
                }
                foreach ($files as $foundFile) {
                    $this->assertContains($foundFile, $expect['files'], sprintf("Found unexpected file '%s' for '%s' in docvaultindex:\n%s", $foundFile, $docName, $this->dumpDocVaultIndex($res, true)));
                }
            }
        }
        /*
         * Check docvaultindex consistency
        */
        $report = array();
        $vaultAnalyzer = new \Dcp\Vault\VaultAnalyzer();
        // Inhibit/buffer progress messages from checkDocVaultIndex
        ob_start();
        try {
            $ret = $vaultAnalyzer->checkDocVaultIndex($report);
        }
        catch(\Exception $e) {
            // Printout buffered progress messages in case of error
            ob_end_flush();
            throw $e;
        }
        // Discard buffered progress messages
        ob_end_clean();
        $missing = array();
        foreach ($report['missing']['iterator'] as $row) {
            $missing[] = $row;
        }
        $new = array();
        foreach ($report['new']['iterator'] as $row) {
            $new[] = $row;
        }
        $this->assertTrue($ret, sprintf("checkDocVaultIndex reported inconsistencies: %s", var_export(array(
            'missing' => $missing,
            'new' => $new
        ) , true)));
    }
    
    public function dumpDocVaultIndex($res, $return = false)
    {
        $out = '';
        $out.= sprintf("docvaultindex:\n");
        foreach ($res as $row) {
            $out.= sprintf(" %9d | %32s | %9d | %32s\n", $row['docid'], $row['docname'], $row['vaultid'], $row['filename']);
        }
        $out.= sprintf(" (%d rows)\n", count($res));
        $out.= sprintf("\n");
        if ($return === true) {
            return $out;
        }
        print $out;
        return null;
    }
    
    public function getDocVaultIndex($names = array())
    {
        $res = array();
        $where = '';
        if (count($names) > 0) {
            $where = array();
            foreach ($names as $name) {
                $where[] = sprintf("doc.name = %s", pg_escape_literal($name));
            }
            $where = sprintf("AND (%s)", join(' OR ', $where));
        }
        $q = sprintf("SELECT dvi.docid, doc.name AS docname, dvi.vaultid, vds.name AS filename FROM docvaultindex AS dvi, doc, vaultdiskstorage AS vds WHERE dvi.docid = doc.id AND dvi.vaultid = vds.id_file %s ORDER BY dvi.docid, dvi.vaultid", $where);
        simpleQuery(self::$dbaccess, $q, $res, false, false, true);
        return $res;
    }
    
    public function dataDocVaultIndex()
    {
        return array(
            array(
                array(
                    'import:family' => array(
                        './DCPTEST/PU_data_dcp_docvaultindex_family_parent.csv' => function ($dbaccess)
                        {
                            $docFam = new_Doc($dbaccess, 'TST_DOCVAULTINDEX');
                            if (!$docFam->isAlive()) {
                                return sprintf("Could not find family '%s'.", 'TST_DOCVAULTINDEX');
                            }
                            $vid = $docFam->vaultRegisterFile('DCPTEST/Images/img_one.png', 'icône TST_DOCVAULTINDEX.png', $info);
                            if (($err = $docFam->changeIcon($vid)) !== '') {
                                return $err;
                            }
                            return '';
                        }
                        ,
                        './DCPTEST/PU_data_dcp_docvaultindex_family_childs.csv' => function ($dbaccess)
                        {
                            return '';
                        }
                    ) ,
                    'set' => array(
                        'TST_DOCVAULTINDEX' => array(
                            'param' => array(
                                'P_S_FILE' => array(
                                    'DCPTEST/Images/img_one.png' => 'file param 1 of 3.png'
                                ) ,
                                'P_S_IMAGE' => array(
                                    'DCPTEST/Images/img_one.png' => 'image param 1 of 3.png'
                                ) ,
                                'P_M_FILE' => array(
                                    'DCPTEST/Images/img_one.png' => 'file param 2 of 3.png',
                                    'DCPTEST/Images/img_two.png' => 'file param 3 of 3.png'
                                ) ,
                                'P_M_IMAGE' => array(
                                    'DCPTEST/Images/img_one.png' => 'image param 2 of 3.png',
                                    'DCPTEST/Images/img_two.png' => 'image param 3 of 3.png'
                                )
                            ) ,
                            'defval' => array(
                                'S_FILE' => array(
                                    'DCPTEST/Images/img_one.png' => 'file defval 1 of 3.png'
                                ) ,
                                'S_IMAGE' => array(
                                    'DCPTEST/Images/img_one.png' => 'image defval 1 of 3.png'
                                ) ,
                                'M_FILE' => array(
                                    'DCPTEST/Images/img_one.png' => 'file defval 2 of 3.png',
                                    'DCPTEST/Images/img_two.png' => 'file defval 3 of 3.png'
                                ) ,
                                'M_IMAGE' => array(
                                    'DCPTEST/Images/img_one.png' => 'image defval 2 of 3.png',
                                    'DCPTEST/Images/img_two.png' => 'image defval 3 of 3.png'
                                )
                            )
                        ) ,
                        'TST_DOCVAULTINDEX_CHILD_2' => array(
                            'param' => array(
                                'P_S_FILE' => array(
                                    'DCPTEST/Images/img_one.png' => 'overwrite file param 1 of 3 from parent.png'
                                ) ,
                                'P_S_IMAGE' => array(
                                    'DCPTEST/Images/img_one.png' => 'overwrite image param 1 of 3 from parent.png'
                                ) ,
                                'P_S_FILE_2' => array(
                                    'DCPTEST/Images/img_one.png' => 'self file param 1 of 1.png'
                                ) ,
                                'P_S_IMAGE_2' => array(
                                    'DCPTEST/Images/img_one.png' => 'self image param 1 of 1.png'
                                )
                            ) ,
                            'defval' => array(
                                'S_FILE' => array(
                                    'DCPTEST/Images/img_one.png' => 'overwrite file defval 1 of 3 from parent.png'
                                ) ,
                                'S_IMAGE' => array(
                                    'DCPTEST/Images/img_one.png' => 'overwrite image defval 1 of 3 from parent.png'
                                ) ,
                                'S_FILE_2' => array(
                                    'DCPTEST/Images/img_one.png' => 'self file defval 1 of 1.png'
                                ) ,
                                'S_IMAGE_2' => array(
                                    'DCPTEST/Images/img_one.png' => 'self image defval 1 of 1.png'
                                )
                            )
                        )
                    ) ,
                    'import:documents' => './DCPTEST/PU_data_dcp_docvaultindex_documents.zip',
                    'expect' => array(
                        'TST_DOCVAULTINDEX' => array(
                            'count' => 13,
                            'files' => array(
                                'icône TST_DOCVAULTINDEX.png',
                                'file param 1 of 3.png',
                                'image param 1 of 3.png',
                                'file param 2 of 3.png',
                                'file param 3 of 3.png',
                                'image param 2 of 3.png',
                                'image param 3 of 3.png',
                                'file defval 1 of 3.png',
                                'image defval 1 of 3.png',
                                'file defval 2 of 3.png',
                                'file defval 3 of 3.png',
                                'image defval 2 of 3.png',
                                'image defval 3 of 3.png'
                            )
                        ) ,
                        'TST_DOCVAULTINDEX_CHILD_1' => array(
                            'count' => 0,
                            'files' => array()
                        ) ,
                        'TST_DOCVAULTINDEX_CHILD_2' => array(
                            'count' => 8,
                            'files' => array(
                                'overwrite file param 1 of 3 from parent.png',
                                'overwrite image param 1 of 3 from parent.png',
                                'self file param 1 of 1.png',
                                'self image param 1 of 1.png',
                                'overwrite file defval 1 of 3 from parent.png',
                                'overwrite image defval 1 of 3 from parent.png',
                                'self file defval 1 of 1.png',
                                'self image defval 1 of 1.png'
                            )
                        ) ,
                        'TST_DOCVAULTINDEX_01' => array(
                            'count' => 7,
                            'files' => array(
                                'icône TST_DOCVAULTINDEX.png',
                                'img_one.png',
                                'img_one_bis.png',
                                'img_two.png',
                                'img_three.png',
                                'img_two_bis.png',
                                'img_three_bis.png'
                            )
                        ) ,
                        'TST_DOCVAULTINDEX_02' => array(
                            'count' => 7,
                            'files' => array(
                                'icône TST_DOCVAULTINDEX.png',
                                'file defval 1 of 3.png',
                                'image defval 1 of 3.png',
                                'file defval 2 of 3.png',
                                'image defval 2 of 3.png',
                                'file defval 3 of 3.png',
                                'image defval 3 of 3.png'
                            )
                        )
                    )
                )
            )
        );
    }
}
