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

require_once 'PU_testcase_dcp.php';

class TestPgArray extends TestCaseDcp
{
    /**
     * @dataProvider dataPgToArray
     */
    public function testPgToArray($pgValue, array $expectValues)
    {
        $values = \Doc::pgArrayToPhp($pgValue);
        $this->assertEquals($expectValues, $values, sprintf("wrong value : \n%s", print_r($values, true)));
        foreach ($expectValues as $k => $expectValue) {
            $this->assertTrue($expectValue === $values[$k], sprintf('not strict equal value "%s" <> "%s"', print_r($expectValue, true) , print_r($values[$k], true)));
        }
    }
    /**
     * @dataProvider dataArrayToPg
     */
    public function testArrayToPg(array $values, $expect)
    {
        $values = \Doc::phpArrayToPg($values);
        $this->assertEquals($expect, $values, "wrong value");
    }
    
    public function dataArrayToPg()
    {
        
        return array(
            array(
                array() ,
                "null"
            ) ,
            array(
                array(
                    1
                ) ,
                "{1}"
            ) ,
            
            array(
                array(
                    1,
                    2,
                    3
                ) ,
                "{1,2,3}"
            ) ,
            array(
                array(
                    "1",
                    "2",
                    "3"
                ) ,
                "{1,2,3}"
            ) ,
            array(
                array(
                    1,
                    '',
                    3
                ) ,
                "{1,NULL,3}"
            ) ,
            array(
                array(
                    " 1",
                    " 2",
                    " 3"
                ) ,
                '{" 1"," 2"," 3"}'
            ) ,
            array(
                array(
                    "1,2",
                    "1.3",
                    "3.14,159"
                ) ,
                '{"1,2",1.3,"3.14,159"}'
            ) ,
            array(
                array(
                    'enfin l"été"'
                ) ,
                '{"enfin l\\"été\\""}'
            ) ,
            array(
                array(
                    "l'été"
                ) ,
                "{l'été}"
            ) ,
            array(
                array(
                    "automne hiver"
                ) ,
                '{"automne hiver"}'
            ) ,
            array(
                array(
                    array()
                ) ,
                '{null}'
            ) ,
            array(
                array(
                    array(
                        1,
                        2,
                        3
                    )
                ) ,
                '{{1,2,3}}'
            ) ,
            array(
                array(
                    array(
                        1,
                        2,
                        3
                    ) ,
                    array(
                        4,
                        5,
                        6
                    ) ,
                    array(
                        7,
                        8,
                        9
                    )
                ) ,
                '{{1,2,3},{4,5,6},{7,8,9}}'
            ) ,
            array(
                array(
                    array(
                        1,
                        2,
                        3
                    ) ,
                    array(
                        4
                    ) ,
                    array(
                        5
                    )
                ) ,
                '{{1,2,3},{4,NULL,NULL},{5,NULL,NULL}}'
            ) ,
            array(
                array(
                    array(
                        1,
                        2,
                        3
                    ) ,
                    array(
                        4,
                        '',
                        null
                    ) ,
                    array(
                        null,
                        5
                    )
                ) ,
                '{{1,2,3},{4,NULL,NULL},{NULL,5,NULL}}'
            ) ,
            array(
                array(
                    array() ,
                    array(
                        1
                    ) ,
                    array(
                        null,
                        2
                    ) ,
                    array(
                        null,
                        null,
                        3
                    )
                ) ,
                '{{NULL,NULL,NULL},{1,NULL,NULL},{NULL,2,NULL},{NULL,NULL,3}}'
            ) ,
            array(
                array(
                    array(
                        "007",
                        "James Bond"
                    ) ,
                    array(
                        "Secret,Agent"
                    ) ,
                    array(
                        "MI5"
                    )
                ) ,
                '{{007,"James Bond"},{"Secret,Agent",NULL},{MI5,NULL}}'
            )
        );
    }
    
