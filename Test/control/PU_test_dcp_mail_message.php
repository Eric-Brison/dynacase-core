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

class TestDcpMailMessage extends TestCaseDcp
{
    /**
     * @dataProvider dataMailAddrParser
     */
    public function testMailAddrParser($str, $expectedMails)
    {
        $p = new \Dcp\Mail\MailAddrParser();
        $addrList = array();
        $err = '';
        try {
            $addrList = $p->parse($str);
        }
        catch(\Dcp\Mail\MailAddrParserException $e) {
            $err = $e->getMessage();
        }
        $this->assertEmpty($err, sprintf("Unexpected parse error: %s", $err));
        foreach ($expectedMails as $expected) {
            $found = false;
            foreach ($addrList as $addr) {
                if ($addr->address == $expected) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found, sprintf("Expected address '%s' not found in [%s].", $expected, $str));
        }
    }
    
    public function dataMailAddrParser()
    {
        return array(
            array(
                'foo.bar@example.net',
                array(
                    'foo.bar@example.net'
                )
            ) ,
            array(
                'bill.o\'reilly@example.net',
                array(
                    'bill.o\'reilly@example.net'
                )
            ) ,
            array(
                '  John "Doe" Junior <john.doe@example.net>   ,  "Foo \\"Bar, Baz\\" Buz" <foo.buz@example.net>    ,
  Bill O\'Reilly <bill.o\'reilly@example.net>,
  foo.bar@example.net   ,
  <foo.bar@acme.corp> ,
  "Foo <ACME Corp.>" <foo@acme.corp>
',
                array(
                    'john.doe@example.net',
                    'foo.buz@example.net',
                    'bill.o\'reilly@example.net',
                    'foo.bar@example.net',
                    'foo.bar@acme.corp',
                    'foo@acme.corp'
                )
            )
        );
    }
}
