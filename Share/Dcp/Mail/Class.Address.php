<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Mail;

class Address
{
    const className = __CLASS__;
    public $address = '';
    public $name = '';
    public function __construct($address, $name = '')
    {
        $this->address = $address;
        $this->name = $name;
    }
    public function __toString()
    {
        if ($this->name !== '') {
            return sprintf("%s <%s>", $this->name, $this->address);
        } else {
            return sprintf("<%s>", $this->address);
        }
    }
}
