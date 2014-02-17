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

require_once 'PU_testcase_dcp_commonfamily.php';
/**
 * test some SearchDoc option like generalFilter
 */
class TestSearchFileContent extends TestCaseDcpCommonFamily
{
    /**
     * import TST_FULLSERACHFAM1 family and some documents
     * @static
     * @return string
     */
    protected static function getCommonImportFile()
    {
        return "PU_data_dcp_filecontentsearchfamily.ods";
    }
    /**
     * @dataProvider dataGlobalSearch
     */
    public function testGlobalSearch($words, array $expectedNames)
    {
        $this->initFileContentData();
        $search = new \SearchDoc(self::$dbaccess, "TST_FILECONTENT_A");
        $search->setObjectReturn();
        $search->addGeneralFilter($words);
        $search->search();
        $res = array();
        while ($doc = $search->getNextDoc()) {
            $res[] = $doc->name;
        }
        
        $this->assertEquals(count($expectedNames) , $search->count() , sprintf("returns %s\n expected %s\n%s", print_r($res, true) , print_r($expectedNames, true) , print_r($search->getSearchInfo() , true)));
        
        foreach ($expectedNames as $name) {
            $this->assertTrue(in_array($name, $res) , sprintf("%s not found, returns %s\n expected %s", $name, print_r($res, true) , print_r($expectedNames, true)));
        }
    }
    /**
     * @dataProvider dataRevisionSearch
     */
    public function testRevisionSearch($words, array $expectedNames)
    {
        $this->initFileContentData();
        $search = new \SearchDoc(self::$dbaccess, "TST_FILECONTENT_A");
        $search->setObjectReturn();
        $search->latest = false;
        $search->addFilter("locked = -1");
        $search->addGeneralFilter($words);
        $search->search();
        $res = array();
        while ($doc = $search->getNextDoc()) {
            $res[] = array(
                "name" => $doc->name,
                "revision" => $doc->revision
            );
        }
        
        $this->assertEquals(count($expectedNames) , $search->count() , sprintf("returns %s\n expected %s\n%s", print_r($res, true) , print_r($expectedNames, true) , print_r($search->getSearchInfo() , true)));
        
        foreach ($expectedNames as $exp) {
            $name = $exp["name"];
            $rev = $exp["revision"];
            $this->assertTrue($this->verifyGoodRevision($res, $name, $rev) , sprintf("%s not found, returns %s\n expected %s", $name, print_r($res, true) , print_r($expectedNames, true)));
        }
    }
    /**
     * @dataProvider dataRecordedSearch
     */
    public function testRecordedSearch($searchName, array $expectedNames)
    {
        $this->initFileContentData();
        $search = new \SearchDoc(self::$dbaccess);
        $search->setObjectReturn(true);
        $search->useCollection($searchName);
        $search->search();
        $res = array();
        while ($doc = $search->getNextDoc()) {
            $res[] = array(
                "name" => $doc->name,
                "revision" => $doc->revision
            );
        }
        
        $this->assertEquals(count($expectedNames) , $search->count() , sprintf("returns %s\n expected %s\n%s", print_r($res, true) , print_r($expectedNames, true) , print_r($search->getSearchInfo() , true)));
        
        foreach ($expectedNames as $exp) {
            $name = $exp["name"];
            $rev = $exp["revision"];
            $this->assertTrue($this->verifyGoodRevision($res, $name, $rev) , sprintf("%s not found, returns %s\n expected %s", $name, print_r($res, true) , print_r($expectedNames, true)));
        }
    }
    
    private function verifyGoodRevision(array $t, $name, $revision)
    {
        foreach ($t as $item) {
            $iname = $item["name"];
            $irevision = $item["revision"];
            if ($iname == $name && $irevision == $revision) {
                return true;
            }
        }
        return false;
    }
    
