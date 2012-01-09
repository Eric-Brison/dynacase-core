<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

abstract class CheckData
{
    /**
     * @var array
     */
    protected $errors = array();
    /**
     * call it after check to see error message
     * @return string error message
     */
    public function getErrors()
    {
        return implode("\n", $this->errors);
    }
    /**
     * call it after check to see if error occurs
     * @return bool
     */
    public function hasErrors()
    {
        return count($this->errors) > 0;
    }
    public function addError($msg)
    {
        if ($msg) {
            
            if (!in_array($msg, $this->errors)) {
                $this->errors[] = $msg;
                error_log("ERROR:" . $msg);
            }
        }
    }
    /**
     * @abstract
     * @param array $data
     * @param null $extra
     * @return CheckData this itself
     */
    public abstract function check(array $data, &$extra = null);
}
