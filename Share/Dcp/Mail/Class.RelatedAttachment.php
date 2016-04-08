<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Mail;

class RelatedAttachment extends Attachment
{
    public $cid = '';
    public function __construct($file, $name = 'att.dat', $type = 'application/binary', $cid = '')
    {
        parent::__construct($file, $name, $type);
        $this->cid = $cid;
    }
}

