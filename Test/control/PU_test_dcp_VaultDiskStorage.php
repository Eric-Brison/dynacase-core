<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace PU;
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package DCP
 */

require_once 'PU_testcase_dcp_commonfamily.php';

class TestVaultDiskStorage extends TestCaseDcpCommonFamily
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
     * @dataProvider dataStore
     */
    public function testStore($data) {
        $id_file = null;

        $vds = new \VaultDiskStorage(self::$dbaccess);
        $err = $vds->Store($data['infile'], $data['public_access'], $id_file);
        if (!$data['expect:success']) {
            $this->assertNonEmpty($err, sprintf("Store(%s) did not returned with an expected error.", $data['infile']));
            return;
        }
        $this->assertEmpty($err, sprintf("Unexpected error in Store(%s): %s", $data['infile'], $err));
        $this->assertNotNull($id_file, sprintf("Store(%s) returned a null id_file.", $data['infile']));
        $this->assertTrue(is_numeric($id_file), sprintf("Store(%s) did not returned a numeric id_file.", $data['infile']));
        $this->assertTrue(($id_file > 0), sprintf("Store(%s) did not returned an id_file > 0.", $data['infile']));
    }
    public function dataStore() {
        return array(
            array(
                array(
                    "infile" => "DCPTEST/Layout/tst_VaultDiskStorage.odt",
                    "public_access" => false,
                    "expect:success" => true
                ),
                array(
                    "infile" => "DCPTEST/Layout/tst_VaultDiskStorage_non_existing_file.odt",
                    "public_access" => false,
                    "expect:success" => false
                )
            )
        );
    }
    /**
     * @dataProvider dataShow
     */
    public function testShow($data) {
        $id_file = null;

        $vds = new \VaultdiskStorage(self::$dbaccess);
        $err = $vds->Store($data['infile'], false, $id_file);
        if ($err != '') {
            $this->markTestIncomplete(sprintf("Store(%s) returned with error: %s", $data['infile'], $err));
        }

        $vfi = null;
        $vds = new \VaultDiskStorage(self::$dbaccess);
        $err = $vds->show($id_file, $vfi);
        $this->assertEmpty($err, sprintf("show(%s) returned with error: %s", $id_file, $err));
        $this->assertTrue(is_object($vfi), sprintf("show(%s) did not returned an object.", $id_file));
        $this->assertTrue(is_a($vfi, '\VaultFileInfo'), sprintf("show(%s) dit not returned a VaultFileInfo object: class is %s", $id_file, get_class($vfi)));
    }
    public function dataShow() {
        return array(
            array(
                array(
                    "infile" => "DCPTEST/Layout/tst_VaultDiskStorage.odt"
                )
            )
        );
    }
    /**
     * @dataProvider dataGetPath
     */
    public function testGetPath($data) {
        $id_file = null;

        $vds = new \VaultDiskStorage(self::$dbaccess);
        $err = $vds->Store($data['infile'], false, $id_file);
        if ($err != '') {
            $this->markTestIncomplete(sprintf("Store(%s) returned with error: %s", $data['infile'], $err));
        }

        $vds = new \VaultDiskStorage(self::$dbaccess, $id_file);
        $this->assertTrue($vds->isAffected(), sprintf("Could not get back id_file '%s' from previously stored file '%s'.", $id_file, $data['infile']));

        $path = $vds->getPath();
        $this->assertNotEmpty($path, sprintf("getPath() on id_file '%s' returned an empty path.", $id_file));
        $this->assertTrue(is_file($path), sprintf("getPath() returned path '%s' for id_file '%s' is not a file.", $path, $id_file));
    }
    public function dataGetPath() {
        return array(
            array(
                array(
                    "infile" => "DCPTEST/Layout/tst_VaultDiskStorage.odt"
                )
            )
        );
    }
    /**
     * @dataProvider dataDestroy
     */
    public function testDestroy($data) {
        $id_file = null;

        $vds = new \VaultDiskStorage(self::$dbaccess);
        $err = $vds->Store($data['infile'], false, $id_file);
        if ($err != '') {
            $this->markTestIncomplete(sprintf("Store(%s) returned with error: %s", $err));
        }

        $vds = new \VaultDiskStorage(self::$dbaccess, $id_file);
        if (!$vds->isAffected()) {
            $this->markTestIncomplete(sprintf("Could not get back id_file '%s' from previously stored file '%s'.", $id_file, $data['infile']));
        }

        $path = $vds->getPath();
        if (!is_file($path)) {
            $this->markTestIncomplete(sprintf("Invalid path '%s' for stored file '%s' with id_file '%s'.", $path, $data['infile'], $id_file));
        }

        $err = $vds->Destroy($id_file);
        $this->assertEmpty($err, sprintf("Destroy() returned with error: %s", $err));
        $this->assertFalse(is_file($path), sprintf("File '%s' from id_file %s is still present after Destroy().", $path, $id_file));

        $vds = new \VaultDiskStorage(self::$dbaccess, $id_file);
        $this->assertFalse($vds->isAffected(), sprintf("id_file '%s' is still present in database after Destroy().", $id_file));
    }
    public function dataDestroy() {
        return array(
            array(
                array(
                    "infile" => "DCPTEST/Layout/tst_VaultDiskStorage.odt"
                )
            )
        );
    }
}
?>