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

class TestGroupAccount extends TestCaseDcpCommonFamily
{
    protected static function getCommonImportFile()
    {
        return array(
            'PU_data_dcp_groupaccount.ods'
        );
    }
    /**
     * @dataProvider dataClearGroup
     * @param $groupId
     * @param $expectedContents
     */
    public function testClearGroup($groupId, $expectedContents)
    {
        /**
         * @var \Dcp\Core\GroupAccount $group
         */
        $group = new_Doc('', $groupId, true);
        $this->assertTrue($group->isAlive() , sprintf("Could not get group with id '%s'.", $groupId));
        $err = $group->Clear();
        $this->assertEmpty($err, sprintf("Clear() on group with id '%s' returned unexpected error message: %s", $groupId, $err));
        foreach ($expectedContents as $expectedContent) {
            $subjectId = $expectedContent['subject'];
            $subject = new_Doc('', $subjectId, true);
            $this->assertTrue($subject->isAlive() , sprintf("Expected subject with id '%s' not found.", $subjectId));
            $check = $expectedContent['check'];
            $argv = isset($expectedContent['argv']) ? $expectedContent['argv'] : null;
            switch ($check) {
                case 'is-empty':
                    /**
                     * @var \Dcp\Core\GroupAccount $subject
                     */
                    $this->assertTrue(is_a($subject, '\Dcp\Core\GroupAccount') , sprintf("Subject with id '%s' is not of expected class '\\Dcp\\Core\\GroupAccount'.", $subjectId));
                    $content = $subject->getContent(false);
                    $this->assertCount(0, $content, sprintf("Unexpected content's count (%s) for subject with id '%s'.", count($content) , $subjectId));
                    break;

                case 'has-no-parent':
                    /**
                     * @var \Dcp\Core\UserAccount $subject
                     */
                    $this->assertTrue(is_a($subject, 'Dcp\Core\UserAccount') , sprintf("Subject with id '%s' is not of expected class 'Dcp\\Core\\UserAccount'.", $subjectId));
                    $parents = $subject->getAllUserGroups();
                    $this->assertCount(0, $parents, sprintf("Unexpected parent's count (%s) for subject with id '%s'.", count($parents) , $subjectId));
                    break;

                case 'has-not-parent':
                    /**
                     * @var \Dcp\Core\UserAccount $subject
                     */
                    $this->assertTrue(is_a($subject, 'Dcp\Core\UserAccount') , sprintf("Subject with id '%s' is not of expected class 'Dcp\\Core\\UserAccount'.", $subjectId));
                    $parents = $subject->getAllUserGroups();
                    $hasNotParent = true;
                    foreach ($parents as $sysId => $docId) {
                        $group = new_Doc('', $docId, true);
                        if (!$group->isAlive()) {
                            continue;
                        }
                        if ($group->name == $argv) {
                            $hasNotParent = false;
                        }
                    }
                    $this->assertTrue($hasNotParent, sprintf("User with id '%s' has unexpected parent '%s'.", $subjectId, $argv));
                    break;
            }
        }
    }
    
    public function dataClearGroup()
    {
        return array(
            array(
                'G_FOO',
                array(
                    array(
                        'subject' => 'G_FOO',
                        'check' => 'is-empty',
                    ) ,
                    array(
                        'subject' => 'U_FOO',
                        'check' => 'has-no-parents'
                    ) ,
                    array(
                        'subject' => 'G_BAR',
                        'check' => 'has-no-parents'
                    ) ,
                    array(
                        'subject' => 'U_BAR',
                        'check' => 'has-not-parent',
                        'argv' => 'G_FOO'
                    )
                )
            )
        );
    }
}