    private function initFileContentData()
    {
        require_once "FDL/Lib.Vault.php";
        SetHttpVar("TE_ACTIVATE", "no");
        $d = createDoc(self::$dbaccess, "TST_FILECONTENT_A");
        $d->setValue("tst_title", "One Hello");
        $err = $d->store();
        $this->assertEmpty($err, "ccanot create document test");
        $d->setLogicalName("TST_FC1");
        
        $d = createDoc(self::$dbaccess, "TST_FILECONTENT_A");
        $d->setValue("tst_title", "Dog");
        $err = $d->store();
        $this->assertEmpty($err, "cannot create document test");
        $d->setLogicalName("TST_FC2");
        insertIntoFileContent($d, "tst_file1", -1, "Le chien (Canis lupus familiaris) est la sous-espèce domestique de Canis lupus, un mammifère de la famille des Canidés (Canidae) qui comprend également le loup gris, ancêtre sauvage du chien, et le dingo, chien domestique redevenu sauvage.");
        
        $d = createDoc(self::$dbaccess, "TST_FILECONTENT_A");
        $d->setValue("tst_title", "Horse");
        $err = $d->store();
        $this->assertEmpty($err, "cannot create document test");
        $d->setLogicalName("TST_FC3");
        insertIntoFileContent($d, "tst_file1", -1, "Le cheval (Equus ferus caballus ou Equus caballus) est un grand mammifère herbivore et ongulé à sabot unique, appartenant aux espèces de la famille des Équidés (Equidae). Il a évolué au cours des dernières 45 à 55 millions d'années, à partir d'un mammifère de la taille d'un chien possédant plusieurs doigts");
        
        $d = createDoc(self::$dbaccess, "TST_FILECONTENT_B");
        $d->setValue("tst_title", "Duck");
        $err = $d->store();
        $this->assertEmpty($err, "cannot create document test");
        $d->setLogicalName("TST_FC4");
        insertIntoFileContent($d, "tst_file1", -1, "« Canard » est un terme générique qui désigne des oiseaux aquatiques, aux pattes palmées (palmipède) et au bec caractéristique, domestiqués ou non. Ils font pour la plupart partie de la famille des anatidés. Ce mot désigne des espèces qui ne portent pas nécessairement un nom vernaculaire contenant le terme canard. En effet, certaines espèces qualifiées de canards sont désignées par des noms vernaculaires comportant des termes comme dendrocygnes, sarcelles, tadornes ou brassemers. Le canard le plus connu du grand public est le Canard colvert dont sont issus de nombreux canards domestiques.");
        insertIntoFileContent($d, "tst_file2", -1, "On peut distinguer plusieurs types de canards suivant leur mode de vie :

            les canards de surface (genres Anas, Aix, etc.) ;
            les canards plongeurs (genres Aythya, Netta, etc.) ;
            les canards piscivores de la sous-famille des merginés.
        ");
        
        $d = createDoc(self::$dbaccess, "TST_FILECONTENT_C");
        $d->setValue("tst_title", "Rhinoceros");
        $err = $d->store();
        $this->assertEmpty($err, "cannot create document test");
        $d->setLogicalName("TST_FC5");
        insertIntoFileContent($d, "tst_file1", -1, "Les rhinocéros sont des mammifères herbivores appartenant à la famille des Rhinocerotidae, ordre des périssodactyles. Toutes les espèces de rhinocéros sont considérées comme menacées de disparition. Le rhinocéros fait localement l'objet d'un programme ou de projets de réintroduction.");
        insertIntoFileContent($d, "tst_files", 0, "Ils peuvent mesurer 4 m de longueur pour 2 m de hauteur, et une masse pouvant avoisiner les 3 tonnes. Ce sont les plus gros mammifères terrestres actuels après l'éléphant.");
        insertIntoFileContent($d, "tst_files", 1, "Comme l'éléphant, le rhinocéros barète ou barrit.");
        insertIntoFileContent($d, "tst_files", 2, "Le mot rhinocéros vient du grec rhinos, nez, et keras, corne, car il porte une ou deux cornes sur le nez, et non sur le front comme les autres mammifères cornus.");
        
        $d = createDoc(self::$dbaccess, "TST_FILECONTENT_C");
        $d->setValue("tst_title", "Big Cachalot");
        $err = $d->store();
        $this->assertEmpty($err, "cannot create document test");
        $d->setLogicalName("TST_FC6");
        insertIntoFileContent($d, "tst_file1", -1, "Le grand cachalot (Physeter macrocephalus ou P. catodon), communément appelé cachalot et parfois cachalot macrocéphale, est une espèce de cétacés à dents de la famille des physétéridés et unique représentant actuel de son genre, Physeter. Il est l'une des trois espèces encore vivantes de sa super-famille, avec le cachalot pygmée (Kogia breviceps) et le cachalot nain (K. simus). Il a une répartition cosmopolite, fréquentant tous les océans et une grande majorité des mers du monde. Cependant, seuls les mâles se risquent dans les eaux arctiques et antarctiques, les femelles restant avec leurs jeunes dans les eaux plus chaudes.");
        $d->revise();
        
        $d = createDoc(self::$dbaccess, "TST_FILECONTENT_C");
        $d->setValue("tst_title", "calmar");
        $err = $d->store();
        $this->assertEmpty($err, "cannot create document test");
        $d->setLogicalName("TST_FC7");
        insertIntoFileContent($d, "tst_file1", -1, "Les calmars ou teuthides (Teuthida) constituent un ordre, apparu au début du Jurassique, de céphalopodes décapodes marins apparentés aux seiches et regroupant près de 300 espèces. La plupart des espèces n'ont pas de nom vernaculaire spécifique et sont donc désignées en français sous le nom générique de « calmar ». Il en est de même pour le terme encornet, autre nom vernaculaire plus particulièrement utilisé lorsque ces animaux sont considérés en tant que comestibles ou appâts de pêche, mais qui désigne aussi d'autres céphalopodes, comme les seiches.");
        $d->revise();
        $d->setValue("tst_title", "seiche");
        $err = $d->store();
        $this->assertEmpty($err, "cannot create document test");
        insertIntoFileContent($d, "tst_file1", -1, "La seiche commune (Sepia officinalis) est une espèce de céphalopode.
         Longue de 20 à 30 cm (tentacules compris), elle possède un flotteur interne (l'os de seiche) qui joue aussi un rôle d'endosquelette et peut-être de réserve minérale. Sa tête porte des bras courts munis de ventouses, ainsi que deux grands tentacules et deux très gros yeux.");
    }
    public function dataGlobalSearch()
    {
        return array(
            array(
                "Hello",
                array(
                    "TST_FC1"
                )
            ) ,
            array(
                "chien",
                array(
                    "TST_FC2",
                    "TST_FC3"
                )
            ) ,
            array(
                "chevaux",
                array(
                    "TST_FC3"
                )
            ) ,
            array(
                "*sauvage*",
                array(
                    "TST_FC2"
                )
            ) ,
            array(
                "piscivore",
                array(
                    "TST_FC4"
                )
            ) ,
            array(
                "piscivore aquatiques dendrocygnes",
                array(
                    "TST_FC4"
                )
            ) ,
            array(
                "deux corne 3 tonne herbivore",
                array(
                    "TST_FC5"
                )
            ) ,
            array(
                "*deux cornes*",
                array(
                    "TST_FC5"
                )
            ) ,
            array(
                "grand cachalot",
                array(
                    "TST_FC6"
                )
            ) ,
            array(
                "flotteur interne",
                array(
                    "TST_FC7"
                )
            ) ,
            array(
                "teuthides",
                array()
            ) ,
            array(
                "famille",
                array(
                    "TST_FC2",
                    "TST_FC4",
                    "TST_FC3",
                    "TST_FC5",
                    "TST_FC6"
                )
            ) ,
        );
    }
    
    public function dataRecordedSearch()
    {
        return array(
            array(
                "TST_FC_MAMMIFERE",
                array(
                    array(
                        "name" => "TST_FC2",
                        "revision" => 0
                    ) ,
                    array(
                        "name" => "TST_FC3",
                        "revision" => 0
                    ) ,
                    array(
                        "name" => "TST_FC5",
                        "revision" => 0
                    )
                )
            ) ,
            
            array(
                "TST_FC_RHINO",
                array(
                    array(
                        "name" => "TST_FC5",
                        "revision" => 0
                    )
                )
            ) ,
            array(
                "TST_FC_CANARD",
                array(
                    array(
                        "name" => "TST_FC4",
                        "revision" => 0
                    )
                )
            ) ,
            array(
                "TST_FC_MAMMI",
                array(
                    array(
                        "name" => "TST_FC2",
                        "revision" => 0
                    ) ,
                    array(
                        "name" => "TST_FC3",
                        "revision" => 0
                    ) ,
                    array(
                        "name" => "TST_FC5",
                        "revision" => 0
                    )
                )
            )
        );
    }
    public function dataRevisionSearch()
    {
        return array(
            array(
                "grand cachalot",
                array(
                    array(
                        "name" => "TST_FC6",
                        "revision" => 0
                    )
                )
            ) ,
            array(
                "flotteur interne",
                array()
            ) ,
            array(
                "teuthides",
                array(
                    array(
                        "name" => "TST_FC7",
                        "revision" => 0
                    )
                )
            ) ,
            array(
                "espèce",
                array(
                    array(
                        "name" => "TST_FC6",
                        "revision" => 0
                    ) ,
                    array(
                        "name" => "TST_FC7",
                        "revision" => 0
                    )
                )
            ) ,
        );
    }
}
?>