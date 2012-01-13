<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

class CheckMethod extends CheckData
{
    protected $methodFile;
    /**
     * @var Doc
     */
    protected $doc;
    /**
     * @param array $data
     * @param Doc $doc
     * @return CheckMethod
     */
    function check(array $data, &$doc = null)
    {
        $prefix = $data[1][0];
        if (($prefix == '+') || ($prefix == '*')) $this->methodFile = substr($data[1], 1);
        else $this->methodFile = $data[1];
        $this->doc = $doc;
        $this->checkMethodFile();
        return $this;
    }
    private function getClassFile($className)
    {
        return sprintf('FDL/%s', $className);
    }
    /**
     * check if it is a folder
     * @return void
     */
    protected function checkMethodFile()
    {
        if ($this->methodFile) {
            $methodFile = $this->getClassFile($this->methodFile);
            $fileName = realpath($methodFile);
            if ($fileName) {
                // Get the shell output from the syntax check command
                exec(sprintf('php -n -l %s 2>&1', escapeshellarg($fileName)) , $output, $status);
                if ($status != 0) {
                    $this->addError(ErrorCode::getError('MTHD0002', $methodFile, $this->doc->name, implode("\n", $output)));
                }
            } else {
                $this->addError(ErrorCode::getError('MTHD0001', $methodFile, $this->doc->name));
            }
        }
    }
}
