<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Pu;
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package Dcp\Pu
 */
require_once 'PU_testcase_dcp_document.php';

class TestLink extends TestCaseDcpDocument
{
    protected static $outputDir;
    
    protected function setUp()
    {
        // nothing
        
    }
    
    protected function tearDown()
    {
        // nothing
        
    }
    
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        
        self::connectUser();
        self::beginTransaction();
        // family
        self::importDocument("PU_data_dcp_documentlink.ods");
        // three documents : linkOne, linkTwo and linkThree
        self::importDocument("PU_data_dcp_documentslink.xml");
    }
    
    public static function tearDownAfterClass()
    {
        self::rollbackTransaction();
    }
    /**
     * @dataProvider dataAttrLinks
     */
    public function testAttrLinkCompose($docName, $link, $expectedLink)
    {
        
        $doc = new_doc(self::$dbaccess, $docName);
        $this->assertTrue($doc->isAlive() , "document $docName is not alive");
        $url = $doc->urlWhatEncode($link);
        $this->assertEquals($expectedLink, $url, "url link is not correctly encoded");
    }
    /**
     * @dataProvider dataParamLinks
     */
    public function testParamLinkCompose($docName, $link, array $params, $expectedLink)
    {
        
        $doc = new_doc(self::$dbaccess, $docName);
        $this->assertTrue($doc->isAlive() , "document $docName is not alive");
        foreach ($params as $k => $v) {
            $this->getApplication()->SetVolatileParam($k, $v);
        }
        
        $url = $doc->urlWhatEncode($link);
        $this->assertEquals($expectedLink, $url, "url link is not correctly encoded");
    }
    
    public function dataParamLinks()
    {
        return array(
            array(
                "linkOne",
                "http://www.test.com/{PU_TEST1}",
                array(
                    "PU_TEST1" => "testOne"
                ) ,
                "http://www.test.com/testOne"
            ) ,
            array(
                "linkOne",
                "http://www.test.com/{PU_TEST2}",
                array(
                    "PU_TEST2" => "test Two"
                ) ,
                "http://www.test.com/test%20Two"
            )
        );
    }
    
    public function dataAttrLinks()
    {
        return array(
            array(
                "linkOne",
                "http://www.test.com/",
                "http://www.test.com/"
            ) ,
            array(
                "linkThree",
                "http://test.com/%tst_title%/",
                "http://test.com/Test/"
            ) ,
            array(
                "linkThree",
                "http://test.com/?title=%tst_title%&long=%tst_longtext%",
                "http://test.com/?title=Test&long=Long"
            ) ,
            array(
                "linkOne",
                "http://test.com/?title=%tst_title%&long=%tst_longtext%",
                "http://test.com/?title=Arbre%20%2B%20Feuilles&long=La%20nature%20est%20jolie%20%3Cen%20automne%3E"
            ) ,
            array(
                "linkOne",
                "http://test.com/?title=%aa_one%",
                "http://test.com/?title=Un"
            ) ,
            array(
                "linkOne",
                "http://test.com/?title=%AA_ONE%",
                "http://test.com/?title=Un"
            ) ,
            array(
                "linkTwo",
                "http://test.com/?title=%tst_title%",
                "http://test.com/?title=Joe%20%26%20Jane"
            ) ,
            array(
                "linkTwo",
                "http://test.com/?long=%tst_longtext%",
                "http://test.com/?long=Premi%C3%A8re%20ligne%5CnEt%20deuxi%C3%A8me%20ligne"
            ) ,
            array(
                "linkTwo",
                "http://test.com/?date=%tst_coldate%",
                getLcDate() == 'iso' ? 'http://test.com/?date=2011-11-01%5Cn2011-11-02' : "http://test.com/?date=01%2F11%2F2011%5Cn02%2F11%2F2011"
            ) ,
            array(
                "linkTwo",
                "http://test.com/?date=%tst_nothing%",
                false
            ) ,
            array(
                "linkTwo",
                "http://test.com/?title=%title%",
                "http://test.com/?title=Joe%20%26%20Jane"
            ) ,
            array(
                "linkTwo",
                "http://test.com/?title=%TITLE%",
                "http://test.com/?title=Joe%20%26%20Jane"
            ) ,
            array(
                "linkTwo",
                "http://test.com/?title=%T%",
                "http://test.com/?title=Joe%20%26%20Jane"
            ) ,
            array(
                "linkTwo",
                "http://test.com/?title=%%2T",
                "http://test.com/?title=%2T"
            ) ,
            array(
                "linkTwo",
                "http://test.com/?title=%3A",
                "http://test.com/?title=%3A"
            ) ,
            array(
                "linkTwo",
                "http://test.com/?title=%3A%3ATest%28%29",
                "http://test.com/?title=%3A%3ATest%28%29"
            ) ,
            array(
                "linkTwo",
                "http://test.com/?title=%3A%3A%T%%28%29",
                "http://test.com/?title=%3A%3AJoe%20%26%20Jane%28%29"
            ) ,
            array(
                "linkTwo",
                "http://test.com/?title=%TITLE%&option=%?OPTIONONE%",
                "http://test.com/?title=Joe%20%26%20Jane&option="
            ) ,
            array(
                "linkTwo",
                "http://test.com/?title=%TITLE%&option1=%?OPTIONONE%&hard=true&option2=%?OPTIONTWO%",
                "http://test.com/?title=Joe%20%26%20Jane&option1=&hard=true&option2="
            ) ,
            array(
                "linkTwo",
                "::linkOne()",
                "::linkOne()"
            ) ,
            array(
                "linkTwo",
                "%::linkOne()%",
                "http://www.test.net/"
            ) ,
            array(
                "linkTwo",
                "%::linkOne()%?a=%T%",
                "http://www.test.net/?a=Joe%20%26%20Jane"
            ) ,
            array(
                "linkTwo",
                "%::linkTwo()%",
                "http://www.test.net/?b=Joe%20%26%20Jane"
            )
        );
    }
}
?>