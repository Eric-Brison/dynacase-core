<?php
/*
 * @author Anakeen
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
