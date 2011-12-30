<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

class CheckEnd extends CheckData
{
    /**
     * @param array $data
     * @param Doc $doc
     * @return CheckEnd
     */
    function check(array $data, &$doc = null)
    {
        if ($doc->usefor == 'W') {
            $checkW = new CheckWorkflow($doc->classname, $doc->name);
            $checkCr = $checkW->verifyWorkflowComplete();
            if (count($checkCr) > 0) {
                $this->addError(implode("\n", $checkCr));
            }
        }
        
        return $this;
    }
}
