<?php
/*
 * @author Anakeen
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
