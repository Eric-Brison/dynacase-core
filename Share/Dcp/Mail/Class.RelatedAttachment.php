<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
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

