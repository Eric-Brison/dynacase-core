<?php
/*
 * @author Anakeen
 * @package FDL
*/
namespace Dcp\Pu;

class TstInvisible extends \Dcp\Family\Document
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