    public function dataPgToArray()
    {
        return array(
            array(
                "{}",
                array()
            ) ,
            array(
                "{1}",
                array(
                    "1"
                )
            ) ,
            array(
                "{NULL}",
                array(
                    NULL
                )
            ) ,
            array(
                "{null}",
                array(
                    NULL
                )
            ) ,
            array(
                '{"null"}',
                array(
                    "null"
                )
            ) ,
            array(
                '{""}',
                array(
                    ""
                )
            ) ,
            array(
                '{"",""}',
                array(
                    "",
                    ""
                )
            ) ,
            array(
                "{alors}",
                array(
                    "alors"
                )
            ) ,
            array(
                "{alors ici}",
                array(
                    "alors ici"
                )
            ) ,
            array(
                '{"alors ici"}',
                array(
                    "alors ici"
                )
            ) ,
            array(
                '{1,02,30}',
                array(
                    "1",
                    "02",
                    "30"
                )
            ) ,
            array(
                '{1, 02, 30}',
                array(
                    "1",
                    "02",
                    "30"
                )
            ) ,
            array(
                '{ 1 , 02 , 30}',
                array(
                    "1",
                    "02",
                    "30"
                )
            ) ,
            array(
                '{"1","02","30"}',
                array(
                    "1",
                    "02",
                    "30"
                )
            ) ,
            array(
                '{"1" ,  " 02 " ,  "30"}',
                array(
                    "1",
                    " 02 ",
                    "30"
                )
            ) ,
            array(
                '{"  1"," 02","-30"}',
                array(
                    "  1",
                    " 02",
                    "-30"
                )
            ) ,
            array(
                '{"ìíîï",òóôõöø,"àáâãäå"}',
                array(
                    "ìíîï",
                    "òóôõöø",
                    "àáâãäå"
                )
            ) ,
            array(
                '{"été",hivers,"automne"}',
                array(
                    "été",
                    "hivers",
                    "automne"
                )
            ) ,
            array(
                '{{},{},{}}',
                array(
                    array() ,
                    array() ,
                    array() ,
                )
            ) ,
            array(
                '{1, null, 2}',
                array(
                    "1",
                    null,
                    "2",
                )
            ) ,
            
            array(
                '{{"été","hivers"},{"automne","printemps"}}',
                array(
                    array(
                        "été",
                        "hivers"
                    ) ,
                    array(
                        "automne",
                        "printemps"
                    )
                )
            ) ,
            array(
                '{{1,2,3},{4,5},{6}}',
                array(
                    array(
                        "1",
                        "2",
                        "3"
                    ) ,
                    array(
                        "4",
                        "5"
                    ) ,
                    array(
                        "6"
                    )
                )
            ) ,
            array(
                '{{1,2,3} ,{4,5} ,{6}}',
                array(
                    array(
                        "1",
                        "2",
                        "3"
                    ) ,
                    array(
                        "4",
                        "5"
                    ) ,
                    array(
                        "6"
                    )
                )
            ) ,
            array(
                '{{1,2,3}, {4,5}, {6}}',
                array(
                    array(
                        "1",
                        "2",
                        "3"
                    ) ,
                    array(
                        "4",
                        "5"
                    ) ,
                    array(
                        "6"
                    )
                )
            ) ,
            array(
                '{{1,2,3}, {4,5}, {6}}',
                array(
                    array(
                        "1",
                        "2",
                        "3"
                    ) ,
                    array(
                        "4",
                        "5"
                    ) ,
                    array(
                        "6"
                    )
                )
            ) ,
            array(
                '{{1,2,3},{4,5,NULL},{6,NULL,NULL}}',
                array(
                    array(
                        "1",
                        "2",
                        "3"
                    ) ,
                    array(
                        "4",
                        "5",
                        null
                    ) ,
                    array(
                        "6",
                        null,
                        null
                    )
                )
            )
        );
    }
}
