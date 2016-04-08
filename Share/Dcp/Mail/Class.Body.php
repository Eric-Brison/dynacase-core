<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Mail;

class Body implements DataSource
{
    public $data = '';
    public $type = '';
    public function __construct($data, $type)
    {
        $this->data = $data;
        $this->type = $type;
    }
    public function getMimeType()
    {
        return $this->type;
    }
    public function getData()
    {
        return $this->data;
    }
    public function getName()
    {
        if ($this->type == 'text/plain') {
            return "body.txt";
        } elseif ($this->type == 'text/html') {
            return "body.html";
        }
        return "body.bin";
    }
}
