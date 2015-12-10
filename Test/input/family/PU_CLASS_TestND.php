<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */


namespace Dcp\Pu;

class TestNd extends \Dcp\Family\Document
{
    
    public function postCreated()
    {
        $err = $this->setValue("tst_shared", \Dcp\Core\SharedDocuments::isShared($this->id, $this) ? "yes" : "no");
        return $err;
    }
}
