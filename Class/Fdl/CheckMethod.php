<?php
/*
 * @author Anakeen
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
        if (!empty($data[1])) {
            $prefix = $data[1][0];
            if (($prefix == '+') || ($prefix == '*')) $this->methodFile = substr($data[1], 1);
            else $this->methodFile = $data[1];
            $this->doc = $doc;
            $this->checkMethodFile();
        }
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
                if (CheckClass::phpLintFile($fileName, $output) === false) {
                    $this->addError(ErrorCode::getError('MTHD0002', $methodFile, $this->doc->name, implode("\n", $output)));
                }
            } else {
                $this->addError(ErrorCode::getError('MTHD0001', $methodFile, $this->doc->name));
            }
        }
    }
}
