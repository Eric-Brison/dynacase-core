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

require_once 'PU_testcase_dcp.php';

include_once 'FREEDOM/freedom_import_xml.php';
/**
 * Test class for splitXmlDocument() function.
 */

class TestHtmlclean extends TestCaseDcp
{
    /**
     * @dataProvider dataConvertHTMLFragmentToXHTMLDocument
     */
    public function testConvertHTMLFragmentToXHTMLDocument($data)
    {
        \Dcp\Utils\htmlclean::convertHTMLFragmentToXHTMLDocument($data['html'], $error);
        if (!isset($data['errorMatch'])) {
            /* We do not expect errors */
            $this->assertEmpty($error, sprintf("Unexpected error '%s' when converting '%s'.", $error, $data['html']));
        } else {
            $this->assertTrue((preg_match($data['errorMatch'], $error) === 1) , sprintf("Error '%s' did not matched expected error match '%s'.", $error, $data['errorMatch']));
        }
    }
    
    public function dataConvertHTMLFragmentToXHTMLDocument()
    {
        return array(
            array(
                array(
                    'html' => '<a name="foo">foo#1</a><a name="foo">foo#2</a>',
                    // Expect no errors
                    
                )
            )
        );
    }
}
