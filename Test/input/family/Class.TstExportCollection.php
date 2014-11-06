<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
namespace Dcp\Pu;

class TstExportCollection extends \Dcp\Family\Document
{
    public function postImport(array $extra = array())
    {
        if (!empty($extra["state"])) {
            return $this->setState($extra["state"]);
        } else {
            $this->wid = 0;
            $this->state = '';
            $this->modify();
        }
        return '';
    }
}

