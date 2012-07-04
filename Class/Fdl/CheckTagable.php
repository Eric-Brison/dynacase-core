<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Checking document's profil
 * @class CheckTagable
 * @brief Check tagable property when importing definition
 * @see ErrorCodePRFL
 */
class CheckTagable extends CheckData
{
    
    private $tagValue = "";
    
    private $availableTags = array(
        "no",
        "public",
        "restricted"
    );
    /**
     * @param array $data
     * @param null $extra
     * @return CheckData this itself
     */
    public function check(array $data, &$extra = null)
    {
        $this->tagValue = $data[1];
        $this->checkTags();
        return $this;
    }
    
    private function checkTags()
    {
        if ($this->tagValue) {
            if (!in_array($this->tagValue, $this->availableTags)) {
                $this->addError(ErrorCode::getError('TAG0001', $this->tagValue, implode(', ', $this->availableTags)));
            }
        }
    }
}
